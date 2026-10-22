<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status',
        'shipping_address_id',
        'shipping_details',
        'delivery_type',
        'payment_method',
        'payment_details',
        'additional_info',
        'shipping_fee',
        'tax',
        'coupon_code',
        'coupon_discount',
        'stock_deducted',
        'tracking_number',
        'tracking_carrier',
        'status_history',
        'is_deleted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Decrement product stock by each item's qty.
     */
    public function deductStock(): void
    {
        foreach ($this->items as $item) {
            $item->product?->decrement('vendor_stock', $item->qty);
        }
    }

    /**
     * Increment product stock back by each item's qty.
     */
    public function restoreStock(): void
    {
        foreach ($this->items as $item) {
            $item->product?->increment('vendor_stock', $item->qty);
        }
    }

    protected function casts(): array
    {
        return [
            'status_history' => 'array',
        ];
    }

    /**
     * Append an entry to the order status history (skips consecutive duplicates).
     */
    public function recordStatusChange(string $status, ?string $note = null): void
    {
        $history = $this->status_history ?: [];
        $last = end($history);

        if ($last && ($last['status'] ?? null) === $status) {
            return;
        }

        $history[] = [
            'status' => $status,
            'label' => ucfirst($status),
            'note' => $note,
            'at' => now()->toDateTimeString(),
        ];

        $this->status_history = $history;
        $this->save();
    }

    /**
     * History entries with a synthetic "Order Placed" entry anchored at creation time.
     */
    public function getStatusHistory(): array
    {
        $history = $this->status_history ?: [];

        $placed = [
            'status' => 'placed',
            'label' => 'Order Placed',
            'note' => null,
            'at' => $this->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
        ];

        array_unshift($history, $placed);

        return $history;
    }
}
