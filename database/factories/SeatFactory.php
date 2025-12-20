<?php

namespace Database\Factories;

use App\Enums\SeatClass;
use App\Models\Flight;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seat>
 */
class SeatFactory extends Factory
{
    protected $model = Seat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'flight_id' => Flight::factory(),
            'class' => $this->faker->randomElement(SeatClass::values()),
            'row' => $this->faker->numberBetween(1, 30),
            'column' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F']),
            'is_available' => true,
            'price_modifier_egp' => $this->faker->randomElement([0, 0, 0, 5000, 10000]),
        ];
    }

    /**
     * Indicate that the seat is booked.
     */
    public function booked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    /**
     * Indicate that the seat is business class.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'class' => SeatClass::BUSINESS->value,
            'price_modifier_egp' => 50000,
        ]);
    }
}