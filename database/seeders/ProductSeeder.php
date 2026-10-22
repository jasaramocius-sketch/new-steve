<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $products = [
            ['name' => 'Front Shock Absorber Pair', 'slug' => 'front-shock-absorber-pair', 'description' => 'High-performance front shock absorbers for enhanced stability and road handling.', 'price' => 85.50, 'old_price' => 120.00, 'image' => '1730865630xEquqqNt.jpg', 'badge' => 'Best Sale', 'rating' => 4.8, 'reviews' => 120, 'featured' => true, 'cat_slug' => 'Shock-Absorbers'],
            ['name' => 'Coil Springs Suspension Kit', 'slug' => 'coil-springs-suspension-kit', 'description' => 'Heavy duty coil springs suspension kit for rear axle load leveling.', 'price' => 145.00, 'old_price' => 180.00, 'image' => '173086542025g2VBYv.jpg', 'badge' => 'Hot', 'rating' => 4.6, 'reviews' => 54, 'featured' => true, 'cat_slug' => 'Coil-Springs'],
            ['name' => 'Power Steering Rack & Pinion', 'slug' => 'power-steering-rack-pinion', 'description' => 'Premium replacement power steering rack and pinion gear assembly.', 'price' => 295.00, 'old_price' => null, 'image' => '1730865535QZpTcXXv.jpg', 'badge' => 'New', 'rating' => 4.7, 'reviews' => 38, 'featured' => true, 'cat_slug' => 'Steering-Racks'],
            ['name' => 'Premium Front Brake Pads Set', 'slug' => 'premium-front-brake-pads-set', 'description' => 'Ceramic front brake pads with superior stopping power and low dust.', 'price' => 45.00, 'old_price' => 65.00, 'image' => '1730865270Fc0QRDl8.jpg', 'badge' => 'Best Seller', 'rating' => 4.5, 'reviews' => 89, 'featured' => true, 'cat_slug' => 'Front-Brake-Pads'],
            ['name' => 'Rear Brake Pads Replacement', 'slug' => 'rear-brake-pads-replacement', 'description' => 'Quiet rear semi-metallic brake pads to restore original performance.', 'price' => 35.00, 'old_price' => null, 'image' => '1730865270Fc0QRDl8.jpg', 'badge' => null, 'rating' => 4.2, 'reviews' => 27, 'featured' => true, 'cat_slug' => 'Rear-Brake-Pads'],
            ['name' => 'Cross-Drilled Front Brake Rotors', 'slug' => 'cross-drilled-front-brake-rotors', 'description' => 'Performance cross-drilled and slotted brake rotors for cooling.', 'price' => 125.00, 'old_price' => 160.00, 'image' => '1730865303Q1nWwYLE.jpg', 'badge' => 'Sale', 'rating' => 4.9, 'reviews' => 112, 'featured' => true, 'cat_slug' => 'Brake-Rotors'],
            ['name' => 'High-Flow Cold Air Intake System', 'slug' => 'high-flow-cold-air-intake-system', 'description' => 'Cold air intake system with reusable filter to unlock horsepower.', 'price' => 189.99, 'old_price' => 240.00, 'image' => '173086523535Ifn9IA.jpg', 'badge' => 'Hot', 'rating' => 4.7, 'reviews' => 210, 'featured' => true, 'cat_slug' => 'Air-Filters'],
            ['name' => 'Intake Manifold Runner Control', 'slug' => 'intake-manifold-runner-control', 'description' => 'Replacement intake manifold for optimal air flow and fuel efficiency.', 'price' => 115.00, 'old_price' => null, 'image' => '1730865490RFxdWzUS.jpg', 'badge' => null, 'rating' => 4.4, 'reviews' => 43, 'featured' => true, 'cat_slug' => 'Intake-Manifolds'],
            ['name' => 'Electronic Throttle Body Assembly', 'slug' => 'electronic-throttle-body-assembly', 'description' => 'Calibrated electronic throttle body with position sensor.', 'price' => 175.00, 'old_price' => 210.00, 'image' => '1730865363ZJZiG4PY.jpg', 'badge' => 'Sale', 'rating' => 4.6, 'reviews' => 67, 'featured' => true, 'cat_slug' => 'Throttle-Bodies'],
            ['name' => 'Multi-Port Fuel Injectors Set', 'slug' => 'multi-port-fuel-injectors-set', 'description' => 'Matching set of 4 fuel injectors for complete combustion.', 'price' => 120.00, 'old_price' => 150.00, 'image' => '1730865580GPHzRyFS.jpg', 'badge' => 'New', 'rating' => 4.8, 'reviews' => 52, 'featured' => true, 'cat_slug' => 'Fuel-Injectors'],
            ['name' => 'Dual Core Aluminum Radiator', 'slug' => 'dual-core-aluminum-radiator', 'description' => 'Lightweight dual row aluminum radiator for maximum heat dissipation.', 'price' => 155.00, 'old_price' => 195.00, 'image' => '1730865303Q1nWwYLE.jpg', 'badge' => 'Best Sale', 'rating' => 4.9, 'reviews' => 143, 'featured' => true, 'cat_slug' => 'Radiators'],
            ['name' => 'Stage 2 Organic Clutch Disc', 'slug' => 'stage-2-organic-clutch-disc', 'description' => 'Heavy duty street organic clutch disc for high torque capacity.', 'price' => 210.00, 'old_price' => 280.00, 'image' => '1730865580GPHzRyFS.jpg', 'badge' => 'Hot', 'rating' => 4.7, 'reviews' => 31, 'featured' => true, 'cat_slug' => 'Clutch-Discs'],
        ];

        foreach ($products as $p) {
            $catSlug = $p['cat_slug'];
            unset($p['cat_slug']);

            $cat = Category::where('slug', $catSlug)->first();
            $p['category_id'] = $cat ? $cat->id : null;

            Product::create($p);
        }
    }
}
