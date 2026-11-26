<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use Revisable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'reply',
        'replied_by',
        'replied_at',
        'user_id',
        'product_id',
        'category',
        'priority',
        'legacy_ticket_id',
        'legacy_conversation_id',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public static function categories(): array
    {
        return [
            'General' => 'General',
            'Order' => 'Order',
            'Product' => 'Product',
            'Payment' => 'Payment',
            'Shipping' => 'Shipping',
            'Return' => 'Return / Refund',
            'Technical' => 'Technical',
        ];
    }

    public static function priorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }

    public function categoryLabel(): string
    {
        return static::categories()[$this->category] ?? ($this->category ?: 'General');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
