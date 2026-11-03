<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Product extends Model
{
    use Revisable;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'brand_id',
        'category_id',
        'part_number',
        'slug',
        'name',
        'short_description',
        'overview',
        'description',
        'features_json',
        'specs_json',
        'images_json',
        'main_image',
        'gtin_upc',
        'hazmat',
        'cost',
        'commission',
        'core_price',
        'super_stock',
        'vendor_stock',
        'stock',
        'featured',
        'reviews_data',
        'rating',
        'reviews',
        'packs',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'featured' => 'boolean',
        'features_json' => 'array',
        'specs_json' => 'array',
        'images_json' => 'array',
        'cost' => 'decimal:2',
        'commission' => 'decimal:2',
        'core_price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Compatibility accessors/mutators
    |--------------------------------------------------------------------------
    | The application code (controllers/views) was written against a different
    | column naming convention. These map the app-facing attribute names onto
    | the actual catalogue columns stored in this schema.
    */

    public function getPriceAttribute()
    {
        return $this->attributes['cost'] ?? null;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['cost'] = $value;
    }

    /**
     * Commission percent that applies to this product.
     * Product-level value wins; otherwise the global setting is used.
     */
    public function effectiveCommissionPercent(): float
    {
        $commission = $this->attributes['commission'] ?? null;

        if ($commission !== null && $commission !== '') {
            return max(0, min(100, (float) $commission));
        }

        return function_exists('globalCommissionPercent') ? globalCommissionPercent() : 0.0;
    }

    public function getSkuAttribute()
    {
        return $this->attributes['part_number'] ?? null;
    }

    public function setSkuAttribute($value)
    {
        $this->attributes['part_number'] = $value;
    }

    public function getImageAttribute()
    {
        $image = trim((string) ($this->attributes['main_image'] ?? ''));

        // Product images must come from the AWS source bucket. Anything else
        // (legacy stautoparts filenames, assets/images/thumbnails paths, or
        // third-party URLs) is intentionally treated as "no image" so the
        // placeholder is shown instead of an outdated image.
        if ($image !== '' && isSourceS3ImageReference($image)) {
            return $image;
        }

        // Images uploaded through this app's own media manager are valid too.
        // They live under storage/app/public/uploads and are referenced as
        // "storage/uploads/..." or "uploads/...".
        if ($image !== '') {
            $localPath = (string) normalizeImagePath($image);
            $isOwnUpload = str_starts_with($localPath, 'uploads/');
            $isPlaceholder = (bool) preg_match('/placeholder|dummy|no[-_]?image|default/i', basename($localPath));

            if ($isOwnUpload && ! $isPlaceholder && file_exists(public_path('storage/'.$localPath))) {
                return $localPath;
            }
        }

        return null;
    }

    public function setImageAttribute($value)
    {
        $this->attributes['main_image'] = $value;
    }

    public function hasUsableImage(): bool
    {
        $image = trim((string) ($this->attributes['main_image'] ?? ''));

        if ($image === '') {
            return false;
        }

        $baseName = strtolower(basename($image));
        if (str_contains($baseName, 'placeholder') || str_contains($baseName, 'dummy') || str_contains($baseName, 'no-image') || str_contains($baseName, 'default')) {
            return false;
        }

        if (isSourceS3ImageReference($image)) {
            return true;
        }

        $localPath = normalizeImagePath($image);
        if (str_starts_with($localPath, 'uploads/') && ! preg_match('/placeholder|dummy|no[-_]?image|default/i', basename($localPath)) && file_exists(public_path('storage/'.$localPath))) {
            return true;
        }

        return false;
    }

    /**
     * Only products with a real AWS source-bucket image (or a valid own upload)
     * should be visible in storefront listings. Products that would fall back
     * to the placeholder are hidden entirely.
     */
    public function scopeHasUsableImage($query)
    {
        $bucket = strtolower((string) config('filesystems.disks.source_images.bucket', 'autoparts-hub'));

        if ($bucket === '') {
            return $query->whereRaw('1 = 0');
        }

        $s3Patterns = [
            's3://'.$bucket.'/%',
            'http://'.$bucket.'.s3.amazonaws.com/%',
            'https://'.$bucket.'.s3.amazonaws.com/%',
            'http://'.$bucket.'.s3.%amazonaws.com/%',
            'https://'.$bucket.'.s3.%amazonaws.com/%',
        ];

        // Only `main_image` decides storefront visibility. `images_json` may
        // contain gallery images, but a product without a usable main image
        // must not appear in listings (it would render the placeholder).
        return $query->where(function ($q) use ($s3Patterns) {
            foreach ($s3Patterns as $pattern) {
                $q->orWhere('main_image', 'like', $pattern);
            }

            // Images uploaded through this app's own media manager.
            $q->orWhere(function ($upload) {
                $upload->where(function ($path) {
                    $path->where('main_image', 'like', 'uploads/%')
                        ->orWhere('main_image', 'like', 'storage/uploads/%');
                })
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%placeholder%'")
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%dummy%'")
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%no-image%'")
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%default%'");
            });
        });
    }

    public function getStockAttribute()
    {
        return $this->attributes['vendor_stock'] ?? 0;
    }

    public function setStockAttribute($value)
    {
        $this->attributes['vendor_stock'] = $value;
    }

    public function getCorePriceAttribute()
    {
        return $this->attributes['core_price'] ?? null;
    }

    public function getGtinUpcAttribute()
    {
        return $this->attributes['gtin_upc'] ?? null;
    }

    public function getFeaturesAttribute()
    {
        return $this->asArray($this->features_json ?? $this->attributes['features_json'] ?? null);
    }

    public function setFeaturesAttribute($value)
    {
        $this->attributes['features_json'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getSpecificationsAttribute()
    {
        return $this->asArray($this->specs_json ?? $this->attributes['specs_json'] ?? null);
    }

    public function setSpecificationsAttribute($value)
    {
        $this->attributes['specs_json'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getReviewsDataAttribute($value = null)
    {
        return $this->asArray($value ?? $this->attributes['reviews_data'] ?? null);
    }

    public function setReviewsDataAttribute($value)
    {
        $this->attributes['reviews_data'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getRatingAttribute()
    {
        return $this->attributes['rating'] ?? 0;
    }

    public function getReviewsAttribute()
    {
        return $this->attributes['reviews'] ?? 0;
    }

    public function getOldPriceAttribute()
    {
        return $this->attributes['old_price'] ?? null;
    }

    public function getBadgeAttribute()
    {
        return $this->attributes['badge'] ?? null;
    }

    public function getTabLabel1Attribute()
    {
        return $this->attributes['tab_label_1'] ?? 'Description';
    }

    public function getTabLabel2Attribute()
    {
        return $this->attributes['tab_label_2'] ?? 'Specifications';
    }

    public function getTabLabel3Attribute()
    {
        return $this->attributes['tab_label_3'] ?? 'Features';
    }

    public function getPolicyTextAttribute()
    {
        return $this->attributes['policy_text'] ?? null;
    }

    public function getIsDeletedAttribute()
    {
        return (bool) ($this->attributes['is_deleted'] ?? false);
    }

    protected function asArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function galleryImages()
    {
        return $this->belongsToMany(Image::class, 'image_product', 'product_id', 'image_id');
    }

    public static function generateSku(string $name, ?int $excludeId = null): string
    {
        $base = strtoupper(Str::substr(Str::slug($name), 0, 12));

        do {
            $candidate = $base.'-'.strtoupper(Str::random(5));
        } while (self::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where('part_number', $candidate)
            ->exists());

        return $candidate;
    }

    /**
     * Product counts keyed by category_id, using a covering index scan for speed.
     */
    public static function statusCountsByCategoryId(): \Illuminate\Support\Collection
    {
        return self::query()
            ->where('status', true)
            ->hasUsableImage()
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');
    }
}
