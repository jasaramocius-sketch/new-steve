<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZoneRate extends Model
{
    use SoftDeletes;

    protected $fillable = ['shipping_zone_id', 'shipping_carrier_id', 'fee'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }
}
