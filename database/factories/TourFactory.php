<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for generating Tour test data.
 *
 * @extends Factory<Tour>
 */
class TourFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Tour>
     */
    protected $model = Tour::class;

    /**
     * Realistic Egyptian tour titles.
     *
     * @var list<string>
     */
    private const TOUR_TITLES = [
        'Pyramids of Giza Sunrise Tour',
        'Luxor Hot Air Balloon Adventure',
        'Nile Cruise from Aswan to Luxor',
        'Red Sea Diving Experience',
        'Alexandria Day Trip',
        'Siwa Oasis Desert Safari',
        'Cairo Islamic Quarter Walking Tour',
        'Valley of the Kings Exploration',
        'Dahab Snorkeling Trip',
        'White Desert Camping Adventure',
        'Abu Simbel Temple Tour',
        'Hurghada Glass Boat Tour',
        'Karnak Temple Sound & Light Show',
        'Sharm El Sheikh Quad Biking',
        'Felucca Ride at Sunset',
        'Egyptian Museum Guided Tour',
        'Aswan High Dam & Philae Temple',
        'Desert Quad Bike Safari',
        'Marsa Alam Dolphin Watching',
        'Saint Catherine Mountain Climb',
    ];

    /**
     * Egyptian locations.
     *
     * @var list<string>
     */
    private const LOCATIONS = [
        'Cairo, Egypt',
        'Luxor, Egypt',
        'Aswan, Egypt',
        'Alexandria, Egypt',
        'Hurghada, Egypt',
        'Sharm El Sheikh, Egypt',
        'Dahab, Egypt',
        'Siwa Oasis, Egypt',
        'Marsa Alam, Egypt',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->randomElement(self::TOUR_TITLES);
        $startDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 7) . ' days');

        return [
            'category_id' => Category::where('key', 'tours')->first()?->id ?? 1,
            'title' => $title,
            'slug' => Str::slug($title),
            'location' => $this->faker->randomElement(self::LOCATIONS),
            'price' => $this->faker->numberBetween(50000, 500000),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_featured' => false,
            'is_active' => true,
            'rating' => $this->faker->randomFloat(1, 3.5, 5.0),
            'thumbnail_url' => 'https://placehold.co/600x400?text=' . urlencode($title),
        ];
    }

    /**
     * Indicate that the tour is featured.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the tour is not featured.
     */
    public function notFeatured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => false,
        ]);
    }

    /**
     * Indicate that the tour is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the tour has expired.
     */
    public function expired(): static
    {
        $startDate = $this->faker->dateTimeBetween('-2 months', '-1 day');
        $endDate = (clone $startDate)->modify('+3 days');

        return $this->state(fn(array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Set a specific start date in the future.
     */
    public function startsInFuture(int $days = 7): static
    {
        $startDate = now()->addDays($days);
        $endDate = (clone $startDate)->addDays(3);

        return $this->state(fn(array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}