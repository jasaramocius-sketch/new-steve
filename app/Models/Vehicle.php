<?php

namespace App\Models;

use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'user_id',
        'year',
        'make',
        'model',
        'engine',
        'is_deleted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
