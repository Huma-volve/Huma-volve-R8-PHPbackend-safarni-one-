<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => 'car',
            'item_id' => 1, // Will be overridden
            'title' => $this->faker->sentence(),
            'comment' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(3, 5),
            'status' => 'approved',
        ];
    }
}
