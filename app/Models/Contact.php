<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Revisable;

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
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

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
