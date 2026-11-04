<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ApiIntegration extends Model
{
    protected $fillable = [
        'type',
        'provider',
        'name',
        'base_url',
        'auth_type',
        'credentials',
        'endpoints',
        'config',
        'description',
        'is_active',
        'is_default',
        'priority',
        'last_tested_at',
        'last_test_result',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'last_tested_at' => 'datetime',
    ];

    public function setCredentialsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['credentials'] = Crypt::encryptString(json_encode($value));
        } elseif (is_string($value) && $value !== '') {
            // if already encrypted or plain json string
            $this->attributes['credentials'] = $value;
        } else {
            $this->attributes['credentials'] = null;
        }
    }

    public function getCredentialsAttribute($value)
    {
        if (!$value) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($value);
            $decoded = json_decode($decrypted, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
    }

    public function setEndpointsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['endpoints'] = json_encode($value);
        } elseif (is_string($value) && $value !== '') {
            $this->attributes['endpoints'] = $value;
        } else {
            $this->attributes['endpoints'] = null;
        }
    }

    public function getEndpointsAttribute($value)
    {
        if (!$value) {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setConfigAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['config'] = json_encode($value);
        } elseif (is_string($value) && $value !== '') {
            $this->attributes['config'] = $value;
        } else {
            $this->attributes['config'] = null;
        }
    }

    public function getConfigAttribute($value)
    {
        if (!$value) {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function getDefaultCourier(): ?self
    {
        return static::where('type', 'courier')
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('priority', 'desc')
            ->first();
    }

    public static function getActiveCourier(): ?self
    {
        $default = static::getDefaultCourier();
        if ($default) {
            return $default;
        }

        return static::where('type', 'courier')
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'asc')
            ->first();
    }
}
