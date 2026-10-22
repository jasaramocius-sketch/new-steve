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
        'core_price',
        'super_stock',
        'vendor_stock',
        'stock',
        'reviews_data',
        'packs',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'features_json' => 'array',
        'specs_json' => 'array',
        'images_json' => 'array',
        'cost' => 'decimal:2',
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
        return $this->attributes['main_image'] ?? null;
    }

    public function setImageAttribute($value)
    {
        $this->attributes['main_image'] = $value;
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
        return DB::table(DB::raw('products FORCE INDEX (idx_category_status)'))
            ->where('status', true)
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');
    }
}
