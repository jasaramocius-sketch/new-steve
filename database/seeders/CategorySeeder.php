<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('order_items')->delete();
        DB::table('wishlists')->delete();
        DB::table('compares')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tree = [
            'Engine Parts' => [
                'slug' => 'Engine-Parts',
                'image' => '1730804224silver-device-with-white-cover-that-says-word-it-minpng.png',
                'children' => [
                    'Air Intake Systems' => [
                        'slug' => 'Air-Intake-Systems',
                        'children' => [
                            ['name' => 'Air Filters', 'slug' => 'Air-Filters'],
                            ['name' => 'Intake Manifolds', 'slug' => 'Intake-Manifolds'],
                            ['name' => 'Throttle Bodies', 'slug' => 'Throttle-Bodies'],
                        ],
                    ],
                    'Fuel Systems' => [
                        'slug' => 'Fuel-Systems',
                        'children' => [
                            ['name' => 'Fuel Injectors', 'slug' => 'Fuel-Injectors'],
                            ['name' => 'Fuel Pumps', 'slug' => 'Fuel-Pumps'],
                            ['name' => 'Carburetors', 'slug' => 'Carburetors'],
                        ],
                    ],
                    'Cooling Systems' => [
                        'slug' => 'Cooling-Systems',
                        'children' => [
                            ['name' => 'Radiators', 'slug' => 'Radiators'],
                            ['name' => 'Water Pumps', 'slug' => 'Water-Pumps'],
                            ['name' => 'Thermostats', 'slug' => 'Thermostats'],
                        ],
                    ],
                    'Exhaust Systems' => ['slug' => 'Exhaust-Systems', 'children' => []],
                ],
            ],
            'Body & Exterior' => [
                'slug' => 'Body-Exterior',
                'image' => '173080421331792401shockabsorber2-minpng.png',
                'children' => [
                    'Body Parts' => ['slug' => 'Body-Parts', 'children' => []],
                    'Mirrors & Glass' => ['slug' => 'Mirrors-Glass', 'children' => []],
                    'Accessories' => ['slug' => 'Accessories', 'children' => []],
                ],
            ],
            'Interior Parts' => [
                'slug' => 'Interior-Parts',
                'image' => '1730804204oval-shaped-car-mirror-minpng.png',
                'children' => [
                    'Tires' => ['slug' => 'Tires', 'children' => []],
                    'Wheels & Rims' => ['slug' => 'Wheels-Rims', 'children' => []],
                    'Tire Accessories' => ['slug' => 'Tire-Accessories', 'children' => []],
                ],
            ],
            'Electrical & Lighting' => [
                'slug' => 'Electrical-Lighting',
                'image' => '1730804142c1b1489eb0545231cf0bfa44a827e2ae-minpng.png',
                'children' => [
                    'Lighting & Lamps' => ['slug' => 'Lighting-Lamps', 'children' => []],
                    'Ignition System' => ['slug' => 'Ignition-System', 'children' => []],
                    'Batteries & Cables' => ['slug' => 'Batteries-Cables', 'children' => []],
                ],
            ],
            'Brakes & Brake Parts' => [
                'slug' => 'Brakes-Brake-Parts',
                'image' => '173080413017369231014177a5f-cedb-407a-8a1d-a6aa0f40249d-minpng.png',
                'children' => [
                    'Brake Pads & Shoes' => [
                        'slug' => 'Brake-Pads-Shoes',
                        'children' => [
                            ['name' => 'Front Brake Pads', 'slug' => 'Front-Brake-Pads'],
                            ['name' => 'Rear Brake Pads', 'slug' => 'Rear-Brake-Pads'],
                        ],
                    ],
                    'Rotors & Drums' => [
                        'slug' => 'Rotors-Drums',
                        'children' => [
                            ['name' => 'Brake Rotors', 'slug' => 'Brake-Rotors'],
                            ['name' => 'Brake Drums', 'slug' => 'Brake-Drums'],
                        ],
                    ],
                    'Brake Lines & Hoses' => ['slug' => 'Brake-Lines-Hoses', 'children' => []],
                ],
            ],
            'Transmission & Drivetrain' => [
                'slug' => 'Transmission-Drivetrain',
                'image' => '1730804110engineering-concept-project-heating-house-thermostatic-valve-copper-fitting-project-minpng.png',
                'children' => [
                    'Clutch Parts' => [
                        'slug' => 'Clutch-Parts',
                        'children' => [
                            ['name' => 'Clutch Discs', 'slug' => 'Clutch-Discs'],
                            ['name' => 'Pressure Plates', 'slug' => 'Pressure-Plates'],
                            ['name' => 'Flywheels', 'slug' => 'Flywheels'],
                        ],
                    ],
                    'Differentials' => [
                        'slug' => 'Differentials',
                        'children' => [
                            ['name' => 'Differential Covers', 'slug' => 'Differential-Covers'],
                            ['name' => 'Gears', 'slug' => 'Gears'],
                        ],
                    ],
                    'Transmission Parts' => [
                        'slug' => 'Transmission-Parts',
                        'children' => [
                            ['name' => 'Transmission Filters', 'slug' => 'Transmission-Filters'],
                            ['name' => 'Seals', 'slug' => 'Seals'],
                        ],
                    ],
                    'Suspension Kits' => ['slug' => 'Suspension-Kits', 'children' => []],
                ],
            ],
            'Suspension & Steering' => [
                'slug' => 'Suspension-Steering',
                'image' => '1730804093huelpful-steering-wheel-isolated-white-background-minpng.png',
                'children' => [
                    'Steering Components' => [
                        'slug' => 'Steering-Components',
                        'children' => [
                            ['name' => 'Steering Racks', 'slug' => 'Steering-Racks'],
                            ['name' => 'Tie Rod Ends', 'slug' => 'Tie-Rod-Ends'],
                            ['name' => 'Steering Columns', 'slug' => 'Steering-Columns'],
                        ],
                    ],
                    'Shocks & Struts' => [
                        'slug' => 'Shocks-Struts',
                        'children' => [
                            ['name' => 'Shock Absorbers', 'slug' => 'Shock-Absorbers'],
                            ['name' => 'Coil Springs', 'slug' => 'Coil-Springs'],
                            ['name' => 'Mounts', 'slug' => 'Mounts'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tree as $name => $data) {
            $parent = Category::create(['name' => $name, 'slug' => $data['slug']]);

            foreach ($data['children'] as $subName => $subData) {
                $sub = Category::create([
                    'name' => $subName,
                    'slug' => $subData['slug'],
                    'parent_id' => $parent->id,
                ]);

                foreach ($subData['children'] as $child) {
                    Category::create([
                        'name' => $child['name'],
                        'slug' => $child['slug'],
                        'parent_id' => $sub->id,
                    ]);
                }
            }
        }
    }
}
