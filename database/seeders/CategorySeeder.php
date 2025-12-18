<?php

namespace Database\Seeders;

use App\Models\Category; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'key' => 'tours',
                'title' => 'Tours',
                'description' => 'Explore the world with guided tours.',
                'image' => 'https://placehold.co/600x400?text=Tours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'flights',
                'title' => 'Flights',
                'description' => 'Book flights to anywhere.',
                'image' => 'https://placehold.co/600x400?text=Flights',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hotels',
                'title' => 'Hotels',
                'description' => 'Stay in the best hotels.',
                'image' => 'https://placehold.co/600x400?text=Hotels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cars',
                'title' => 'Car Rentals',
                'description' => 'Rent a car for your journey.',
                'image' => 'https://placehold.co/600x400?text=Cars',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insertOrIgnore($categories);
    }
}