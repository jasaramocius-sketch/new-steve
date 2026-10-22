<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SteveStore\PageBuilder\Traits\HasBlocks;

class Page extends Model
{
    use HasBlocks, Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'image',
        'content',
        'show_title',
        'meta_title',
        'meta_description',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_title' => 'boolean',
    ];
}
