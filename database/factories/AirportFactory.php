<?php

namespace Database\Factories;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Airport>
 */
class AirportFactory extends Factory
{
    protected $model = Airport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->city() . ' International Airport',
            'city' => $this->faker->city(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'timezone' => $this->faker->timezone(),
        ];
    }
}