<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVehicle extends Model
{
    protected $table = 'product_vehicle';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'base_vehicle_id',
        'engine_base_id',
        'year',
        'make',
        'model',
        'submodel',
        'region_id',
        'engine',
        'qty',
        'note',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
