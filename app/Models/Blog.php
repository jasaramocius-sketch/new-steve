<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use SteveStore\PageBuilder\Traits\HasBlocks;

class Blog extends Model
{
    use HasBlocks, Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'title',
        'slug',
        'image',
        'details',
        'author',
        'status',
        'published_at',
        'blog_category_id',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function additionalCategories()
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_additional_category')->withTimestamps();
    }

    public function allCategories()
    {
        return collect([$this->category])
            ->filter()
            ->merge($this->additionalCategories)
            ->unique('id')
            ->values();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeAccessible(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', 'published')
                ->where(fn (Builder $q2) => $q2->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->orWhere(fn (Builder $q2) => $q2->where('status', 'scheduled')->where('published_at', '<=', now()));
        });
    }

    /**
     * Immediately transition any scheduled blog whose publish time has passed to
     * "published" (both status and database), without a cron job. This is a cheap,
     * idempotent UPDATE.
     */
    public static function autoPublishDueScheduled(): void
    {
        static::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published']);
    }

    public function getPostDateAttribute(): Carbon
    {
        return $this->published_at ?? $this->created_at;
    }
}
