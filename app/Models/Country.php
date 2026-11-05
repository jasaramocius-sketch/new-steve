<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function states()
    {
        return $this->hasMany(State::class)->orderBy('name');
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, State::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
