<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Database\Seeder;

/**
 * Seeder for Tour test data.
 */
class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $toursCategory = Category::where('key', 'tours')->first();

        if (!$toursCategory) {
            $this->command->warn('Tours category not found. Run CategorySeeder first.');
            return;
        }

        // Create 5 featured tours
        Tour::factory()
            ->count(5)
            ->featured()
            ->create(['category_id' => $toursCategory->id]);

        // Create 15 regular (non-featured) tours
        Tour::factory()
            ->count(15)
            ->notFeatured()
            ->create(['category_id' => $toursCategory->id]);

        $this->command->info('Created 20 tours (5 featured, 15 regular).');
    }
}