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
        if ($image !== '' && (isSourceS3ImageReference($image) || preg_match('#^https?://#i', $image))) {
            return $image;
        }

        $localPath = $image !== '' ? normalizeImagePath($image, 'assets/images/thumbnails') : '';
        $checkPath = str_starts_with($localPath, 'uploads/') ? 'storage/'.$localPath : $localPath;
        $isPlaceholder = preg_match('/placeholder|dummy|no[-_]?image|default/i', basename($localPath));
        if ($image !== '' && ! $isPlaceholder && file_exists(public_path($checkPath))) {
            return $image;
        }

        $images = $this->attributes['images_json'] ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        foreach ((array) $images as $entry) {
            $candidate = is_array($entry) ? ($entry['file'] ?? null) : $entry;
            if (is_string($candidate) && isSourceS3ImageReference($candidate)) {
                return $candidate;
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
        $imageCandidates = [$this->attributes['main_image'] ?? null];

        $images = $this->attributes['images_json'] ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        foreach ((array) $images as $entry) {
            $candidate = is_array($entry) ? ($entry['file'] ?? null) : $entry;
            if (is_string($candidate) && trim($candidate) !== '') {
                $imageCandidates[] = $candidate;
            }
        }

        foreach (array_filter(array_map('trim', $imageCandidates), fn ($value) => $value !== '') as $image) {
            $baseName = strtolower(basename($image));
            if (str_contains($baseName, 'placeholder') || str_contains($baseName, 'dummy') || str_contains($baseName, 'no-image') || str_contains($baseName, 'default')) {
                continue;
            }

            if (isSourceS3ImageReference($image) || preg_match('#^https?://#i', $image)) {
                return true;
            }

            $localPath = normalizeImagePath($image, 'assets/images/thumbnails');
            $checkPath = str_starts_with($localPath, 'uploads/') ? 'storage/'.$localPath : $localPath;

            if (file_exists(public_path($checkPath))) {
                return true;
            }
        }

        return false;
    }

    public function scopeHasUsableImage($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('main_image')
                    ->where('main_image', '<>', '')
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%placeholder%'")
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%dummy%'")
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%no-image%'")
                    ->whereRaw("LOWER(COALESCE(main_image, '')) NOT LIKE '%default%'");
            })->orWhere(function ($sub) {
                $sub->whereNotNull('images_json')
                    ->where('images_json', '<>', '')
                    ->where('images_json', '<>', '[]')
                    ->whereRaw("LOWER(COALESCE(images_json, '')) NOT LIKE '%placeholder%'")
                    ->whereRaw("LOWER(COALESCE(images_json, '')) NOT LIKE '%dummy%'")
                    ->whereRaw("LOWER(COALESCE(images_json, '')) NOT LIKE '%no-image%'")
                    ->whereRaw("LOWER(COALESCE(images_json, '')) NOT LIKE '%default%'");
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
