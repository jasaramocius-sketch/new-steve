<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\HomePageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTopBrandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_only_six_brands_when_configured(): void
    {
        for ($i = 0; $i < 8; $i++) {
            Brand::create([
                'name' => 'Brand '.($i + 1),
                'slug' => 'brand-'.($i + 1),
                'status' => true,
            ]);
        }

        HomePageSection::create([
            'section_name' => 'top_brands_heading',
            'title' => 'Top Brands',
            'status' => true,
            'extra_data' => ['brands_limit' => '6'],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('brands', function ($brands) {
            return $brands->count() === 6;
        });
    }

    public function test_home_page_creates_top_brands_section_when_missing(): void
    {
        $this->assertDatabaseMissing('home_page_sections', ['section_name' => 'top_brands_heading']);

        $response = $this->get('/');

        $response->assertOk();
        $this->assertDatabaseHas('home_page_sections', ['section_name' => 'top_brands_heading']);
    }

    public function test_home_page_shows_admin_selected_brands_when_configured(): void
    {
        $brandOne = Brand::create([
            'name' => 'Alpha Brand',
            'slug' => 'alpha-brand',
            'status' => true,
        ]);
        $brandTwo = Brand::create([
            'name' => 'Beta Brand',
            'slug' => 'beta-brand',
            'status' => true,
        ]);
        Brand::create([
            'name' => 'Gamma Brand',
            'slug' => 'gamma-brand',
            'status' => true,
        ]);

        HomePageSection::create([
            'section_name' => 'top_brands_heading',
            'title' => 'Top Brands',
            'status' => true,
            'extra_data' => ['brand_ids' => [$brandOne->id, $brandTwo->id]],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('brands', function ($brands) use ($brandOne, $brandTwo) {
            return $brands->pluck('id')->all() === [$brandOne->id, $brandTwo->id];
        });
    }
}
