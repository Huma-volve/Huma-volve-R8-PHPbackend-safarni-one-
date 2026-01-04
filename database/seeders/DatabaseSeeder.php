<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('Password1!'),
                'phone' => '+201234567890',
                'location' => 'Cairo, Egypt',
                'is_verified' => true,
                'is_admin' => true,
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Test User
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Test User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('Password1!'),
                'phone' => '+201098765432',
                'location' => 'Alexandria, Egypt',
                'is_verified' => true,
                'is_admin' => false,
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Run other seeders
        $this->call([
            CategorySeeder::class,
            AirportSeeder::class,
            AirlineSeeder::class,
            AircraftSeeder::class,
            FlightSeeder::class,
            TourSeeder::class,
        ]);
    }
}
