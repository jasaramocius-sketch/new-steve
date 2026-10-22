<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'price',
        'coupon_discount',
        'is_deleted',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
