<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CommissionHistory;
use App\Models\Contact;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductFitment;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wishlist;
use App\Services\ProductReviewEligibility;
use App\Services\VehicleCatalog;
use App\Support\DescriptionMarkdown;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public static function normalizeImportedProductData(array $data): array
    {
        $rawPolicy = trim((string) ($data['policy_text'] ?? $data['buy_return_policy'] ?? $data['return_policy'] ?? ''));
        $rawFeatures = trim((string) ($data['features'] ?? $data['feature_list'] ?? ''));
        $rawReviews = $data['reviews_data'] ?? $data['reviews'] ?? null;

        $features = [];
        if ($rawFeatures !== '') {
            $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\n|\|\|/', $rawFeatures))));
        }

        $reviews = [];
        if (is_string($rawReviews) && trim($rawReviews) !== '') {
            $decoded = json_decode($rawReviews, true);
            if (is_array($decoded)) {
                $reviews = $decoded;
            } elseif (str_contains($rawReviews, '|')) {
                foreach (explode('|', $rawReviews) as $chunk) {
                    $chunk = trim($chunk);
                    if ($chunk === '') {
                        continue;
                    }
                    $parts = explode('::', $chunk, 3);
                    if (count($parts) === 3) {
                        $reviews[] = [
                            'name' => $parts[0],
                            'rating' => max(1, min(5, (int) $parts[1])),
                            'text' => $parts[2],
                            'deleted' => false,
                        ];
                    }
                }
            }
        } elseif (is_array($rawReviews)) {
            $reviews = $rawReviews;
        }

        foreach ($reviews as $key => $review) {
            if (! is_array($review)) {
                unset($reviews[$key]);

                continue;
            }
            $reviews[$key]['deleted'] = (bool) ($review['deleted'] ?? false);
            if (! isset($reviews[$key]['rating'])) {
                $reviews[$key]['rating'] = 5;
            }
            $reviews[$key]['rating'] = max(1, min(5, (int) $reviews[$key]['rating']));
            if (! isset($reviews[$key]['name'])) {
                $reviews[$key]['name'] = 'Customer';
            }
        }

        $description = DescriptionMarkdown::toHtml((string) ($data['description'] ?? ''));

        // If the description embeds a "Key Features:" section and no separate
        // feature list was provided, split it out so content is not duplicated.
        if ($features === [] && $description !== '') {
            $split = DescriptionMarkdown::splitKeyFeatures($description);
            if ($split['features'] !== []) {
                $description = $split['description'];
                $features = $split['features'];
            }
        }

        return [
            'description' => $description,
            'policy_text' => $rawPolicy !== '' ? $rawPolicy : null,
            'features' => $features ?: null,
            'reviews_data' => $reviews ?: null,
        ];
    }

    public static function resolveImportedSku(array $data, string $name, ?Product $existing = null): string
    {
        $partNumber = trim((string) ($data['part_number'] ?? ''));
        if ($partNumber === '') {
            $partNumber = trim((string) ($data['sku'] ?? ''));
        }
        if ($partNumber !== '') {
            return $partNumber;
        }

        if ($existing?->sku) {
            return $existing->sku;
        }

        return Product::generateSku($name, $existing ? (int) $existing->id : null);
    }

    public function lookup(Request $request)
    {
        $type = (string) $request->query('type', '');
        $q = trim((string) $request->query('q', ''));

        $query = match ($type) {
            'category' => Category::query(),
            'brand' => Brand::where('status', true),
            'seller' => Seller::query(),
            default => null,
        };

        if (! $query) {
            return response()->json(['results' => []]);
        }

        if ($q !== '') {
            $query->where('name', 'like', '%'.$q.'%');
        }

        $models = $query->orderBy('name')->limit(50)->get(['id', 'name']);

        return response()->json([
            'results' => $models->map(fn ($model) => [
                'id' => $model->id,
                'text' => $model->name,
            ])->values()->all(),
        ]);
    }

    private function productLookupOptions(string $type, array $ids)
    {
        $ids = collect($ids)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        return match ($type) {
            'category' => Category::whereIn('id', $ids)->get(['id', 'name']),
            'brand' => Brand::whereIn('id', $ids)->get(['id', 'name']),
            'seller' => Seller::whereIn('id', $ids)->get(['id', 'name']),
            default => collect(),
        };
    }

    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');

        if (! in_array($sortBy, ['id', 'name', 'sku', 'price', 'old_price', 'stock', 'category_id', 'featured', 'status', 'rating', 'created_at'])) {
            $sortBy = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $sortColumn = [
            'sku' => 'part_number',
            'price' => 'cost',
            'stock' => 'vendor_stock',
        ][$sortBy] ?? $sortBy;

        $perPage = $request->query('per_page', 10);
        if ($perPage === 'all') {
            $perPage = Product::count() ?: 10;
        } else {
            $perPage = (int) $perPage;
            if (! in_array($perPage, [10, 20, 50, 100])) {
                $perPage = 10;
            }
        }

        if ($request->has('trashed')) {
            $query = Product::onlyTrashed()->with(['category', 'seller']);
        } else {
            $query = Product::with(['category', 'seller']);
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->query('brand_id'));
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->query('seller_id'));
        }

        if ($request->filled('product_type') && $request->query('product_type') !== 'all' && \Illuminate\Support\Facades\Schema::hasColumn('products', 'product_type')) {
            $query->where('product_type', $request->query('product_type'));
        }

        if ($request->filled('stock_filter') && $request->query('stock_filter') !== 'all') {
            if ($request->query('stock_filter') === 'in_stock') {
                $query->where('vendor_stock', '>', 0);
            } elseif ($request->query('stock_filter') === 'out_of_stock') {
                $query->where('vendor_stock', '<=', 0);
            }
        }

        if ($request->filled('status') && in_array($request->query('status'), ['0', '1'])) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('featured') && in_array($request->query('featured'), ['0', '1'])) {
            $query->where('featured', $request->query('featured'));
        }

        $products = $query->orderBy($sortColumn, $sortDir)->paginate($perPage);

        $products->appends($request->query())->onEachSide(1);

        // Only the currently selected options are printed. The rest are loaded
        // on demand by the AJAX searchable select.
        $categories = $this->productLookupOptions('category', [$request->query('category_id')]);
        $brands = $this->productLookupOptions('brand', [$request->query('brand_id')]);
        $sellers = $this->productLookupOptions('seller', [$request->query('seller_id')]);

        return view('backend.products.index', compact('products', 'sortBy', 'sortDir', 'categories', 'brands', 'sellers'));
    }

    public function restore($id)
    {
        Product::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.products.index')->with('success', 'Product restored successfully!');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();

        return redirect()->route('admin.products.index')->with('success', 'Product permanently deleted!');
    }

    public function show($slug)
    {
        $product = Product::with(['galleryImages', 'category.parent', 'brand', 'fitments', 'seller' => fn ($q) => $q->withCount('products')])
            ->where('slug', $slug)
            ->where('status', true)
            ->hasUsableImage()
            ->firstOrFail();

        // Products without a real main image are not part of the storefront.
        // Return 404 so search engines drop any previously indexed URL.
        if (! $product->hasUsableImage()) {
            abort(404);
        }

        $fitmentSummary = $product->fitments->map(fn ($fitment) => $fitment->label())->values()->all();

        $productSpecs = [];
        $addSpec = function (string $label, $value) use (&$productSpecs) {
            $value = trim((string) $value);
            if ($value === '') {
                return;
            }
            foreach ($productSpecs as $spec) {
                if (strcasecmp($spec['label'], $label) === 0) {
                    return;
                }
            }
            $productSpecs[] = ['label' => $label, 'value' => $value];
        };

        $addSpec('Condition', $product->condition);
        $addSpec('Warranty', $product->warranty_label);
        $addSpec('Weight', $product->weight_kg ? $product->weight_kg.' kg' : null);

        $dimensionParts = [];
        foreach (['length_cm' => 'L', 'width_cm' => 'W', 'height_cm' => 'H'] as $key => $prefix) {
            if (! empty($product->dimensions[$key])) {
                $dimensionParts[] = $prefix.' '.$product->dimensions[$key];
            }
        }
        $addSpec('Dimensions', $dimensionParts ? implode(' × ', $dimensionParts).' cm' : null);
        $addSpec('OEM Numbers', implode(', ', $product->oem_numbers));
        $addSpec('Interchange Numbers', implode(', ', $product->interchange_numbers));

        foreach ($product->specifications as $spec) {
            $addSpec((string) ($spec['label'] ?? $spec['title'] ?? ''), (string) ($spec['value'] ?? ''));
        }

        $related = Product::with('category')->where('status', true)->hasUsableImage()->where('id', '!=', $product->id)->take(4)->get();

        // "Similar Products To Compare" — same fitment/category, other brands.
        $similar = collect();
        $similarLimit = 2;
        $similarBase = Product::with('brand', 'category')
            ->where('status', true)
            ->hasUsableImage()
            ->where('id', '!=', $product->id);

        $primaryFitment = $product->fitments->first();
        if (! empty($product->category_id) && $primaryFitment) {
            $fitmentMatches = (clone $similarBase)
                ->where('category_id', $product->category_id)
                ->whereHas('fitments', function ($q) use ($primaryFitment) {
                    $q->where('make', $primaryFitment->make)
                        ->where('model', $primaryFitment->model)
                        ->where('year_from', '<=', $primaryFitment->year_from)
                        ->where('year_to', '>=', $primaryFitment->year_from);
                })
                ->orderByRaw('IF(brand_id = '.(int) $product->brand_id.', 1, 0) ASC, id ASC')
                ->limit($similarLimit)
                ->get();
            $similar = $fitmentMatches;
        }

        if ($similar->count() < $similarLimit) {
            $exclude = $similar->pluck('id')->push($product->id);
            $categoryCandidates = (clone $similarBase)
                ->where('category_id', $product->category_id)
                ->whereNotIn('id', $exclude)
                ->orderByRaw('IF(brand_id = '.(int) $product->brand_id.', 1, 0) ASC, id ASC')
                ->limit($similarLimit - $similar->count())
                ->get();
            $similar = $similar->merge($categoryCandidates)->take($similarLimit);
        }

        $activeCategoryUrls = [];
        if ($product->category) {
            $activeCategoryUrls[] = url('/category/'.$product->category->slug);
            if ($product->category->parent) {
                $activeCategoryUrls[] = url('/category/'.$product->category->parent->slug);
            }
        }
        view()->share('activeCategoryUrls', $activeCategoryUrls);

        $wishedProductIds = [];
        if (session('user_profile.id')) {
            $wishedProductIds = Wishlist::where('user_id', session('user_profile.id'))
                ->pluck('product_id')
                ->toArray();
        } else {
            $wishedProductIds = session('guest_wishlist', []);
        }
        $inWishlist = in_array($product->id, $wishedProductIds);

        $purchasedOrders = auth()->check()
            ? app(ProductReviewEligibility::class)->purchasedOrders((int) auth()->id(), (int) $product->id)
            : collect();
        $reviewedOrderIds = app(ProductReviewEligibility::class)->reviewedOrderIds(
            $product->reviews_data ?? [],
            (int) auth()->id(),
            $purchasedOrders
        );
        $hasPurchased = $purchasedOrders->isNotEmpty();
        $canReview = $purchasedOrders->contains(
            fn ($order) => ! in_array((int) $order->id, $reviewedOrderIds, true)
        );

        // Fitment badge against the user's selected/default saved vehicle
        $userVehicle = null;
        $vehicleFit = null;
        if (auth()->check()) {
            $selectedVehicleId = session('selected_vehicle_id');
            if ($selectedVehicleId) {
                $userVehicle = Vehicle::where('user_id', auth()->id())->where('id', $selectedVehicleId)->first();
            }
            if (! $userVehicle) {
                $userVehicle = Vehicle::where('user_id', auth()->id())->first();
            }
            if ($userVehicle && $product->fitments->isNotEmpty()) {
                $vehicleFit = $product->fitments->contains(function ($fitment) use ($userVehicle) {
                    $year = (int) $userVehicle->year;

                    return $year >= (int) $fitment->year_from
                        && $year <= (int) $fitment->year_to
                        && strcasecmp(trim((string) $userVehicle->make), trim((string) $fitment->make)) === 0
                        && strcasecmp(trim((string) $userVehicle->model), trim((string) $fitment->model)) === 0;
                });
            }
        }

        // Recently viewed (session-based, most recent first, max 4).
        // Show only products viewed before this one; store the current product
        // so it appears as "recently viewed" on the next product page.
        $recentlyViewedIds = collect(session('recently_viewed', []))
            ->filter(fn ($id) => (int) $id !== (int) $product->id)
            ->values()
            ->take(4)
            ->map(fn ($id) => (int) $id)
            ->all();
        $recentlyViewed = collect($recentlyViewedIds)->prepend($product->id)
            ->unique()
            ->take(4)
            ->values()
            ->all();
        session(['recently_viewed' => $recentlyViewed]);

        return view('frontend.product.show', compact(
            'product',
            'related',
            'inWishlist',
            'wishedProductIds',
            'hasPurchased',
            'canReview',
            'recentlyViewedIds',
            'similar',
            'userVehicle',
            'vehicleFit',
            'fitmentSummary',
            'productSpecs'
        ));
    }

    public function create()
    {
        $categories = $this->productLookupOptions('category', [old('category_id')]);
        $brands = $this->productLookupOptions('brand', [old('brand_id')]);
        $sellers = $this->productLookupOptions('seller', [old('seller_id')]);

        return view('backend.products.create', array_merge(
            compact('categories', 'brands', 'sellers'),
            $this->vehicleFormData()
        ));
    }

    private function vehicleFormData(): array
    {
        $catalog = app(VehicleCatalog::class);

        return [
            'vehicleYears' => $catalog->years()->all(),
            'vehicleMakes' => $catalog->makes()->all(),
        ];
    }

    public function store(Request $request)
    {
        $maxYear = (int) date('Y') + 1;
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0|max:100',
            'old_price' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'seller_id' => 'nullable|exists:sellers,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'slug' => 'nullable|string|max:255',
            'condition' => 'nullable|string|max:40',
            'warranty_months' => 'nullable|integer|min:0|max:600',
            'warranty_text' => 'nullable|string|max:255',
            'weight_kg' => 'nullable|numeric|min:0|max:100000',
            'dimensions.length_cm' => 'nullable|numeric|min:0|max:100000',
            'dimensions.width_cm' => 'nullable|numeric|min:0|max:100000',
            'dimensions.height_cm' => 'nullable|numeric|min:0|max:100000',
            'oem_numbers' => 'nullable|string|max:5000',
            'interchange_numbers' => 'nullable|string|max:5000',
            'fitments' => 'nullable|array|max:100',
            'fitments.*.year_from' => 'nullable|integer|min:1900|max:'.$maxYear,
            'fitments.*.year_to' => 'nullable|integer|min:1900|max:'.$maxYear,
            'fitments.*.make' => 'nullable|string|max:100',
            'fitments.*.model' => 'nullable|string|max:150',
            'fitments.*.engine' => 'nullable|string|max:150',
            'tab_label_1' => 'nullable|string|max:100',
            'tab_label_2' => 'nullable|string|max:100',
            'tab_label_3' => 'nullable|string|max:100',
            'policy_text' => 'nullable|string',
            'features' => 'nullable|string',
            'specifications' => 'nullable|string',
            'reviews_data' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $fitmentRows = $this->validatedFitmentRows($request, $maxYear);

        $data = $request->only(['name', 'description', 'price', 'old_price', 'shipping_cost', 'category_id', 'brand_id', 'seller_id', 'badge', 'product_type', 'stock', 'status', 'tab_label_1', 'tab_label_2', 'tab_label_3', 'policy_text', 'meta_title', 'meta_description', 'condition', 'warranty_months', 'warranty_text', 'weight_kg']);
        $data['featured'] = $request->boolean('featured');
        $data['commission'] = $request->filled('commission') ? (float) $request->commission : null;
        [$data['description'], $data['policy_text']] = $this->normalizeEditorContent(
            (string) ($data['description'] ?? ''),
            (string) ($data['policy_text'] ?? '')
        );
        $data['features'] = $request->filled('features') ? array_filter(explode("\n", str_replace("\r", '', $request->features))) : null;
        $data['specifications'] = self::parseSpecifications($request->specifications);
        $data['reviews_data'] = $request->filled('reviews_data') ? json_decode($request->reviews_data, true) : null;
        $data['oem_numbers'] = $request->oem_numbers;
        $data['interchange_numbers'] = $request->interchange_numbers;
        $data['dimensions'] = [
            'length_cm' => $request->input('dimensions.length_cm'),
            'width_cm' => $request->input('dimensions.width_cm'),
            'height_cm' => $request->input('dimensions.height_cm'),
        ];
        $data['part_number'] = Product::generateSku((string) $request->name);
        $data['slug'] = $this->resolveProductSlug($request, null, (string) $request->name, $data['part_number']);

        if (! FeatureFlags::enabled('vendor_system_activation') || ! FeatureFlags::enabled('product_manage_by_admin')) {
            unset($data['seller_id']);
        }

        $data['approval_status'] = FeatureFlags::enabled('product_approve_by_admin') && ! empty($data['seller_id'])
            ? 'pending'
            : 'approved';

        if ($data['approval_status'] === 'pending') {
            $data['status'] = false;
        }

        if ($request->hasFile('image')) {
            $data['main_image'] = saveImageWithWebp($request->file('image'));
        } elseif ($request->filled('image_from_manager')) {
            $data['main_image'] = 'storage/'.ltrim($request->image_from_manager, '/');
        }

        $product = Product::create($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager);
        }

        $this->attachGalleryImagesFromManager($product, $request->gallery_images_from_manager);

        $this->attachGalleryUploads($product, $request->file('gallery_images'));

        $this->syncProductFitments($product, $fitmentRows);

        if ($product->category_id) {
            $userIds = Wishlist::whereHas('product', fn ($q) => $q->where('category_id', $product->category_id))
                ->where('user_id', '!=', auth()->id())
                ->pluck('user_id')
                ->unique()
                ->toArray();
            $category = Category::find($product->category_id);
            foreach ($userIds as $uid) {
                $user = User::find($uid);
                if ($user) {
                    NotificationHelper::newProductInCategory($user, $product, $category);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function details($id)
    {
        $product = Product::with(['category', 'brand', 'fitments', 'galleryImages'])->findOrFail($id);

        return view('backend.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('fitments')->findOrFail($id);
        $categories = $this->productLookupOptions('category', [old('category_id', $product->category_id)]);
        $brands = $this->productLookupOptions('brand', [old('brand_id', $product->brand_id)]);
        $sellers = $this->productLookupOptions('seller', [old('seller_id', $product->seller_id)]);
        $fitments = old('fitments', $product->fitments->map(fn ($fitment) => [
            'year_from' => $fitment->year_from,
            'year_to' => $fitment->year_to,
            'make' => $fitment->make,
            'model' => $fitment->model,
            'engine' => $fitment->engine,
        ])->all());

        return view('backend.products.edit', array_merge(
            compact('product', 'categories', 'brands', 'sellers', 'fitments'),
            $this->vehicleFormData()
        ));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $maxYear = (int) date('Y') + 1;

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0|max:100',
            'old_price' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'seller_id' => 'nullable|exists:sellers,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'slug' => 'nullable|string|max:255',
            'condition' => 'nullable|string|max:40',
            'warranty_months' => 'nullable|integer|min:0|max:600',
            'warranty_text' => 'nullable|string|max:255',
            'weight_kg' => 'nullable|numeric|min:0|max:100000',
            'dimensions.length_cm' => 'nullable|numeric|min:0|max:100000',
            'dimensions.width_cm' => 'nullable|numeric|min:0|max:100000',
            'dimensions.height_cm' => 'nullable|numeric|min:0|max:100000',
            'oem_numbers' => 'nullable|string|max:5000',
            'interchange_numbers' => 'nullable|string|max:5000',
            'fitments' => 'nullable|array|max:100',
            'fitments.*.year_from' => 'nullable|integer|min:1900|max:'.$maxYear,
            'fitments.*.year_to' => 'nullable|integer|min:1900|max:'.$maxYear,
            'fitments.*.make' => 'nullable|string|max:100',
            'fitments.*.model' => 'nullable|string|max:150',
            'fitments.*.engine' => 'nullable|string|max:150',
            'tab_label_1' => 'nullable|string|max:100',
            'tab_label_2' => 'nullable|string|max:100',
            'tab_label_3' => 'nullable|string|max:100',
            'policy_text' => 'nullable|string',
            'features' => 'nullable|string',
            'specifications' => 'nullable|string',
            'reviews_data' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $fitmentRows = $this->validatedFitmentRows($request, $maxYear);

        $data = $request->only(['name', 'description', 'price', 'old_price', 'shipping_cost', 'category_id', 'brand_id', 'seller_id', 'badge', 'product_type', 'stock', 'status', 'tab_label_1', 'tab_label_2', 'tab_label_3', 'policy_text', 'meta_title', 'meta_description', 'condition', 'warranty_months', 'warranty_text', 'weight_kg']);
        $data['featured'] = $request->boolean('featured');
        $data['commission'] = $request->filled('commission') ? (float) $request->commission : null;
        [$data['description'], $data['policy_text']] = $this->normalizeEditorContent(
            (string) ($data['description'] ?? ''),
            (string) ($data['policy_text'] ?? '')
        );
        $data['features'] = $request->filled('features') ? array_filter(explode("\n", str_replace("\r", '', $request->features))) : null;
        $data['specifications'] = self::parseSpecifications($request->specifications);
        $data['reviews_data'] = $request->filled('reviews_data') ? json_decode($request->reviews_data, true) : null;
        $data['oem_numbers'] = $request->oem_numbers;
        $data['interchange_numbers'] = $request->interchange_numbers;
        $data['dimensions'] = [
            'length_cm' => $request->input('dimensions.length_cm'),
            'width_cm' => $request->input('dimensions.width_cm'),
            'height_cm' => $request->input('dimensions.height_cm'),
        ];
        $data['slug'] = $this->resolveProductSlug($request, $product, (string) $request->name, (string) $product->part_number);

        if (! FeatureFlags::enabled('vendor_system_activation') || ! FeatureFlags::enabled('product_manage_by_admin')) {
            unset($data['seller_id']);
        }

        $effectiveSellerId = $data['seller_id'] ?? $product->seller_id;
        if (
            FeatureFlags::enabled('product_approve_by_admin')
            && $effectiveSellerId
            && (int) $effectiveSellerId !== (int) $product->seller_id
        ) {
            $data['approval_status'] = 'pending';
            $data['status'] = false;
        }

        if ($request->hasFile('image')) {
            $data['main_image'] = saveImageWithWebp($request->file('image'));
        } elseif ($request->filled('image_from_manager')) {
            $data['main_image'] = 'storage/'.ltrim($request->image_from_manager, '/');
        }

        $product->update($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager);
        }

        if ($request->has('delete_gallery_ids')) {
            $this->detachGalleryImages($product, $request->delete_gallery_ids);
        }

        $this->attachGalleryUploads($product, $request->file('gallery_images'));

        $this->attachGalleryImagesFromManager($product, $request->gallery_images_from_manager);

        $this->syncProductFitments($product, $fitmentRows);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    public function approveSellerProduct($id)
    {
        abort_unless(FeatureFlags::enabled('product_approve_by_admin'), 404);

        $product = Product::findOrFail($id);
        $product->approval_status = 'approved';
        $product->status = true;
        $product->save();

        return back()->with('success', 'Seller product approved and published.');
    }

    public function rejectSellerProduct($id)
    {
        abort_unless(FeatureFlags::enabled('product_approve_by_admin'), 404);

        $product = Product::findOrFail($id);
        $product->approval_status = 'rejected';
        $product->status = false;
        $product->save();

        return back()->with('success', 'Seller product rejected and hidden.');
    }

    private function attachGalleryUploads(Product $product, $files): void
    {
        if (! $files) {
            return;
        }

        foreach ($files as $file) {
            if ($file->isValid()) {
                $image = Image::storeFromUpload($file, 'products/gallery', $product);
                $product->galleryImages()->attach($image->id);
            }
        }
    }

    private function detachGalleryImages(Product $product, array $imageIds): void
    {
        $product->galleryImages()->detach($imageIds);

        foreach ($imageIds as $imageId) {
            $image = Image::find($imageId);
            if (! $image) {
                continue;
            }

            $hasOtherOwners = DB::table('image_product')->where('image_id', $imageId)->exists();

            if (
                ! $hasOtherOwners
                && $image->attachable_type === Product::class
                && (int) $image->attachable_id === (int) $product->id
            ) {
                $image->attachable_type = null;
                $image->attachable_id = null;
                $image->is_unused = true;
                $image->save();
            }
        }
    }

    private function attachGalleryImagesFromManager(Product $product, ?string $galleryImagesFromManager): void
    {
        if (! $galleryImagesFromManager) {
            return;
        }

        $paths = json_decode($galleryImagesFromManager, true);
        if (! is_array($paths)) {
            $paths = [$galleryImagesFromManager];
        }

        foreach ($paths as $imgPath) {
            if (! $imgPath) {
                continue;
            }

            try {
                Image::attachToProduct($imgPath, $product);
            } catch (\Throwable $e) {
                \Log::warning('Unable to attach gallery image to product', [
                    'product_id' => $product->id,
                    'path' => $imgPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);

        if (
            ! $product->status
            && FeatureFlags::enabled('product_approve_by_admin')
            && $product->seller_id
            && ($product->approval_status ?? 'approved') !== 'approved'
        ) {
            return back()->with('warning', 'Approve this seller product before publishing it.');
        }

        $product->status = ! $product->status;
        $product->save();

        return back()->with('success', 'Product status updated successfully.');
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->featured = ! $product->featured;
        $product->save();

        return back()->with('success', 'Product featured status updated successfully.');
    }

    /**
     * Update the per-product commission from the All Products table.
     * An empty value clears the override and falls back to the global commission.
     */
    public function updateCommission(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'commission' => 'nullable|numeric|min:0|max:100',
        ]);

        $oldCommission = $product->commission === null
            ? null
            : number_format((float) $product->commission, 2, '.', '');
        $newCommission = $request->filled('commission')
            ? number_format((float) $validated['commission'], 2, '.', '')
            : null;

        DB::transaction(function () use ($product, $oldCommission, $newCommission) {
            $product->commission = $newCommission;
            $product->save();

            if ($oldCommission !== $newCommission) {
                CommissionHistory::recordChange('product', (int) $product->id, $oldCommission, $newCommission);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'commission' => $product->commission,
                'effective' => $product->effectiveCommissionPercent(),
                'total_price' => currency_format(buyerPrice($product->price, $product)),
            ]);
        }

        return back()->with('success', 'Product commission updated successfully.');
    }

    public function duplicate($id)
    {
        $product = Product::findOrFail($id);

        $copy = $product->replicate();
        $copy->name = $product->name.' (Copy)';
        $copy->slug = Str::slug($copy->name).'-'.time();
        $copy->status = false;
        $copy->created_at = now();
        $copy->updated_at = now();
        $copy->save();

        return back()->with('success', 'Product duplicated successfully! You can now edit the copy.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        Product::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids).' product(s) moved to trash.');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        Product::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', count($ids).' product(s) restored.');
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        Product::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', count($ids).' product(s) permanently deleted.');
    }

    public function bulkStatus(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        $status = $request->integer('status') === 1 ? 1 : 0;
        $query = Product::whereIn('id', $ids);

        if ($status === 1 && FeatureFlags::enabled('product_approve_by_admin')) {
            $query->where(function ($q) {
                $q->whereNull('seller_id')
                    ->orWhere('approval_status', 'approved');
            });
        }

        $updated = $query->update(['status' => $status]);

        if ($updated < count($ids)) {
            return back()->with('warning', 'Some seller products were skipped because they are not approved yet.');
        }

        return back()->with('success', count($ids).' product(s) '.($status ? 'activated' : 'deactivated').'.');
    }

    public function bulkFeatured(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (empty($ids)) {
            return back()->with('error', 'No products selected.');
        }

        $featured = $request->integer('featured') === 1 ? 1 : 0;
        Product::whereIn('id', $ids)->update(['featured' => $featured]);

        return back()->with('success', count($ids).' product(s) updated as '.($featured ? 'featured' : 'not featured').'.');
    }

    public function bulkCategory(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (empty($ids) || ! $request->filled('category_id')) {
            return back()->with('error', 'Select at least one product and a destination category.');
        }

        Product::whereIn('id', $ids)->update(['category_id' => $request->input('category_id')]);

        return back()->with('success', count($ids).' product(s) moved to the selected category.');
    }

    public function contactSeller(Request $request)
    {
        abort_unless(FeatureFlags::enabled('product_query_activation'), 404);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'message' => 'required|string|max:2000',
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::find($request->product_id);

        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => 'Seller Contact: '.($product->name ?? 'Product Inquiry'),
            'message' => "Product: {$product->name}\nProduct URL: ".route('product', $product->slug)."\n\n{$request->message}",
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'category' => 'Product',
            'priority' => 'medium',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Your message has been sent to the seller.']);
        }

        return back()->with('success', 'Your message has been sent to the seller.');
    }

    public function importForm()
    {
        return view('backend.products.import', [
            'maxUploadLabel' => '64MB',
        ]);
    }

    public function downloadSampleCsv()
    {
        $headers = ['id', 'name', 'part_number', 'price', 'old_price', 'category', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'brand', 'seller', 'year', 'make', 'model', 'gallery_images', 'policy_text', 'features', 'specifications', 'reviews_data', 'tab_label_1', 'tab_label_2', 'tab_label_3'];
        $rows = [
            ['', 'Brake Pads Set', 'bp-1001', '49.99', '69.99', 'Brakes', '100', 'High quality ceramic brake pads', 'New', 'none', '1', '1', 'https://example.com/images/brake-pads.jpg', 'Duralast', 'AutoZone Seller', '2020', 'Toyota', 'Camry', 'https://example.com/images/brake-pads-2.jpg|https://example.com/images/brake-pads-3.jpg', '<p>We offer a 30-day return policy for unused items in original packaging.</p>', "Premium quality\nEasy installation", "Material::Ceramic\nFriction::Low dust", '[{"name":"Ava","rating":5,"text":"Perfect fit and fast delivery.","deleted":false}]', 'Overview', 'Specifications', 'Reviews'],
            ['', 'Oil Filter', '', '12.99', '', 'Engine', '250', '', 'Sale', 'none', '1', '0', '', 'Apex Gasket', 'AutoZone Seller', '2019', 'Honda', 'Civic', '', '<p>Items can be returned within 14 days if they are not installed or damaged.</p>', '', 'Thread::M20x1.5', '[{"name":"Noah","rating":4,"text":"Works well and shipped quickly.","deleted":false}]', '', '', ''],
            ['', 'LED Headlight Bulb', '', '29.99', '39.99', 'Lighting', '75', 'Bright 12000LM LED bulbs', '', 'none', '1', '1', '', '', '', '', '', '', '', '<p>All purchases include a 12-month warranty and a simple return process.</p>', "6000K bright\nPlug and play", 'Voltage::12V', '[{"name":"Mila","rating":5,"text":"Great brightness and easy install.","deleted":false}]', '', '', ''],
        ];

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample-products-import.csv"',
        ]);
    }

    private function validatedFitmentRows(Request $request, int $maxYear): array
    {
        $rows = [];

        foreach ((array) $request->input('fitments', []) as $index => $item) {
            $item = is_array($item) ? $item : [];
            $row = [
                'year_from' => ($item['year_from'] ?? '') === '' ? null : (int) $item['year_from'],
                'year_to' => ($item['year_to'] ?? '') === '' ? null : (int) $item['year_to'],
                'make' => trim((string) ($item['make'] ?? '')),
                'model' => trim((string) ($item['model'] ?? '')),
                'engine' => trim((string) ($item['engine'] ?? '')),
            ];

            if (! $row['year_from'] && ! $row['year_to'] && $row['make'] === '' && $row['model'] === '' && $row['engine'] === '') {
                continue;
            }

            $messages = [];
            if (! $row['year_from']) {
                $messages['fitments.'.$index.'.year_from'] = 'A start year is required for each fitment row.';
            }
            if (! $row['year_to']) {
                $messages['fitments.'.$index.'.year_to'] = 'An end year is required for each fitment row.';
            }
            if ($row['year_from'] && $row['year_to'] && $row['year_from'] > $row['year_to']) {
                $messages['fitments.'.$index.'.year_to'] = 'The end year must be greater than or equal to the start year.';
            }
            if ($row['make'] === '') {
                $messages['fitments.'.$index.'.make'] = 'Make is required for each fitment row.';
            }
            if ($row['model'] === '') {
                $messages['fitments.'.$index.'.model'] = 'Model is required for each fitment row.';
            }
            if ($row['year_to'] && $row['year_to'] > $maxYear) {
                $messages['fitments.'.$index.'.year_to'] = 'The end year cannot be in the future.';
            }

            if ($messages) {
                throw ValidationException::withMessages($messages);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function resolveProductSlug(Request $request, ?Product $product, string $name, string $partNumber): string
    {
        $raw = trim((string) $request->input('slug', ''));
        if ($raw === '' && $product) {
            $raw = (string) $product->slug;
        }
        if ($raw === '') {
            $raw = $name.'-'.$partNumber;
        }

        $base = Str::slug($raw);
        if ($base === '') {
            $base = Str::slug($name).'-'.Str::lower($partNumber);
        }
        if ($base === '') {
            $base = 'product-'.Str::lower($partNumber);
        }

        $candidate = $base;
        $suffix = Str::lower($partNumber);
        $attempt = 1;
        while (Product::query()->where('slug', $candidate)
            ->when($product, fn ($q) => $q->where('id', '!=', $product->id))
            ->exists()) {
            $candidate = $base.'-'.$suffix.($attempt > 1 ? '-'.$attempt : '');
            $attempt++;
        }

        return $candidate;
    }

    private function syncProductFitments(Product $product, array $rows): void
    {
        $normalize = function ($row) {
            return [
                'year_from' => (int) ($row['year_from'] ?? 0),
                'year_to' => (int) ($row['year_to'] ?? 0),
                'make' => trim((string) ($row['make'] ?? '')),
                'model' => trim((string) ($row['model'] ?? '')),
                'engine' => trim((string) ($row['engine'] ?? '')),
            ];
        };

        $existing = $product->fitments()
            ->orderBy('sort_order')
            ->get(['year_from', 'year_to', 'make', 'model', 'engine'])
            ->map($normalize)
            ->all();
        $incoming = array_values(array_map($normalize, $rows));

        if ($existing === $incoming) {
            return;
        }

        DB::transaction(function () use ($product, $rows) {
            DB::table('product_fitments')->where('product_id', $product->id)->delete();
            DB::table('product_vehicle')
                ->where('product_id', $product->id)
                ->where('note', 'like', ProductFitment::PIVOT_NOTE_PREFIX.'%')
                ->delete();

            $mirrorRows = [];

            foreach ($rows as $sortOrder => $row) {
                $fitment = $product->fitments()->create([
                    'year_from' => $row['year_from'],
                    'year_to' => $row['year_to'],
                    'make' => $row['make'],
                    'model' => $row['model'],
                    'engine' => $row['engine'] ?: null,
                    'sort_order' => $sortOrder,
                ]);

                for ($year = $fitment->year_from; $year <= $fitment->year_to; $year++) {
                    $mirrorRows[] = [
                        'product_id' => $product->id,
                        'year' => $year,
                        'make' => $fitment->make,
                        'model' => $fitment->model,
                        'engine' => $fitment->engine,
                        'note' => ProductFitment::PIVOT_NOTE_PREFIX.$fitment->id,
                    ];
                }
            }

            foreach (array_chunk($mirrorRows, 500) as $chunk) {
                DB::table('product_vehicle')->insert($chunk);
            }
        });

        app(VehicleCatalog::class)->flush();
    }

    private function normalizeEditorContent(string $description, string $policyText): array
    {
        $description = DescriptionMarkdown::toHtml(trim($description));

        $policyText = trim($policyText);
        if ($policyText !== '' && preg_match('/<[a-z][^>]*>/i', $policyText) !== 1) {
            $policyText = nl2br(e($policyText));
        }

        return [$description, $policyText];
    }

    public static function parseSpecifications(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $specs = [];
        $lines = preg_split('/\r\n|\n/', $raw);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '::')) {
                [$label, $value] = array_map('trim', explode('::', $line, 2));
            } else {
                $label = $line;
                $value = '';
            }
            if ($label === '') {
                continue;
            }
            $specs[] = ['label' => $label, 'value' => $value];
        }

        return $specs ?: null;
    }

    public function stockIndex(Request $request)
    {
        $threshold = $request->has('threshold')
            ? max(0, (int) $request->query('threshold'))
            : max(0, (int) Setting::get('low_stock_threshold', 5));
        $perPage = in_array((int) $request->query('per_page'), [10, 20, 50, 100], true) ? (int) $request->query('per_page') : 20;

        $query = Product::with(['category', 'seller'])->where('vendor_stock', '<=', $threshold);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_filter') && in_array($request->query('stock_filter'), ['low', 'out_of_stock'], true)) {
            if ($request->query('stock_filter') === 'out_of_stock') {
                $query->where('vendor_stock', '<=', 0);
            } else {
                $query->whereBetween('vendor_stock', [1, $threshold]);
            }
        }

        $products = $query->orderBy('vendor_stock', 'asc')->paginate($perPage);
        $products->appends($request->query())->onEachSide(1);

        return view('backend.products.stock', compact('products', 'threshold'));
    }

    public function bulkStockUpdate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'integer',
            'stock_qty' => 'required|array',
            'stock_qty.*' => 'integer|min:0',
            'mode' => 'required|in:add,set',
        ]);

        $ids = $request->input('product_id');
        $quantities = $request->input('stock_qty');
        $mode = $request->mode;

        $count = 0;
        DB::transaction(function () use ($ids, $quantities, $mode, &$count) {
            foreach ($ids as $i => $id) {
                $product = Product::find($id);
                if (! $product) {
                    continue;
                }
                $qty = (int) ($quantities[$i] ?? 0);
                if ($mode === 'add') {
                    $product->increment('vendor_stock', $qty);
                } else {
                    $product->update(['vendor_stock' => $qty]);
                }
                $count++;
            }
        });

        return back()->with('success', "Stock updated for {$count} product(s).");
    }
}
