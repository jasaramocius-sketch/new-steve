<?php

namespace Database\Seeders;

use App\Models\HomePageSection;
use Illuminate\Database\Seeder;

class AdditionalHomePageSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'section_name' => 'categories_heading',
                'title' => 'Categories',
                'subtitle' => null,
                'description' => 'Browse our wide selection of auto parts by category.',
                'order' => 6,
                'status' => true,
            ],
            [
                'section_name' => 'explore_products',
                'title' => 'Explore Our Products',
                'subtitle' => null,
                'description' => 'Discover our complete range of high-quality automotive parts and accessories.',
                'order' => 7,
                'status' => true,
            ],
            [
                'section_name' => 'deal_of_day',
                'title' => 'Deal of the Day',
                'subtitle' => 'CLICK SHOP NOW FOR ALL DEAL OF THE PRODUCT',
                'description' => 'Donec condimentum Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras cursus pretium sapien, in pulvinar ipsum molestie id. Aliquam erat volutpat. Duis quam tellus, ullamcorper.....',
                'button_text' => 'Shop Now',
                'button_url' => '/shop',
                'order' => 8,
                'status' => true,
            ],
            [
                'section_name' => 'featured_products_heading',
                'title' => 'Our Featured Products',
                'subtitle' => null,
                'description' => null,
                'order' => 9,
                'status' => true,
            ],
            [
                'section_name' => 'best_selling',
                'title' => 'Best Selling Products',
                'subtitle' => null,
                'description' => 'Discover our top-performing products that customers love most. Quality parts, verified performance, and exceptional ratings.',
                'order' => 10,
                'status' => true,
            ],
            [
                'section_name' => 'latest_post',
                'title' => 'Latest Post',
                'subtitle' => null,
                'description' => 'Stay updated with our latest maintenance guides, tips, and insights from professional automotive mechanics.',
                'order' => 11,
                'status' => true,
            ],
            [
                'section_name' => 'partners_heading',
                'title' => 'Our Partners',
                'subtitle' => null,
                'description' => 'We collaborate with world-class manufacturers to provide the highest-grade auto parts and accessories.',
                'order' => 12,
                'status' => true,
            ],
        ];

        foreach ($sections as $data) {
            HomePageSection::firstOrCreate(
                ['section_name' => $data['section_name']],
                $data
            );
        }

        $this->command->info('Additional home page sections seeded successfully!');
    }
}
