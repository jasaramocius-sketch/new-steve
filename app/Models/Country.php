<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name'];

    public function states()
    {
        return $this->hasMany(State::class)->orderBy('name');
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, State::class);
    }
}
