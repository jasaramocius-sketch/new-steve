<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CommissionHistory;
use App\Models\Contact;
use App\Models\Image;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wishlist;
use App\Support\DescriptionMarkdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        if (! empty($data['sku'])) {
            return trim($data['sku']);
        }

        if ($existing?->sku) {
            return $existing->sku;
        }

        return Product::generateSku($name, $existing ? (int) $existing->id : null);
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

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $sellers = Seller::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'sortBy', 'sortDir', 'categories', 'brands', 'sellers'));
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
        $product = Product::with(['galleryImages', 'category.parent', 'brand', 'seller' => fn ($q) => $q->withCount('products')])->where('slug', $slug)->where('status', true)->firstOrFail();
        $related = Product::with('category')->where('status', true)->hasUsableImage()->where('id', '!=', $product->id)->take(4)->get();

        // "Similar Products To Compare" — same part, other brands (Moglix-style).
        $similar = collect();
        $similarLimit = 2;
        $similarBase = Product::with('brand', 'category')
            ->where('status', true)
            ->hasUsableImage()
            ->where('id', '!=', $product->id);

        if (! empty($product->category_id) && ! empty($product->year) && ! empty($product->make) && ! empty($product->model)) {
            $fitmentMatches = (clone $similarBase)
                ->where('category_id', $product->category_id)
                ->where('year', $product->year)
                ->where('make', $product->make)
                ->where('model', $product->model)
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

        $hasPurchased = ReviewController::hasPurchased(auth()->id(), $product->id);

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
            if ($userVehicle && $product->year && $product->make && $product->model) {
                $vehicleFit = (
                    strtolower(trim((string) $userVehicle->year)) === strtolower(trim((string) $product->year)) &&
                    strtolower(trim((string) $userVehicle->make)) === strtolower(trim((string) $product->make)) &&
                    strtolower(trim((string) $userVehicle->model)) === strtolower(trim((string) $product->model))
                );
            }
        }

        // Recently viewed (session-based, most recent first, max 4)
        $recentlyViewed = collect(session('recently_viewed', []))
            ->filter(fn ($id) => (int) $id !== (int) $product->id)
            ->values()
            ->prepend($product->id)
            ->unique()
            ->take(4)
            ->values()
            ->all();
        session(['recently_viewed' => $recentlyViewed]);

        return view('product.show', compact('product', 'related', 'inWishlist', 'wishedProductIds', 'hasPurchased', 'similar', 'userVehicle', 'vehicleFit'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();
        $sellers = Seller::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands', 'sellers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0|max:100',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'seller_id' => 'nullable|exists:sellers,id',
            'year' => 'nullable|integer|min:1900|max:2026',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'tab_label_1' => 'nullable|string|max:100',
            'tab_label_2' => 'nullable|string|max:100',
            'tab_label_3' => 'nullable|string|max:100',
            'policy_text' => 'nullable|string',
            'features' => 'nullable|string',
            'specifications' => 'nullable|string',
            'reviews_data' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price', 'old_price', 'category_id', 'brand_id', 'seller_id', 'year', 'make', 'model', 'badge', 'product_type', 'stock', 'status', 'tab_label_1', 'tab_label_2', 'tab_label_3', 'policy_text']);
        $data['featured'] = $request->boolean('featured');
        $data['commission'] = $request->filled('commission') ? (float) $request->commission : null;
        [$data['description'], $data['policy_text']] = $this->normalizeEditorContent(
            (string) ($data['description'] ?? ''),
            (string) ($data['policy_text'] ?? '')
        );
        $data['sku'] = $request->filled('sku') ? trim($request->sku) : null;
        $data['features'] = $request->filled('features') ? array_filter(explode("\n", str_replace("\r", '', $request->features))) : null;
        $data['specifications'] = $this->parseSpecifications($request->specifications);
        $data['reviews_data'] = $request->filled('reviews_data') ? json_decode($request->reviews_data, true) : null;
        $data['slug'] = Str::slug($request->name).'-'.time();
        $data['sku'] = $data['sku'] ?: Product::generateSku((string) $request->name);

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
        $product = Product::with(['category', 'brand', 'galleryImages'])->findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();
        $sellers = Seller::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'sellers'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0|max:100',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'seller_id' => 'nullable|exists:sellers,id',
            'year' => 'nullable|integer|min:1900|max:2026',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'tab_label_1' => 'nullable|string|max:100',
            'tab_label_2' => 'nullable|string|max:100',
            'tab_label_3' => 'nullable|string|max:100',
            'policy_text' => 'nullable|string',
            'features' => 'nullable|string',
            'specifications' => 'nullable|string',
            'reviews_data' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price', 'old_price', 'category_id', 'brand_id', 'seller_id', 'year', 'make', 'model', 'badge', 'product_type', 'stock', 'status', 'tab_label_1', 'tab_label_2', 'tab_label_3', 'policy_text']);
        $data['featured'] = $request->boolean('featured');
        $data['commission'] = $request->filled('commission') ? (float) $request->commission : null;
        [$data['description'], $data['policy_text']] = $this->normalizeEditorContent(
            (string) ($data['description'] ?? ''),
            (string) ($data['policy_text'] ?? '')
        );
        $data['sku'] = $request->filled('sku') ? trim($request->sku) : ($product->sku ?: Product::generateSku((string) $request->name, (int) $product->id));
        $data['features'] = $request->filled('features') ? array_filter(explode("\n", str_replace("\r", '', $request->features))) : null;
        $data['specifications'] = $this->parseSpecifications($request->specifications);
        $data['reviews_data'] = $request->filled('reviews_data') ? json_decode($request->reviews_data, true) : null;

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

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
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
        Product::whereIn('id', $ids)->update(['status' => $status]);

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
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Your message has been sent to the seller.']);
        }

        return back()->with('success', 'Your message has been sent to the seller.');
    }

    public function importForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 0, ',');

        $header = array_map('trim', $header);
        $expected = ['id', 'name', 'sku', 'price', 'old_price', 'category', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'brand', 'seller', 'year', 'make', 'model', 'gallery_images', 'policy_text', 'buy_return_policy', 'return_policy', 'features', 'feature_list', 'reviews', 'reviews_data', 'specifications', 'tab_label_1', 'tab_label_2', 'tab_label_3'];

        $colMap = [];
        foreach ($header as $i => $col) {
            $colLower = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $col));
            if (in_array($colLower, $expected)) {
                $colMap[$i] = $colLower;
            }
        }

        if (! isset($colMap[array_search('name', $colMap)]) || ! isset($colMap[array_search('price', $colMap)])) {
            fclose($handle);

            return back()->with('error', 'CSV must contain at least "name" and "price" columns.');
        }

        $categories = Category::all()->keyBy(function ($cat) {
            return strtolower(trim($cat->name));
        });

        $brands = Brand::all()->keyBy(function ($brand) {
            return strtolower(trim($brand->name));
        });

        $sellers = Seller::all()->keyBy(function ($seller) {
            return strtolower(trim($seller->name));
        });

        $imported = 0;
        $categoriesCreated = 0;
        $brandsCreated = 0;
        $sellersCreated = 0;
        $galleryImported = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNum++;
            $row = array_map('trim', $row);
            $data = [];

            foreach ($colMap as $colIdx => $colName) {
                $data[$colName] = $row[$colIdx] ?? '';
            }

            if (empty($data['name'])) {
                $errors[] = "Row {$rowNum}: name is required.";

                continue;
            }
            if (! is_numeric($data['price']) || $data['price'] < 0) {
                $errors[] = "Row {$rowNum}: price must be a positive number.";

                continue;
            }

            $existingProduct = null;
            if (! empty($data['id']) && is_numeric($data['id'])) {
                $existingProduct = Product::find((int) $data['id']);
            }

            $categoryId = null;
            if (! empty($data['category'])) {
                $catKey = strtolower(trim($data['category']));
                if (isset($categories[$catKey])) {
                    $categoryId = $categories[$catKey]->id;
                } else {
                    $newCat = Category::create([
                        'name' => trim($data['category']),
                        'slug' => Str::slug(trim($data['category'])),
                        'status' => true,
                    ]);
                    $categories[$catKey] = $newCat;
                    $categoryId = $newCat->id;
                    $categoriesCreated++;
                }
            }

            $brandId = null;
            if (! empty($data['brand'])) {
                $brandKey = strtolower(trim($data['brand']));
                if (isset($brands[$brandKey])) {
                    $brandId = $brands[$brandKey]->id;
                } else {
                    $newBrand = Brand::create([
                        'name' => trim($data['brand']),
                        'slug' => Str::slug(trim($data['brand'])),
                        'status' => true,
                    ]);
                    $brands[$brandKey] = $newBrand;
                    $brandId = $newBrand->id;
                    $brandsCreated++;
                }
            }

            $sellerId = null;
            if (! empty($data['seller'])) {
                $sellerKey = strtolower(trim($data['seller']));
                if (isset($sellers[$sellerKey])) {
                    $sellerId = $sellers[$sellerKey]->id;
                } else {
                    $newSeller = Seller::create([
                        'name' => trim($data['seller']),
                        'slug' => Str::slug(trim($data['seller'])).'-'.uniqid(),
                        'status' => true,
                    ]);
                    $sellers[$sellerKey] = $newSeller;
                    $sellerId = $newSeller->id;
                    $sellersCreated++;
                }
            }

            $normalized = self::normalizeImportedProductData($data);

            $insertData = [
                'name' => $data['name'],
                'price' => $data['price'],
                'old_price' => (! empty($data['old_price']) && is_numeric($data['old_price'])) ? $data['old_price'] : null,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'seller_id' => $sellerId,
                'year' => (! empty($data['year']) && is_numeric($data['year'])) ? (int) $data['year'] : null,
                'make' => $data['make'] ?? null,
                'model' => $data['model'] ?? null,
                'stock' => (! empty($data['stock']) && is_numeric($data['stock'])) ? (int) $data['stock'] : 0,
                'description' => $normalized['description'] ?: ($data['description'] ?? ''),
                'badge' => $data['badge'] ?? null,
                'product_type' => in_array($data['product_type'] ?? '', ['none', 'new_arrival', 'trending', 'best_selling', 'popular']) ? $data['product_type'] : 'none',
                'status' => in_array(($data['status'] ?? ''), ['1', 'yes', 'active', 'true'], true) ? true : false,
                'featured' => in_array(($data['featured'] ?? ''), ['1', 'yes', 'active', 'true'], true) ? true : false,
                'policy_text' => $normalized['policy_text'],
                'features' => $normalized['features'],
                'reviews_data' => $normalized['reviews_data'],
                'specifications' => $this->parseSpecifications($data['specifications'] ?? null),
                'tab_label_1' => $data['tab_label_1'] ?? null,
                'tab_label_2' => $data['tab_label_2'] ?? null,
                'tab_label_3' => $data['tab_label_3'] ?? null,
            ];

            if ($existingProduct) {
                $insertData['slug'] = $existingProduct->slug;
                $insertData['sku'] = self::resolveImportedSku($data, $data['name'], $existingProduct);
            } else {
                $insertData['slug'] = Str::slug($data['name']).'-'.time().'-'.$imported;
                $insertData['added_by'] = 'admin';
                $insertData['sku'] = self::resolveImportedSku($data, $data['name']);
            }

            if (! empty($data['image']) && filter_var($data['image'], FILTER_VALIDATE_URL)) {
                $saved = saveImageFromUrlWithWebp($data['image']);
                if ($saved) {
                    $insertData['image'] = $saved;
                } else {
                    $errors[] = "Row {$rowNum}: image URL could not be downloaded or was not a valid image.";
                }
            }

            try {
                if ($existingProduct) {
                    $existingProduct->update($insertData);
                    $product = $existingProduct;
                    $action = 'updated';
                } else {
                    $product = Product::create($insertData);
                    $action = 'created';
                }
                $imported++;

                if (! empty($data['gallery_images'])) {
                    $galleryUrls = array_filter(array_map('trim', explode('|', $data['gallery_images'])));
                    $subdir = 'uploads/'.now()->format('Y/m');
                    $destDir = storage_path('app/public/'.$subdir);
                    if (! is_dir($destDir)) {
                        mkdir($destDir, 0775, true);
                    }
                    foreach ($galleryUrls as $imgUrl) {
                        if (! filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                            continue;
                        }
                        $response = Http::get($imgUrl);
                        if ($response->failed()) {
                            continue;
                        }
                        $contentType = $response->header('Content-Type');
                        if (! str_contains($contentType, 'image/')) {
                            continue;
                        }
                        $ext = 'jpg';
                        if (str_contains($contentType, 'png')) {
                            $ext = 'png';
                        } elseif (str_contains($contentType, 'webp')) {
                            $ext = 'webp';
                        } elseif (str_contains($contentType, 'gif')) {
                            $ext = 'gif';
                        }
                        $filename = time().'_'.uniqid().'.'.$ext;
                        file_put_contents($destDir.'/'.$filename, $response->body());
                        $info = @getimagesize($destDir.'/'.$filename);
                        Image::create([
                            'original_name' => $filename,
                            'filename' => $filename,
                            'path' => $subdir.'/'.$filename,
                            'url' => 'storage/'.$subdir.'/'.$filename,
                            'mime_type' => $contentType,
                            'size' => filesize($destDir.'/'.$filename),
                            'width' => $info[0] ?? null,
                            'height' => $info[1] ?? null,
                            'is_unused' => false,
                            'attachable_type' => Product::class,
                            'attachable_id' => $product->id,
                        ]);
                        $galleryImported++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        $message = "Imported {$imported} product(s) successfully.";
        $extras = [];
        if ($categoriesCreated > 0) {
            $extras[] = "{$categoriesCreated} new category(ies) created";
        }
        if ($brandsCreated > 0) {
            $extras[] = "{$brandsCreated} new brand(s) created";
        }
        if ($sellersCreated > 0) {
            $extras[] = "{$sellersCreated} new seller(s) created";
        }
        if ($galleryImported > 0) {
            $extras[] = "{$galleryImported} gallery image(s) imported";
        }
        if (! empty($extras)) {
            $message .= ' ('.implode(', ', $extras).')';
        }
        if (! empty($errors)) {
            $message .= ' '.count($errors).' error(s): '.implode('; ', array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= ' (and '.(count($errors) - 10).' more)';
            }
        }

        return redirect()->route('admin.products.index')->with('success', $message);
    }

    public function downloadSampleCsv()
    {
        $headers = ['id', 'name', 'sku', 'price', 'old_price', 'category', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'brand', 'seller', 'year', 'make', 'model', 'gallery_images', 'policy_text', 'features', 'specifications', 'reviews_data', 'tab_label_1', 'tab_label_2', 'tab_label_3'];
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

    public function exportCsv()
    {
        $headers = ['id', 'name', 'sku', 'price', 'old_price', 'category', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'brand', 'seller', 'year', 'make', 'model', 'gallery_images', 'policy_text', 'features', 'specifications', 'reviews_data', 'tab_label_1', 'tab_label_2', 'tab_label_3'];

        $products = Product::with('category', 'brand', 'seller', 'galleryImages')->get();

        $callback = function () use ($headers, $products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($products as $p) {
                $galleryUrls = $p->galleryImages->map(function ($img) {
                    return $img->url ? url($img->url) : '';
                })->filter()->values()->implode('|');

                $features = is_array($p->features) ? implode("\n", array_filter($p->features)) : '';

                $specsLines = [];
                foreach ((array) $p->specifications as $spec) {
                    $label = trim($spec['label'] ?? '');
                    $value = trim($spec['value'] ?? '');
                    $specsLines[] = ($label !== '' && $value !== '') ? $label.'::'.$value : ($label ?: $value);
                }
                $specs = implode("\n", array_filter($specsLines));

                $reviewsData = is_array($p->reviews_data) && $p->reviews_data ? json_encode($p->reviews_data) : '';

                fputcsv($handle, [
                    $p->id,
                    $p->name,
                    $p->sku ?? '',
                    $p->price,
                    $p->old_price ?? '',
                    $p->category->name ?? '',
                    $p->stock ?? 0,
                    $p->description ?? '',
                    $p->badge ?? '',
                    $p->product_type ?? 'none',
                    $p->status ? '1' : '0',
                    $p->featured ? '1' : '0',
                    $p->image ? url(storedPath($p->image, 'assets/images/thumbnails')) : '',
                    $p->brand->name ?? '',
                    $p->seller->name ?? '',
                    $p->year ?? '',
                    $p->make ?? '',
                    $p->model ?? '',
                    $galleryUrls,
                    $p->policy_text ?? '',
                    $features,
                    $specs,
                    $reviewsData,
                    $p->tab_label_1 ?? '',
                    $p->tab_label_2 ?? '',
                    $p->tab_label_3 ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-export-'.date('Y-m-d').'.csv"',
        ]);
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

    private function parseSpecifications(?string $raw): ?array
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
        $threshold = max(0, (int) $request->query('threshold', 5));
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

        return view('admin.products.stock', compact('products', 'threshold'));
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
