<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'banner',
        'icon',
        'cover_image',
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
        $image = trim((string) ($this->attributes['banner'] ?? ''));
        return $image !== '' ? $image : null;
    }

    public function setImageAttribute($value): void
    {
        $this->attributes['banner'] = $value;
        $this->attributes['cover_image'] = $value;
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
        $storedImage = trim((string) ($this->image ?? ''));
        if ($storedImage !== '') {
            $candidate = storedPath($storedImage, 'assets/images/categories');
            if (preg_match('#^https?://#i', $candidate)) {
                return $candidate;
            }

            $checkPath = str_starts_with($candidate, 'uploads/') ? 'storage/'.$candidate : $candidate;
            if (file_exists(public_path($checkPath))) {
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
        if (array_key_exists('total_products_count', $this->attributes)) {
            return (int) $this->attributes['total_products_count'];
        }

        $count = $this->products_count ?? 0;
        if ($this->relationLoaded('children')) {
            foreach ($this->children as $child) {
                $count += $child->total_products_count;
            }
        }

        return $count;
    }
}
