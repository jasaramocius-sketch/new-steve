<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'user_id',
        'product_id',
        'is_deleted',
    ];

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }
}
