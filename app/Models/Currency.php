<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'symbol', 'rate', 'is_active'];

    protected $casts = [
        'rate' => 'decimal:8',
        'is_active' => 'boolean',
    ];
}
