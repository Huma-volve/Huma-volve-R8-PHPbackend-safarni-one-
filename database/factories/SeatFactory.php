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
     * Track used row/column combinations per flight to avoid duplicates.
     */
    protected static array $usedCombinations = [];

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
     * Configure the model factory to generate unique row/column per flight.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Seat $seat) {
            $flightId = (string) $seat->flight_id;

            if (!isset(static::$usedCombinations[$flightId])) {
                static::$usedCombinations[$flightId] = [];
            }

            $maxAttempts = 100;
            $attempts = 0;

            do {
                $row = $this->faker->numberBetween(1, 30);
                $column = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F']);
                $key = "{$row}-{$column}";
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    // Fallback to sequential generation
                    $existingCount = count(static::$usedCombinations[$flightId]);
                    $row = (int) floor($existingCount / 6) + 1;
                    $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
                    $column = $columns[$existingCount % 6];
                    $key = "{$row}-{$column}";
                    break;
                }
            } while (isset(static::$usedCombinations[$flightId][$key]));

            static::$usedCombinations[$flightId][$key] = true;

            $seat->row = $row;
            $seat->column = $column;
        });
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

    /**
     * Reset the used combinations tracker.
     */
    public static function resetUsedCombinations(): void
    {
        static::$usedCombinations = [];
    }
}