<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use Revisable, SoftDeletes;

    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        $setting = static::withTrashed()->firstOrNew(['key' => $key]);
        $setting->value = $value;

        if ($setting->exists && $setting->trashed()) {
            $setting->restore();
        } else {
            $setting->save();
        }

        return $setting;
    }

    public static function getAllAsArray()
    {
        return static::pluck('value', 'key')->toArray();
    }
}
