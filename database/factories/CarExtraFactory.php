<?php

namespace Database\Factories;

use App\Models\CarExtra;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarExtraFactory extends Factory
{
    protected $model = CarExtra::class;

    public function definition(): array
    {
        $extras = [
            ['name' => 'GPS Navigation', 'pricing_type' => 'per_rental', 'price' => 5000],
            ['name' => 'Child Seat', 'pricing_type' => 'per_day', 'price' => 1500],
            ['name' => 'Comprehensive Insurance', 'pricing_type' => 'per_day', 'price' => 3000],
            ['name' => 'Additional Driver', 'pricing_type' => 'per_rental', 'price' => 2000],
            ['name' => 'Full Fuel Tank', 'pricing_type' => 'per_rental', 'price' => 8000],
        ];

        $extra = $this->faker->unique()->randomElement($extras);

        return [
            'name' => $extra['name'],
            'pricing_type' => $extra['pricing_type'],
            'price' => $extra['price'],
            'is_available' => true,
        ];
    }
}
