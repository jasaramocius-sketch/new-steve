<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class CommissionHistory extends Model
{
    protected $fillable = [
        'scope',
        'product_id',
        'old_commission',
        'new_commission',
        'changed_by_type',
        'changed_by_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_commission' => 'decimal:2',
            'new_commission' => 'decimal:2',
        ];
    }

    public static function recordChange(
        string $scope,
        ?int $productId,
        ?string $oldCommission,
        ?string $newCommission
    ): self {
        $actor = Auth::user()
            ?? Auth::guard('admin')->user()
            ?? Auth::guard('staff')->user()
            ?? Auth::guard('web')->user();

        return static::create([
            'scope' => $scope,
            'product_id' => $productId,
            'old_commission' => $oldCommission,
            'new_commission' => $newCommission,
            'changed_by_type' => $actor ? get_class($actor) : null,
            'changed_by_id' => $actor?->getAuthIdentifier(),
            'ip_address' => request()->ip(),
        ]);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy(): MorphTo
    {
        return $this->morphTo();
    }
}