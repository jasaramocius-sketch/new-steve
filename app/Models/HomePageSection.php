<?php

namespace App\Models;

use App\Traits\Revisable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageSection extends Model
{
    use HasFactory, Revisable;

    protected $fillable = [
        'section_name',
        'title',
        'subtitle',
        'description',
        'image',
        'button_text',
        'button_url',
        'order',
        'status',
        'extra_data',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getExtraDataAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function setExtraDataAttribute($value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        $this->attributes['extra_data'] = json_encode(is_array($value) ? $value : []);
    }

    public function imagePath(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return storedImageUrl($this->image, 'assets/images/home');
    }

    public function bgStyle(): string
    {
        $path = $this->imagePath();
        if (! $path) {
            return '';
        }

        return "style=\"background-image: url('{$path}'); background-size: cover; background-position: center;\"";
    }

    public function dealImagePath(): ?string
    {
        $extra = $this->extra_data ?? [];
        $dealImage = $extra['deal_image'] ?? null;
        if (! $dealImage) {
            return null;
        }

        return storedImageUrl($dealImage, 'assets/images/home');
    }

    public function dealBgStyle(): string
    {
        $path = $this->dealImagePath();
        if (! $path) {
            return '';
        }

        return "style=\"background-image: url('{$path}'); background-size: cover; background-position: center;\"";
    }
}
