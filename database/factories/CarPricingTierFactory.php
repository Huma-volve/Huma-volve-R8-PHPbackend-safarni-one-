<?php

namespace Database\Factories;

use App\Models\CarPricingTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarPricingTierFactory extends Factory
{
    protected $model = CarPricingTier::class;

    public function definition(): array
    {
        return [
            // This will be handled in the seeder to ensure logical hour ranges
            'car_id' => \App\Models\Car::factory(),
            'from_hours' => 1,
            'to_hours' => 24,
            'price_per_hour' => 2000,
        ];
    }
}
