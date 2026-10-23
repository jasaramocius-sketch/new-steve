<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        if (isSourceS3ImageReference($logo)) {
            return $logo;
        }

        return Cache::remember('brand.aws_image.'.$this->id, now()->addHours(6), function () {
            $bucket = (string) config('filesystems.disks.source_images.bucket');
            if ($bucket === '') {
                return null;
            }

            $primaryImage = $this->products()
                ->where('main_image', 'like', 's3://'.$bucket.'/%')
                ->orderBy('id')
                ->value('main_image');

            if ($primaryImage) {
                return $primaryImage;
            }

            $gallery = $this->products()
                ->where('images_json', 'like', '%s3://'.$bucket.'/%')
                ->orderBy('id')
                ->value('images_json');
            foreach ((array) (is_string($gallery) ? json_decode($gallery, true) : $gallery) as $entry) {
                $candidate = is_array($entry) ? ($entry['file'] ?? null) : $entry;
                if (is_string($candidate) && isSourceS3ImageReference($candidate)) {
                    return $candidate;
                }
            }

            return null;
        });
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
