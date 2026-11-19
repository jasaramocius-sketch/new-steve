<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductConversation extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'seller_id',
        'subject',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function messages()
    {
        return $this->hasMany(ProductConversationMessage::class)->orderBy('created_at');
    }
}
