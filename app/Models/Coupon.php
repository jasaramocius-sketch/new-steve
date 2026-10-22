<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'status' => 'boolean',
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    public function isValid(): bool
    {
        if (! $this->status) {
            return false;
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isValid()) {
            return 0;
        }
        if ($this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min((float) $this->value, $subtotal);
        }

        $discount = $subtotal * ((float) $this->value / 100);

        return min($discount, $subtotal);
    }
}
