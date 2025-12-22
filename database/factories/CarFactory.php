<?php

namespace Database\Factories;

use App\Models\Car;
use App\Enums\Availability;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        $brands = [
            'Toyota' => ['Camry', 'Corolla', 'Fortuner', 'Land Cruiser'],
            'BMW' => ['3 Series', '5 Series', 'X3', 'X5'],
            'Mercedes' => ['C-Class', 'E-Class', 'GLC', 'GLE'],
            'Honda' => ['Civic', 'Accord', 'CR-V'],
            'Hyundai' => ['Elantra', 'Tucson', 'Santa Fe'],
        ];

        $brand = $this->faker->randomElement(array_keys($brands));
        $model = $this->faker->randomElement($brands[$brand]);

        return [
            'brand' => $brand,
            'model' => $model,
            'year' => $this->faker->numberBetween(2020, 2024),
            'type' => $this->faker->randomElement(['sedan', 'suv', 'luxury', 'economy']),
            'seats' => $this->faker->randomElement([4, 5, 7]),
            'location' => $this->faker->randomElement(['Cairo', 'Alexandria', 'Giza', 'Sharm El Sheikh', 'Hurghada']),
            'price_per_hour' => $this->faker->numberBetween(1000, 5000), // 10 to 50 EGP
            'image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&q=80&w=800',
            'description' => $this->faker->paragraph(),
            'features' => ['AC', 'GPS', 'Bluetooth', 'Leather Seats', 'Sunroof'],
            'availability' => Availability::Available,
            'rating' => $this->faker->randomFloat(2, 3, 5),
        ];
    }
}
