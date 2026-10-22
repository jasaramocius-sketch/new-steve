<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Master Admin
        User::firstOrCreate(
            ['email' => 'admin@stautoparts.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('admin123'),
                'role' => 'master_admin',
                'phone' => '+1 (000) 000-0000',
                'address' => '1 Admin Plaza',
                'city' => 'New York',
                'country' => 'United States',
            ]
        );

        // Create a Staff account
        User::firstOrCreate(
            ['email' => 'staff@stautoparts.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone' => '+1 (111) 111-1111',
                'address' => '10 Staff Lane',
                'city' => 'Chicago',
                'country' => 'United States',
            ]
        );

        // Create a sample Customer
        User::firstOrCreate(
            ['email' => 'customer@stautoparts.com'],
            [
                'name' => 'Sample Customer',
                'password' => Hash::make('customer123'),
                'role' => 'customer',
                'phone' => '+1 (222) 222-2222',
                'address' => '42 Customer Road',
                'city' => 'Los Angeles',
                'country' => 'United States',
            ]
        );

        $this->command->info('✅ Users seeded: admin@stautoparts.com / admin123');
    }
}
