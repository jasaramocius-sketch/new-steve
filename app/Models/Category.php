<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

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
        'image',
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
        $image = trim((string) ($this->attributes['image'] ?? ''));
        if (isSourceS3ImageReference($image)) {
            return $image;
        }

        return Cache::remember('category.aws_image.'.$this->id, now()->addHours(6), function () {
            $bucket = (string) config('filesystems.disks.source_images.bucket');
            if ($bucket === '') {
                return null;
            }

            $categoryIds = $this->getAllDescendantIds();
            $primaryImage = Product::whereIn('category_id', $categoryIds)
                ->where('main_image', 'like', 's3://'.$bucket.'/%')
                ->orderBy('id')
                ->value('main_image');

            if ($primaryImage) {
                return $primaryImage;
            }

            $gallery = Product::whereIn('category_id', $categoryIds)
                ->where('images_json', 'like', '%s3://'.$bucket.'/%')
                ->orderBy('id')
                ->value('images_json');
            foreach ((array) (is_string($gallery) ? json_decode($gallery, true) : $gallery) as $entry) {
                $candidate = is_array($entry) ? ($entry['file'] ?? null) : $entry;
                if (is_string($candidate) && isSourceS3ImageReference($candidate)) {
                    return $candidate;
                }
            }

            return null;
        });
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
        $count = $this->products_count ?? 0;
        if ($this->relationLoaded('children')) {
            foreach ($this->children as $child) {
                $count += $child->total_products_count;
            }
        }

        return $count;
    }
}
