<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'parent_id',
        'is_deleted',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'blog_category_id');
    }

    public function additionalBlogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_additional_category')->withTimestamps();
    }

    public function scopeWithBlogCounts(Builder $query): Builder
    {
        return $query->withCount([
            'blogs' => fn ($q) => $q->accessible(),
            'additionalBlogs' => fn ($q) => $q->accessible(),
        ]);
    }

    public function getTotalBlogsCountAttribute(): int
    {
        return (int) ($this->blogs_count ?? 0) + (int) ($this->additional_blogs_count ?? 0);
    }
}
