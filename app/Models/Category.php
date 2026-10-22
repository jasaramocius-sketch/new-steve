<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use Revisable;
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'level',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function getAllDescendantIds(): array
    {
        static $childMap = null;

        if ($childMap === null) {
            $childMap = [];
            foreach (self::query()->get(['id', 'parent_id']) as $row) {
                $childMap[$row->parent_id ?? 0][] = $row->id;
            }
        }

        $ids = [$this->id];
        $stack = [$this->id];

        while ($stack) {
            $current = array_pop($stack);
            foreach ($childMap[$current] ?? [] as $childId) {
                $ids[] = $childId;
                $stack[] = $childId;
            }
        }

        return $ids;
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id')->orderBy('name');
    }

    public static function searchCategoryTree()
    {
        return static::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();
    }

    public function parent_category()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function getImageAttribute()
    {
        return $this->attributes['image'] ?? null;
    }

    public function getStatusAttribute()
    {
        return (bool) ($this->attributes['status'] ?? true);
    }

    public function getIsDeletedAttribute()
    {
        return (bool) ($this->attributes['is_deleted'] ?? false);
    }

    public function getDisplayImagePath(): string
    {
        $storedImage = trim((string) ($this->attributes['image'] ?? ''));
        if ($storedImage !== '') {
            $candidate = storedPath($storedImage, 'assets/images/categories');
            $checkPath = str_starts_with($candidate, 'uploads/') ? 'storage/'.$candidate : $candidate;
            if (file_exists(public_path($checkPath))) {
                return $candidate;
            }
        }

        $fallbacks = [
            'engine-parts' => '1730804224silver-device-with-white-cover-that-says-word-it-minpng.png',
            'body-exterior' => '173080421331792401shockabsorber2-minpng.png',
            'interior-parts' => '1730804204oval-shaped-car-mirror-minpng.png',
            'electrical-lighting' => '1730804142c1b1489eb0545231cf0bfa44a827e2ae-minpng.png',
            'brakes-brake-parts' => '173080413017369231014177a5f-cedb-407a-8a1d-a6aa0f40249d-minpng.png',
            'transmission-drivetrain' => '1730804110engineering-concept-project-heating-house-thermostatic-valve-copper-fitting-project-minpng.png',
            'suspension-steering' => '1730804093huelpful-steering-wheel-isolated-white-background-minpng.png',
        ];

        $slug = Str::slug((string) ($this->slug ?: $this->name));
        $fallback = $fallbacks[$slug] ?? null;
        if ($fallback) {
            $candidate = 'assets/images/categories/'.$fallback;
            if (file_exists(public_path($candidate))) {
                return $candidate;
            }
        }

        return 'assets/images/placeholder.png';
    }

    public function getDisplayImage(): string
    {
        return basename($this->getDisplayImagePath());
    }

    /**
     * Get total products count including all descendant categories.
     * Requires withCount('products') and children to be eager loaded.
     */
    public function getTotalProductsCountAttribute(): int
    {
        $count = $this->products_count ?? 0;
        if ($this->relationLoaded('children')) {
            foreach ($this->children as $child) {
                $count += $child->total_products_count;
            }
        }

        return $count;
    }
}
