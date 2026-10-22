<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'set_default',
        'is_deleted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
