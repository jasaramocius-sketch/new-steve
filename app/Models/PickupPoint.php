<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickupPoint extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'manager', 'location', 'contact', 'is_open'];

    protected $casts = [
        'is_open' => 'boolean',
    ];
}
