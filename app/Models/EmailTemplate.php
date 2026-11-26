<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'subject',
        'body_html',
        'description',
        'placeholders',
        'is_enabled',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_enabled' => 'boolean',
    ];
}
