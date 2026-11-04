<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['name', 'code', 'symbol', 'rate', 'is_active'];

    protected $casts = [
        'rate' => 'decimal:8',
        'is_active' => 'boolean',
    ];
}
