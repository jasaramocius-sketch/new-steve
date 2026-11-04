<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use Revisable;

    protected $table = 'brands';

    protected $fillable = [
        'aaia_id',
        'name',
        'line_code',
        'slug',
        'logo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageAttribute()
    {
        $logo = trim((string) ($this->attributes['logo'] ?? ''));

        if ($logo !== '') {
            if (str_starts_with($logo, 'storage/')) {
                return $logo;
            }

            if (str_starts_with($logo, 'uploads/')) {
                return $logo;
            }

            if (preg_match('#^https?://#i', $logo)) {
                return $logo;
            }

            if (str_starts_with($logo, 's3://')) {
                return $logo;
            }

            if (function_exists('isSourceS3ImageReference') && isSourceS3ImageReference($logo)) {
                return $logo;
            }

            return $logo;
        }

        return null;
    }

    public function getStatusAttribute()
    {
        return (bool) ($this->attributes['status'] ?? true);
    }

    public function getIsDeletedAttribute()
    {
        return (bool) ($this->attributes['is_deleted'] ?? false);
    }
}
