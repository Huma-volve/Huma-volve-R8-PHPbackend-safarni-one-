<?php

namespace Database\Factories;

use App\Models\CarImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarImageFactory extends Factory
{
    protected $model = CarImage::class;

    public function definition(): array
    {
        return [
            'car_id' => \App\Models\Car::factory(),
            'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=800',
            'image_type' => 'gallery',
            'display_order' => rand(1, 10),
            'is_active' => true,
        ];
    }
}
