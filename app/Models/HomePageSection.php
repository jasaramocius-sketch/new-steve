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
        'extra_data' => 'json',
        'status' => 'boolean',
    ];

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
