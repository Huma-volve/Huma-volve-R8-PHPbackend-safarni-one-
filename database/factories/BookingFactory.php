<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => fn () => Category::firstOrCreate(
                ['key' => 'flights'],
                ['title' => 'Flights', 'description' => 'Book flights to anywhere.']
            )->key,
            'item_id' => $this->faker->randomNumber(5),
            'total_price' => $this->faker->numberBetween(200000, 3000000),
            'payment_status' => PaymentStatus::PENDING->value,
            'status' => BookingStatus::PENDING->value,
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CONFIRMED->value,
            'payment_status' => PaymentStatus::SUCCEEDED->value,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CANCELLED->value,
        ]);
    }
}