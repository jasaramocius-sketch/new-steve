<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;

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

        $norm = static::normalizePath($this->path) ?? $this->path;
        $matches = function ($value) use ($norm) {
            if ($value === null || trim((string) $value) === '') {
                return false;
            }

            return (static::normalizePath($value) ?? $value) === $norm;
        };

        $locations = [];
        $add = function ($type, $id, $label = '', $route = null, $usage = null) use (&$locations) {
            $key = $type.':'.$id;
            if (! isset($locations[$key])) {
                $locations[$key] = [
                    'type' => $type,
                    'id' => $id,
                    'label' => $label,
                    'route' => $route,
                    'usages' => [],
                ];
            }
            if ($usage) {
                $locations[$key]['usages'][] = $usage;
            }
        };

        // Products via gallery pivot
        foreach ($this->products as $product) {
            $add('Product', $product->id, $product->name, route('admin.products.edit', $product->id), 'Gallery');
        }

        // Entities that store an image path in their image column
        foreach (Product::withTrashed()->whereNotNull('image')->where('image', '!=', '')->get() as $item) {
            if ($matches($item->image)) {
                $add('Product', $item->id, $item->name, route('admin.products.edit', $item->id), 'Primary Image');
            }
        }
        foreach (Category::withTrashed()->whereNotNull('image')->where('image', '!=', '')->get() as $item) {
            if ($matches($item->image)) {
                $add('Category', $item->id, $item->name, route('admin.categories.edit', $item->id));
            }
        }
        foreach (Brand::withTrashed()->whereNotNull('image')->where('image', '!=', '')->get() as $item) {
            if ($matches($item->image)) {
                $add('Brand', $item->id, $item->name, route('admin.brands.edit', $item->id));
            }
        }
        foreach (Seller::withTrashed()->whereNotNull('image')->where('image', '!=', '')->get() as $item) {
            if ($matches($item->image)) {
                $add('Seller', $item->id, $item->name, route('admin.sellers.edit', $item->id));
            }
        }
        foreach (Blog::withTrashed()->whereNotNull('image')->where('image', '!=', '')->get() as $item) {
            if ($matches($item->image)) {
                $add('Blog', $item->id, $item->title, route('admin.blogs.edit', $item->id));
            }
        }
        foreach (Page::withTrashed()->whereNotNull('image')->where('image', '!=', '')->get() as $item) {
            if ($matches($item->image)) {
                $add('Page', $item->id, $item->title, route('admin.pages.edit', $item->id));
            }
        }
        foreach (HomePageSection::whereNotNull('image')->get() as $item) {
            if ($matches($item->image)) {
                $add('Home Page Section', $item->id, $item->section_name, route('admin.home-page.edit'));
            }
            $dealImage = $item->extra_data['deal_image'] ?? null;
            if ($matches($dealImage)) {
                $add('Home Page Section', $item->id, $item->section_name.' (Deal Image)', route('admin.home-page.edit'));
            }
        }

        // Header/footer settings
        foreach (['header_logo', 'header_favicon', 'mobile_logo', 'footer_logo', 'admin_header_bg'] as $key) {
            $value = Setting::get($key);
            if ($matches($value)) {
                $add('Site Setting', $key, ucwords(str_replace('_', ' ', $key)), route('admin.settings.header'));
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
