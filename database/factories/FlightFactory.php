<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flight>
 */
class FlightFactory extends Factory
{
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureTime = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $durationMinutes = $this->faker->numberBetween(60, 600);
        $arrivalTime = (clone $departureTime)->modify("+{$durationMinutes} minutes");

        return [
            'id' => Str::uuid(),
            'flight_number' => strtoupper($this->faker->lexify('??')) . $this->faker->numberBetween(100, 999),
            'airline_id' => Airline::factory(),
            'aircraft_id' => Aircraft::factory(),
            'origin_airport_id' => Airport::factory(),
            'destination_airport_id' => Airport::factory(),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'duration_minutes' => $durationMinutes,
            'stops' => $this->faker->randomElement([0, 0, 0, 1, 1, 2]),
            'layover_details' => null,
            'baggage_rules' => '1x23kg checked, 1x7kg cabin',
            'is_refundable' => $this->faker->boolean(70),
            'fare_conditions' => $this->faker->sentence(),
            'base_price_egp' => $this->faker->numberBetween(200000, 2000000),
            'tax_percentage' => 14.00,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the flight is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the flight is direct (no stops).
     */
    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'stops' => 0,
            'layover_details' => null,
        ]);
    }
}