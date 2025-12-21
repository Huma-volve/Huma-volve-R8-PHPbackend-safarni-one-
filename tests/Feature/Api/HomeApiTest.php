<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Home Page API.
 */
class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    private const HOME_ENDPOINT = '/api/home';

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Seed categories for all tests
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    /**
     * Test home endpoint returns correct JSON structure.
     */
    public function test_home_endpoint_returns_correct_structure(): void
    {
        Tour::factory()->count(3)->featured()->create();
        Tour::factory()->count(5)->notFeatured()->create();

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'categories',
                    'recommendations',
                    'tours',
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test categories are returned with correct keys.
     */
    public function test_categories_are_listed_with_correct_keys(): void
    {
        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'categories' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'icon_url',
                        ],
                    ],
                ],
            ]);

        $categories = $response->json('data.categories');
        $this->assertNotEmpty($categories);

        // Verify "tours" category exists
        $toursCategory = collect($categories)->firstWhere('slug', 'tours');
        $this->assertNotNull($toursCategory);
        $this->assertEquals('Tours', $toursCategory['name']);
    }

    /**
     * Test recommendations contain only featured tours.
     */
    public function test_recommendations_contain_only_featured_tours(): void
    {
        Tour::factory()->count(5)->featured()->create();
        Tour::factory()->count(5)->notFeatured()->create();

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $recommendations = $response->json('data.recommendations');
        $this->assertCount(5, $recommendations);

        foreach ($recommendations as $tour) {
            $this->assertTrue($tour['is_featured']);
        }
    }

    /**
     * Test expired tours are not included in response.
     */
    public function test_expired_tours_are_not_included(): void
    {
        // Create active tours
        Tour::factory()->count(3)->featured()->startsInFuture()->create();
        Tour::factory()->count(5)->notFeatured()->startsInFuture()->create();

        // Create expired tour
        $expiredTour = Tour::factory()->expired()->create([
            'title' => 'Expired Tour Should Not Appear',
        ]);

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $allTours = array_merge(
            $response->json('data.recommendations'),
            $response->json('data.tours')
        );

        $tourIds = collect($allTours)->pluck('id')->toArray();
        $this->assertNotContains($expiredTour->id, $tourIds);
    }

    /**
     * Test inactive tours are not included in response.
     */
    public function test_inactive_tours_are_not_included(): void
    {
        Tour::factory()->count(3)->featured()->create();

        $inactiveTour = Tour::factory()->inactive()->create([
            'title' => 'Inactive Tour Should Not Appear',
        ]);

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $allTours = array_merge(
            $response->json('data.recommendations'),
            $response->json('data.tours')
        );

        $tourIds = collect($allTours)->pluck('id')->toArray();
        $this->assertNotContains($inactiveTour->id, $tourIds);
    }

    /**
     * Test tour resource contains correct price structure.
     */
    public function test_tour_has_correct_price_structure(): void
    {
        Tour::factory()->featured()->create([
            'price' => 150000,
        ]);

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'recommendations' => [
                        '*' => [
                            'id',
                            'title',
                            'slug',
                            'location',
                            'price' => [
                                'amount',
                                'formatted',
                                'currency',
                            ],
                            'start_date',
                            'end_date',
                            'rating',
                            'thumbnail',
                            'is_featured',
                        ],
                    ],
                ],
            ]);

        $tour = $response->json('data.recommendations.0');
        $this->assertEquals(150000, $tour['price']['amount']);
        $this->assertEquals('1,500.00 EGP', $tour['price']['formatted']);
        $this->assertEquals('EGP', $tour['price']['currency']);
    }

    /**
     * Test recommendations limit is respected.
     */
    public function test_recommendations_limit_is_five(): void
    {
        Tour::factory()->count(10)->featured()->create();

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $recommendations = $response->json('data.recommendations');
        $this->assertCount(5, $recommendations);
    }

    /**
     * Test tours limit is respected.
     */
    public function test_tours_limit_is_ten(): void
    {
        Tour::factory()->count(20)->notFeatured()->create();

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $tours = $response->json('data.tours');
        $this->assertCount(10, $tours);
    }

    /**
     * Test home endpoint works with no tours.
     */
    public function test_home_endpoint_works_with_no_tours(): void
    {
        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'recommendations' => [],
                    'tours' => [],
                ],
            ]);

        $categories = $response->json('data.categories');
        $this->assertNotEmpty($categories);
    }

    /**
     * Test all four categories are returned.
     */
    public function test_all_four_categories_are_returned(): void
    {
        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $slugs = collect($categories)->pluck('slug')->toArray();

        $this->assertContains('tours', $slugs);
        $this->assertContains('flights', $slugs);
        $this->assertContains('hotels', $slugs);
        $this->assertContains('cars', $slugs);
    }

    /**
     * Test tours are ordered by creation date (latest first).
     */
    public function test_tours_are_ordered_by_latest(): void
    {
        $oldTour = Tour::factory()->notFeatured()->create([
            'created_at' => now()->subDays(5),
        ]);

        $newTour = Tour::factory()->notFeatured()->create([
            'created_at' => now(),
        ]);

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $tours = $response->json('data.tours');
        $this->assertEquals($newTour->id, $tours[0]['id']);
    }

    /**
     * Test recommendations are ordered by rating (highest first).
     */
    public function test_recommendations_are_ordered_by_rating(): void
    {
        $lowRatedTour = Tour::factory()->featured()->create([
            'rating' => 3.5,
        ]);

        $highRatedTour = Tour::factory()->featured()->create([
            'rating' => 5.0,
        ]);

        $response = $this->getJson(self::HOME_ENDPOINT);

        $response->assertStatus(200);

        $recommendations = $response->json('data.recommendations');
        $this->assertEquals($highRatedTour->id, $recommendations[0]['id']);
    }
}