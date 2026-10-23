<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

class Image extends Model
{
    use SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'original_name',
        'filename',
        'path',
        'url',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'title',
        'caption',
        'is_unused',
        'attachable_type',
        'attachable_id',
        'is_deleted',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_unused' => 'boolean',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'image_product', 'image_id', 'product_id');
    }

    /**
     * Build a list of every place this image is referenced across the site
     * (product primary/gallery, categories, brands, sellers, blogs, pages,
     * home page sections, header settings, and the morph "owner" record).
     */
    public function usageLocations(): array
    {
        if (! $this->relationLoaded('products')) {
            $this->load('products');
        }

        $key = static::imageKey($this->path);
        $candidates = static::pathVariants($key);
        $matches = function ($value) use ($key) {
            if ($key === null || $value === null || trim((string) $value) === '') {
                return false;
            }

            return static::imageKey($value) === $key;
        };

        $locations = [];
        $add = function ($type, $id, $label = '', $route = null, $usage = null) use (&$locations) {
            $locationKey = $type.':'.$id;
            if (! isset($locations[$locationKey])) {
                $locations[$locationKey] = [
                    'type' => $type,
                    'id' => $id,
                    'label' => $label,
                    'route' => $route,
                    'usages' => [],
                ];
            }
            if ($usage) {
                $locations[$locationKey]['usages'][] = $usage;
            }
        };

        // Products via gallery pivot
        foreach ($this->products as $product) {
            $add('Product', $product->id, $product->name, route('admin.products.edit', $product->id), 'Gallery');
        }

        // Entities that keep the image path in their own column. The catalogue
        // schema stores these under different names than the models expose
        // (products.main_image, brands.logo), so resolve the real column
        // instead of assuming it is always called "image".
        $productColumn = static::imageColumn('products', ['main_image', 'image']);
        if ($productColumn) {
            foreach (static::findByImageColumn(Product::class, $productColumn, $candidates) as $item) {
                if ($matches($item->{$productColumn})) {
                    $add('Product', $item->id, $item->name, route('admin.products.edit', $item->id), 'Primary Image');
                }
            }
        }

        $categoryColumn = static::imageColumn('categories', ['image']);
        if ($categoryColumn) {
            foreach (static::findByImageColumn(Category::class, $categoryColumn, $candidates) as $item) {
                if ($matches($item->{$categoryColumn})) {
                    $add('Category', $item->id, $item->name, route('admin.categories.edit', $item->id));
                }
            }
        }

        $brandColumn = static::imageColumn('brands', ['logo', 'image']);
        if ($brandColumn) {
            foreach (static::findByImageColumn(Brand::class, $brandColumn, $candidates) as $item) {
                if ($matches($item->{$brandColumn})) {
                    $add('Brand', $item->id, $item->name, route('admin.brands.edit', $item->id));
                }
            }
        }

        $sellerColumn = static::imageColumn('sellers', ['image']);
        if ($sellerColumn) {
            foreach (static::findByImageColumn(Seller::class, $sellerColumn, $candidates) as $item) {
                if ($matches($item->{$sellerColumn})) {
                    $add('Seller', $item->id, $item->name, route('admin.sellers.edit', $item->id));
                }
            }
        }

        $blogColumn = static::imageColumn('blogs', ['image']);
        if ($blogColumn) {
            foreach (static::findByImageColumn(Blog::class, $blogColumn, $candidates) as $item) {
                if ($matches($item->{$blogColumn})) {
                    $add('Blog', $item->id, $item->title, route('admin.blogs.edit', $item->id));
                }
            }
        }

        $pageColumn = static::imageColumn('pages', ['image']);
        if ($pageColumn) {
            foreach (static::findByImageColumn(Page::class, $pageColumn, $candidates) as $item) {
                if ($matches($item->{$pageColumn})) {
                    $add('Page', $item->id, $item->title, route('admin.pages.edit', $item->id));
                }
            }
        }

        $sectionColumn = static::imageColumn('home_page_sections', ['image']);
        if ($sectionColumn) {
            foreach (static::findByImageColumn(HomePageSection::class, $sectionColumn, $candidates) as $item) {
                if ($matches($item->{$sectionColumn})) {
                    $add('Home Page Section', $item->id, $item->section_name, route('admin.home-page.edit'));
                }
                $dealImage = $item->extra_data['deal_image'] ?? null;
                if ($matches($dealImage)) {
                    $add('Home Page Section', $item->id, $item->section_name.' (Deal Image)', route('admin.home-page.edit'));
                }
            }
        }

        // Header/footer settings
        foreach (['header_logo', 'header_favicon', 'mobile_logo', 'footer_logo', 'admin_header_bg'] as $settingKey) {
            $value = Setting::get($settingKey);
            if ($matches($value)) {
                $add('Site Setting', $settingKey, ucwords(str_replace('_', ' ', $settingKey)), route('admin.settings.header'));
            }
        }

        // Morph owner (kept for backwards compatibility)
        if ($this->attachable_type && $this->attachable) {
            $add(
                class_basename($this->attachable_type),
                $this->attachable_id,
                $this->attachable->name ?? $this->attachable->title ?? '',
                null
            );
        }

        return array_values($locations);
    }

    public function getSizeInKbAttribute(): string
    {
        return $this->size ? round($this->size / 1024, 1).' KB' : '-';
    }

    public function getThumbUrlAttribute(): string
    {
        if (str_starts_with($this->path, 'assets/')) {
            return asset($this->path);
        }

        return asset('storage/'.$this->path);
    }

    public function getFilePathAttribute(): string
    {
        $paths = [
            public_path($this->path),
            storage_path('app/public/'.$this->path),
        ];
        foreach ($paths as $p) {
            if (file_exists($p)) {
                return $p;
            }
        }

        return $paths[0];
    }

    public function scopeUnused($query)
    {
        return $query->whereNull('attachable_type')->whereNull('attachable_id');
    }

    public function scopeConvertible($query)
    {
        return $query->whereIn('mime_type', ['image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/gif']);
    }

    public function hasWebpVersion(): bool
    {
        $baseName = pathinfo($this->filename, PATHINFO_FILENAME);

        return static::where('filename', $baseName.'.webp')->exists();
    }

    public static function storeFromUpload(
        UploadedFile $file,
        string $subdir = 'images',
        ?Model $attachable = null,
        ?string $altText = null,
        ?string $title = null
    ): self {
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/'.now()->format('Y/m'), $filename, 'public');

        // The "public" disk does not throw on failure, so storeAs() hands back
        // false instead of raising. Bail out instead of storing a row that
        // points at a file that was never written.
        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('The uploaded file could not be saved to storage. Please check the storage folder permissions.');
        }

        $dimensions = @getimagesize($file->getPathname());

        return self::create([
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'url' => 'storage/'.$path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'alt_text' => $altText,
            'title' => $title,
            'is_unused' => is_null($attachable),
            'attachable_type' => $attachable ? get_class($attachable) : null,
            'attachable_id' => $attachable?->id,
        ]);
    }

    public static function normalizePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = trim((string) $path);
        $path = preg_replace('#^/+#', '', $path);

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        return $path === '' ? null : $path;
    }

    /**
     * Canonical key for an image reference.
     *
     * Local paths are normalised (no "storage/" or "public/" prefix), while hub
     * (S3) references collapse to their object key so that "s3://bucket/key",
     * "https://bucket.s3.amazonaws.com/key" and a plain "key" path all resolve
     * to the same image. Anything hosted outside the hub bucket is not tracked.
     */
    public static function imageKey(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^(?:s3|https?)://#i', $value)) {
            $parts = parse_url($value);
            $bucket = strtolower((string) config('filesystems.disks.source_images.bucket'));
            $host = strtolower((string) ($parts['host'] ?? ''));

            if ($bucket === '') {
                return null;
            }

            $isHub = $host === $bucket
                || $host === $bucket.'.s3.amazonaws.com'
                || (str_starts_with($host, $bucket.'.s3.') && str_ends_with($host, '.amazonaws.com'));

            if (! $isHub) {
                return null;
            }

            $key = ltrim((string) ($parts['path'] ?? ''), '/');

            return $key === '' ? null : $key;
        }

        return static::normalizePath($value);
    }

    /**
     * Every raw value that resolves to the given image key. Used to look the
     * references up in SQL instead of loading whole tables into memory.
     */
    protected static function pathVariants(?string $key): array
    {
        if ($key === null || $key === '') {
            return [];
        }

        $variants = [
            $key,
            'storage/'.$key,
            'public/'.$key,
            '/'.$key,
        ];

        $bucket = strtolower((string) config('filesystems.disks.source_images.bucket'));
        if ($bucket !== '') {
            $variants[] = 's3://'.$bucket.'/'.$key;

            $region = strtolower((string) config('filesystems.disks.source_images.region'));
            $variants[] = 'https://'.$bucket.'.s3.amazonaws.com/'.$key;
            if ($region !== '') {
                $variants[] = 'https://'.$bucket.'.s3.'.$region.'.amazonaws.com/'.$key;
            }
        }

        return array_values(array_unique($variants));
    }

    /**
     * Resolve the column that actually holds the image path of a table.
     */
    protected static function imageColumn(string $table, array $candidates): ?string
    {
        static $columns = [];

        foreach ($candidates as $candidate) {
            $cacheKey = $table.'.'.$candidate;
            if (! array_key_exists($cacheKey, $columns)) {
                $columns[$cacheKey] = Schema::hasColumn($table, $candidate);
            }

            if ($columns[$cacheKey]) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Fetch the rows of a model whose image column points at one of the paths.
     */
    protected static function findByImageColumn(string $model, string $column, array $candidates)
    {
        if ($candidates === []) {
            return collect();
        }

        $query = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $query->withTrashed();
        }

        return $query->whereIn($column, $candidates)->get();
    }

    public static function markUsed(string $path, ?Model $attachable = null, bool $attach = true): void
    {
        $norm = static::normalizePath($path);
        if (! $norm) {
            return;
        }

        $image = static::where('path', $norm)->first();
        if (! $image) {
            return;
        }

        $image->is_unused = false;

        if ($attach && $attachable) {
            $image->attachable_type = get_class($attachable);
            $image->attachable_id = $attachable->getKey();
        }

        $image->save();
    }

    public static function createFromPath(string $path): self
    {
        $full = resolveImageSource($path);
        $info = $full ? @getimagesize($full) : false;

        return static::create([
            'original_name' => basename($path),
            'filename' => basename($path),
            'path' => $path,
            'url' => 'storage/'.ltrim($path, '/'),
            'mime_type' => $info ? ($info['mime'] ?? 'image/jpeg') : 'image/jpeg',
            'size' => $full ? filesize($full) : null,
            'width' => $info[0] ?? null,
            'height' => $info[1] ?? null,
            'is_unused' => true,
        ]);
    }

    /**
     * Attach an image to a product gallery. The same image row is reused across
     * products (via the image_product pivot) — no duplicate rows or file copies.
     */
    public static function attachToProduct(string $path, Product $product): self
    {
        $norm = static::normalizePath($path);
        if (! $norm) {
            throw new \InvalidArgumentException('Invalid image path.');
        }

        $image = static::where('path', $norm)->first();
        if (! $image) {
            $image = static::createFromPath($norm);
        }

        if ($product->galleryImages()->where('image_product.image_id', $image->id)->doesntExist()) {
            $product->galleryImages()->attach($image->id);
        }

        if ($image->attachable_type === null && $image->attachable_id === null) {
            $image->attachable_type = Product::class;
            $image->attachable_id = $product->getKey();
            $image->is_unused = false;
            $image->save();
        }

        return $image;
    }
}
