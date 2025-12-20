<?php

namespace Database\Factories;

use App\Models\Aircraft;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aircraft>
 */
class AircraftFactory extends Factory
{
    protected $model = Aircraft::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['Boeing 737-800', 'Airbus A320', 'Boeing 777-300ER', 'Airbus A380'];

        return [
            'type' => $this->faker->randomElement($types),
            'total_seats' => $this->faker->numberBetween(150, 400),
            'seat_map_config' => [
                'economy' => [
                    'rows' => range(1, 25),
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                    'price_modifier' => 0,
                ],
            ],
        ];
    }
}