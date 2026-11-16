<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductConversationMessage extends Model
{
    protected $fillable = [
        'product_conversation_id',
        'sender_id',
        'sender_type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(ProductConversation::class, 'product_conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
