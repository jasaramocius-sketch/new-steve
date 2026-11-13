<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'flutter_code', 'rtl', 'is_active'];

    protected $casts = [
        'rtl' => 'boolean',
        'is_active' => 'boolean',
    ];
}
