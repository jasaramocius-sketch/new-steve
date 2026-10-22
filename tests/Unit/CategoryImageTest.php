<?php

namespace Tests\Unit;

use App\Models\Category;
use Tests\TestCase;

class CategoryImageTest extends TestCase
{
    public function test_category_without_image_uses_slug_fallback(): void
    {
        $category = new Category([
            'name' => 'Engine Parts',
            'slug' => 'engine-parts',
            'image' => null,
        ]);

        $this->assertSame('1730804224silver-device-with-white-cover-that-says-word-it-minpng.png', $category->getDisplayImage());
    }
}
