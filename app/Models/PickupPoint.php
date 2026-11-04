<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupPoint extends Model
{
    protected $fillable = ['name', 'manager', 'location', 'contact', 'is_open'];

    protected $casts = [
        'is_open' => 'boolean',
    ];
}
