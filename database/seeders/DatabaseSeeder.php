<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Car;
use App\Models\CarExtra;
use App\Models\CarImage;
use App\Models\CarPricingTier;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admins
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_admin' => true,
        ]);

        // 2. Create Regular Users
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_admin' => false,
        ]);

        User::factory(5)->create();

        // 3. Create Car Extras
        $extras = [
            ['name' => 'GPS Navigation', 'pricing_type' => 'per_rental', 'price' => 5000],
            ['name' => 'Child Seat', 'pricing_type' => 'per_day', 'price' => 1500],
            ['name' => 'Comprehensive Insurance', 'pricing_type' => 'per_day', 'price' => 3000],
            ['name' => 'Additional Driver', 'pricing_type' => 'per_rental', 'price' => 2000],
            ['name' => 'Full Fuel Tank', 'pricing_type' => 'per_rental', 'price' => 8000],
        ];

        foreach ($extras as $extra) {
            CarExtra::create($extra);
        }

        // 4. Create Cars with Images, Tiers, and Reviews
        Car::factory(15)->create()->each(function ($car) {
            // Add Gallery Images
            CarImage::factory(3)->create([
                'car_id' => $car->id,
                'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=800',
                'image_type' => 'gallery'
            ]);

            // Add Pricing Tiers
            CarPricingTier::create([
                'car_id' => $car->id,
                'from_hours' => 1,
                'to_hours' => 30,
                'price_per_hour' => $car->price_per_hour,
            ]);

            CarPricingTier::create([
                'car_id' => $car->id,
                'from_hours' => 31,
                'to_hours' => 40,
                'price_per_hour' => $car->price_per_hour * 0.8, // 20% discount
            ]);

            CarPricingTier::create([
                'car_id' => $car->id,
                'from_hours' => 41,
                'to_hours' => null,
                'price_per_hour' => $car->price_per_hour * 0.6, // 40% discount
            ]);

            // Add Reviews
            Review::factory(rand(2, 5))->create([
                'category' => 'car',
                'item_id' => $car->id,
                'status' => 'approved'
            ]);
        });
    }
}
