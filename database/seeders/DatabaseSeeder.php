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
            ['email' => 'admin@safarni.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@safarni.com',
                'password' => Hash::make('password'),
                'phone' => '+201234567890',
                'location' => 'Cairo, Egypt',
                'is_verified' => true,
                'is_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Test User
        User::updateOrCreate(
            ['email' => 'user@safarni.com'],
            [
                'name' => 'Test User',
                'email' => 'user@safarni.com',
                'password' => Hash::make('password'),
                'phone' => '+201098765432',
                'location' => 'Alexandria, Egypt',
                'is_verified' => true,
                'is_admin' => false,
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
        ]);
    }
}