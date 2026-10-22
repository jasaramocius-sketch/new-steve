<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function relatedTitle(): ?string
    {
        if (! $this->model_type || ! $this->model_id || ! class_exists($this->model_type)) {
            return null;
        }

        try {
            $record = $this->model_type::withoutGlobalScopes()->find($this->model_id);

            return $record ? ($record->title ?? $record->name ?? null) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
