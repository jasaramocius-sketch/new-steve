<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVehicle;
use App\Models\Vehicle;
use App\Services\VehicleCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function __construct(private VehicleCatalog $vehicleCatalog) {}

    /**
     * Buyer-facing price ka SQL expression (cost + product/global commission).
     *
     * Storefront par price `buyerPrice()` se dikhti hai (cost + commission), is liye
     * price filter/sort bhi isi expression par hone chahiye — warna max_price = 172
     * par $190.29 wala product (cost 172.99 + 10%) bhi list ho jata hai.
     */
    private function buyerPriceExpression(): string
    {
        $global = max(0, min(100, (float) globalCommissionPercent()));
        $global = number_format($global, 2, '.', '');

        // Model ki tarah 0–100 clamp karo (product-level commission null ho to global).
        return '(cost * (1 + (LEAST(GREATEST(COALESCE(commission, '.$global.'), 0), 100) / 100)))';
    }

    private function filterProducts(Request $request, $query, bool $applyPriceFilter = true)
    {
        $search = $request->get('search', '');
        $sort = $request->get('sort', 'default');
        $minPrice = $request->get('min_price', '');
        $maxPrice = $request->get('max_price', '');
        $brand = $request->get('brand', '');
        $year = $request->get('year', '');
        $make = $request->get('make', '');
        $model = $request->get('model', '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        $currency = session('currency', 'USD');
        $rate = config('currencies.'.$currency.'.rate', 1);

        if ($applyPriceFilter) {
            if ($minPrice !== '') {
                $query->whereRaw($this->buyerPriceExpression().' >= ?', [(float) $minPrice / $rate]);
            }

            if ($maxPrice !== '') {
                // Add 0.999999 to cover decimal prices up to the selected max integer (e.g. max_price = 4 covers 4.42)
                $effectiveMax = (float) $maxPrice + 0.999999;
                $query->whereRaw($this->buyerPriceExpression().' <= ?', [$effectiveMax / $rate]);
            }
        }

        if ($brand !== '') {
            if (is_numeric($brand)) {
                $query->where('brand_id', (int) $brand);
            } else {
                $brandModel = Brand::where('slug', $brand)->first();
                if ($brandModel) {
                    $query->where('brand_id', $brandModel->id);
                }
            }
        }

        if ($year !== '' || $make !== '' || $model !== '') {
            $query->whereIn('products.id', function ($sub) use ($year, $make, $model) {
                $sub->select('product_id')->from('product_vehicle');

                if ($year !== '') {
                    $sub->where('year', $year);
                }
                if ($make !== '') {
                    $sub->where('make', $make);
                }
                if ($model !== '') {
                    $sub->where('model', $model);
                }
            });
        }

        if ($sort === 'price_asc') {
            $query->orderByRaw($this->buyerPriceExpression().' asc');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw($this->buyerPriceExpression().' desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function applyVehicleFilter(Request $request, $query, bool $autoFirst = false)
    {
        $selectedVehicle = null;
        $vehicleMatchCount = null;
        $vehicleFilterCleared = session('shop_vehicle_cleared', false);

        if (Auth::check()) {
            $selectedVehicleId = session('selected_vehicle_id');
            if ($selectedVehicleId) {
                $selectedVehicle = Vehicle::where('user_id', Auth::id())->where('id', $selectedVehicleId)->first();
            }
            if (! $selectedVehicle && $autoFirst) {
                $selectedVehicle = Vehicle::where('user_id', Auth::id())->first();
            }
        }

        $hasVehicleFilters = $request->filled('year') || $request->filled('make') || $request->filled('model');

        if ($selectedVehicle && ! $hasVehicleFilters && ! $vehicleFilterCleared) {
            $request->merge([
                'year' => $selectedVehicle->year,
                'make' => $selectedVehicle->make,
                'model' => $selectedVehicle->model,
            ]);
        }

        if ($vehicleFilterCleared || $hasVehicleFilters) {
            $selectedVehicle = null;
        }

        // When a garage vehicle is applied, count matching products. On zero match,
        // fall back to showing all products but keep the vehicle alert (with the 0
        // count) so the user knows no parts fit their vehicle.
        if ($selectedVehicle) {
            $vehicleQuery = (clone $query);
            $this->filterProducts($request, $vehicleQuery);
            $vehicleMatchCount = $vehicleQuery->count();

            if ($vehicleMatchCount === 0) {
                $request->merge(['year' => '', 'make' => '', 'model' => '']);
            }
        }

        return compact('selectedVehicle', 'vehicleMatchCount');
    }

    private function getSharedData(Request $request, $productsQuery = null)
    {
        $categoryTree = \App\Services\CategoryTree::tree();

        $currency = session('currency', 'USD');

        $priceRange = Cache::remember('catalog.price_range_v2', now()->addDay(), function () {
            $expr = $this->buyerPriceExpression();

            $row = DB::table('products')
                ->where('status', true)
                ->selectRaw('MAX('.$expr.') as max_price, MIN('.$expr.') as min_price')
                ->first();

            return [
                'max' => $row->max_price ?? 1000,
                'min' => $row->min_price ?? 0,
            ];
        });

        $brands = Cache::remember('catalog.brands', now()->addDay(), function () {
            return DB::table('brands')
                ->whereNotNull('name')
                ->where('name', '<>', '')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('products')
                        ->whereColumn('products.brand_id', 'brands.id')
                        ->where('status', true);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn ($b) => (array) $b)
                ->all();
        });

        $selectedYear = $request->filled('year') ? (int) $request->get('year') : null;
        $selectedMake = $request->filled('make') ? $request->get('make') : null;

        return [
            'categoryTree' => $categoryTree,
            'brands' => collect($brands)->map(fn ($b) => (object) $b),
            'years' => $this->vehicleCatalog->years(),
            'makes' => $selectedYear ? $this->vehicleCatalog->makes($selectedYear) : collect(),
            'models' => $this->vehicleCatalog->models($selectedYear, $selectedMake),
            'maxProductPrice' => $priceRange['max'],
            'minProductPrice' => $priceRange['min'],
            'currencySymbol' => config('currencies.'.$currency.'.symbol', '$'),
        ];
    }

    private function computeCategoryTotals($category, $productCounts)
    {
        $total = $productCounts->get($category->id, 0);
        if ($category->relationLoaded('children')) {
            foreach ($category->children as $child) {
                $total += $this->computeCategoryTotals($child, $productCounts);
            }
        }
        $category->descendant_count = $total;

        return $total;
    }

    public function index(Request $request)
    {
        $productsQuery = Product::where('status', true)->hasUsableImage();
        $vehicleData = $this->applyVehicleFilter($request, $productsQuery, true);

        $baseQuery = clone $productsQuery;
        $this->filterProducts($request, $baseQuery, false);

        $this->filterProducts($request, $productsQuery, true);
        $products = $productsQuery->paginate(24)->onEachSide(1)->withQueryString();

        return view('shop.index', array_merge(
            $vehicleData,
            compact('products'),
            $this->getSharedData($request, $baseQuery),
            ['page' => Page::where('slug', 'shop')->where('status', true)->first()]
        ));
    }

    public function vehicleYears()
    {
        return response()->json($this->vehicleCatalog->years());
    }

    public function vehicleMakes(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->get('year') : null;

        return response()->json($this->vehicleCatalog->makes($year));
    }

    public function vehicleModels(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->get('year') : null;
        $make = $request->filled('make') ? $request->get('make') : null;

        return response()->json($this->vehicleCatalog->models($year, $make));
    }

    public function clearVehicleFilter(Request $request)
    {
        session(['shop_vehicle_cleared' => true]);

        $url = $request->headers->get('referer') ?: route('shop');
        $path = $url;
        $query = [];
        if (str_contains($url, '?')) {
            [$path, $qs] = explode('?', $url, 2);
            parse_str($qs, $query);
        }
        unset($query['year'], $query['make'], $query['model'], $query['page']);

        return redirect($path.(! empty($query) ? '?'.http_build_query($query) : ''));
    }

    public function clearAllFilters(Request $request)
    {
        session(['shop_vehicle_cleared' => true]);

        $returnPath = (string) $request->input('return_path', '/search');
        if (! str_starts_with($returnPath, '/')
            || str_starts_with($returnPath, '//')
            || ($returnPath !== '/search' && ! str_starts_with($returnPath, '/category/'))) {
            $returnPath = '/search';
        }

        return redirect($returnPath);
    }

    public function customerProducts(Request $request)
    {
        $productsQuery = Product::where('status', true)->hasUsableImage();

        $baseQuery = clone $productsQuery;
        $this->filterProducts($request, $baseQuery, false);

        $this->filterProducts($request, $productsQuery, true);
        $products = $productsQuery->paginate(24)->onEachSide(1)->withQueryString();
        $pageTitle = 'Classified Products';

        return view('shop.index', array_merge(
            compact('products', 'pageTitle'),
            $this->getSharedData($request, $baseQuery)
        ));
    }

    public function category(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $currentCategory = $category;
        $currentSubcategory = null;
        $currentChildcategory = null;

        if ($category->parent_id) {
            $topLevel = $category;
            while ($topLevel->parent_id) {
                $topLevel = $topLevel->parent;
            }
            $currentCategory = $topLevel->load('children.children');

            if ($category->parent_id == $topLevel->id) {
                $currentSubcategory = $category;
            } else {
                $midLevel = $category->parent;
                while ($midLevel->parent_id != $topLevel->id) {
                    $midLevel = $midLevel->parent;
                }
                $currentSubcategory = $midLevel;
                $currentChildcategory = $category;
            }
        } else {
            $currentCategory->load('children.children');
        }

        $categoryIds = $category->getAllDescendantIds();

        $productsQuery = Product::whereIn('category_id', $categoryIds)->where('status', true)->hasUsableImage();
        $vehicleData = $this->applyVehicleFilter($request, $productsQuery);

        $baseQuery = clone $productsQuery;
        $this->filterProducts($request, $baseQuery, false);

        $this->filterProducts($request, $productsQuery, true);
        $products = $productsQuery->paginate(24)->onEachSide(1)->withQueryString();

        return view('shop.index', array_merge(
            compact('products', 'currentCategory'),
            $vehicleData,
            array_filter(compact('currentSubcategory', 'currentChildcategory')),
            $this->getSharedData($request, $baseQuery)
        ));
    }

    public function categoryByPath(Request $request, $parent, $child, $subchild = null)
    {
        $parentCat = Category::where('slug', $parent)->firstOrFail();
        $childCat = Category::where('slug', $child)->where('parent_id', $parentCat->id)->firstOrFail();

        if ($subchild) {
            $category = Category::where('slug', $subchild)->where('parent_id', $childCat->id)->firstOrFail();
        } else {
            $category = $childCat;
        }

        $currentCategory = $parentCat;
        $currentSubcategory = $childCat;
        $currentChildcategory = $subchild ? $category : null;

        if (!$subchild) {
            $currentChildcategory = null;
        }

        $categoryIds = $category->getAllDescendantIds();

        $productsQuery = Product::whereIn('category_id', $categoryIds)->where('status', true)->hasUsableImage();
        $vehicleData = $this->applyVehicleFilter($request, $productsQuery);

        $baseQuery = clone $productsQuery;
        $this->filterProducts($request, $baseQuery, false);

        $this->filterProducts($request, $productsQuery, true);
        $products = $productsQuery->paginate(24)->onEachSide(1)->withQueryString();

        return view('shop.index', array_merge(
            compact('products', 'currentCategory', 'currentSubcategory', 'currentChildcategory'),
            $vehicleData,
            $this->getSharedData($request, $baseQuery)
        ));
    }

    public function subcategory(Request $request, $parent, $child, $subchild = null)
    {
        return $this->categoryByPath($request, $parent, $child, $subchild);
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $productFitments = ProductVehicle::where('product_id', $product->id)
            ->whereNotNull('year')
            ->select('year', 'make', 'model')
            ->orderByDesc('year')
            ->limit(60)
            ->get();

        $userVehicle = null;
        $vehicleFit = null;
        if (Auth::check()) {
            $selectedVehicleId = session('selected_vehicle_id');
            if ($selectedVehicleId) {
                $userVehicle = Vehicle::where('user_id', Auth::id())->where('id', $selectedVehicleId)->first();
            }
            if (! $userVehicle) {
                $userVehicle = Vehicle::where('user_id', Auth::id())->first();
            }
            if ($userVehicle) {
                $vehicleFit = ProductVehicle::where('product_id', $product->id)
                    ->where('year', $userVehicle->year)
                    ->where('make', $userVehicle->make)
                    ->where('model', $userVehicle->model)
                    ->exists();
            }
        }

        return view('product.show', compact('product', 'productFitments', 'userVehicle', 'vehicleFit'));
    }

    public function suggestions(Request $request)
    {
        $query = $request->get('query', '');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $products = Product::where('status', true)
            ->hasUsableImage()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('part_number', 'like', "%{$query}%");
            })
            ->with('category:id,name')
            ->limit(8)
            ->get(['id', 'name', 'slug', 'cost', 'main_image', 'category_id']);

        $results = $products->map(function ($product) {
            return [
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => currency_format(buyerPrice($product->price, $product)),
                'old_price' => null,
                'image' => storedImageUrl($product->image, 'assets/images/thumbnails'),
                'category' => $product->category->name ?? '',
                'url' => route('product', $product->slug),
            ];
        });

        return response()->json($results);
    }
}
