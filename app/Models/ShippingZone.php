<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'country', 'state', 'city', 'priority', 'cost', 'is_active'];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingZoneRate::class);
    }
}
