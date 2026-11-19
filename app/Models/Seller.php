<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'location',
        'shipping_cost',
        'image',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
        'shipping_cost' => 'decimal:2',
    ];

    public function followedBy()
    {
        return $this->hasMany(FollowedSeller::class, 'seller_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getFollowersCountAttribute()
    {
        return $this->followedBy()->count();
    }
}
