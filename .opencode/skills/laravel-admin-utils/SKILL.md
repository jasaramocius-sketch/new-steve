---
name: laravel-admin-utils
description: Use when adding admin utility features in Laravel — including CSV import/export, per-page selectors with "All" option, auto-create missing categories/brands during import, image manager picker, or admin product management utilities.
---

# Laravel Admin Utils

## CSV Import/Export

### Export CSV
```php
public function exportCsv() {
    $products = Product::with('category', 'brand')->get();
    $headers = ['Content-Type' => 'text/csv'];
    $callback = function() use ($products) {
        $file = fopen('php://output', 'w');
        // Header row
        fputcsv($file, ['id', 'name', 'price', 'old_price', 'category', 'brand', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'gallery_images', 'year', 'make', 'model']);
        foreach ($products as $product) {
            fputcsv($file, [
                $product->id,
                $product->name,
                $product->price,
                $product->old_price,
                $product->category->name ?? '',
                $product->brand->name ?? '',
                $product->stock,
                $product->short_description,
                $product->badge,
                $product->product_type,
                $product->status ? '1' : '0',
                $product->featured ? '1' : '0',
                $product->image,
                implode('|', $product->galleryImages->pluck('path')->toArray()),
                $product->year,
                $product->make,
                $product->model,
            ]);
        }
        fclose($file);
    };
    return response()->stream($callback, 200, $headers);
}
```

### Import CSV (with Auto-Create + Update by ID)
```php
public function import(Request $request) {
    $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);
    $file = fopen($request->file('csv_file')->getPathname(), 'r');
    $header = fgetcsv($file);

    $imported = 0; $errors = []; $categoriesCreated = 0; $brandsCreated = 0;
    $categories = []; $brands = [];

    while (($row = fgetcsv($file)) !== false) {
        $data = array_combine($header, $row);

        // Auto-create missing category
        if (!empty($data['category'])) {
            if (!isset($categories[strtolower($data['category'])])) {
                $cat = Category::where('name', $data['category'])->first();
                if (!$cat) {
                    $cat = Category::create(['name' => $data['category'], 'slug' => Str::slug($data['category']), 'status' => true]);
                    $categoriesCreated++;
                }
                $categories[strtolower($data['category'])] = $cat;
            }
            $data['category_id'] = $categories[strtolower($data['category'])]->id;
        }

        // Auto-create missing brand
        if (!empty($data['brand'])) {
            if (!isset($brands[strtolower($data['brand'])])) {
                $brand = Brand::where('name', $data['brand'])->first();
                if (!$brand) {
                    $brand = Brand::create(['name' => $data['brand'], 'slug' => Str::slug($data['brand']), 'status' => true]);
                    $brandsCreated++;
                }
                $brands[strtolower($data['brand'])] = $brand;
            }
            $data['brand_id'] = $brands[strtolower($data['brand'])]->id;
        }

        // Update existing or create new
        if (!empty($data['id']) && is_numeric($data['id'])) {
            $existing = Product::find($data['id']);
            if ($existing) {
                $existing->update($data);
                $imported++;
                continue;
            }
        }

        // Create new product
        $data['slug'] = Str::slug($data['name']) . '-' . time();
        Product::create($data);
        $imported++;
    }
    fclose($file);

    return redirect()->back()->with('success', "Imported {$imported} product(s). ({$categoriesCreated} categories, {$brandsCreated} brands created)");
}
```

### Download Sample CSV
```php
public function downloadSampleCsv() {
    $headers = ['Content-Type' => 'text/csv'];
    $callback = function() {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['id', 'name', 'price', 'old_price', 'category', 'brand', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'gallery_images', 'year', 'make', 'model']);
        fputcsv($file, ['', 'Sample Product', '29.99', '39.99', 'Brakes', 'Bosch', '50', 'Sample description', 'New', 'standard', '1', '0', '', '', '2020', 'Toyota', 'Camry']);
        fclose($file);
    };
    return response()->stream($callback, 200, $headers, 'sample_products.csv');
}
```

## Per-Page Selector with "All"

```blade
<select onchange="window.location.href=addParam('per_page', this.value)">
  @foreach([10, 25, 50, 100, 'all'] as $opt)
    <option value="{{ $opt }}" {{ request('per_page', 10) == $opt ? 'selected' : '' }}>
      {{ $opt === 'all' ? 'All' : $opt }}
    </option>
  @endforeach
</select>
```

```php
$perPage = request('per_page', 10);
$items = $perPage === 'all'
    ? $query->get()
    : $query->paginate($perPage)->withQueryString();
```

## Image Manager Picker

Reusable partial: `resources/views/admin/partials/image-manager-picker.blade.php`
- Single select: hidden input `image_from_manager`
- Multi select: hidden input `gallery_images_from_manager` (JSON array)
- AJAX grid with search/pagination
- Upload from picker

### Controller Pattern
```php
if ($request->filled('image_from_manager')) {
    // Copy from Image Manager storage → target directory
    $sourcePath = public_path('assets/images/' . $request->image_from_manager);
    $destPath = public_path('assets/images/thumbnails/' . basename($request->image_from_manager));
    copy($sourcePath, $destPath);
    $data['image'] = 'thumbnails/' . basename($request->image_from_manager);
} elseif ($request->hasFile('image')) {
    // Existing file upload logic
}
```

## Common Pitfalls

- CSV import: auto-create categories/brands with `Str::slug()` for slug
- Import by ID: check `Product::find($data['id'])` before create vs update
- Gallery images: pipe-separated in CSV (`|`), split with `explode('|', $data['gallery_images'])`
- Image Manager: always check `image_from_manager` first, then file upload
- Per-page "All": use `get()` instead of `paginate()` — no pagination for all
