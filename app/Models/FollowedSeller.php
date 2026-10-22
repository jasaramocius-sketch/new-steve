<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowedSeller extends Model
{
    use SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'user_id',
        'seller_id',
        'seller_name',
        'location',
        'products',
        'rating',
        'followers',
        'description',
        'is_deleted',
    ];

    protected $casts = [
        'products' => 'integer',
        'rating' => 'decimal:1',
        'followers' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
