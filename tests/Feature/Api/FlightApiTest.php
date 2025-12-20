<?php

namespace Tests\Feature\Api;

use App\Models\Airport;
use App\Models\Airline;
use App\Models\Aircraft;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FlightApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected Airport $originAirport;
    protected Airport $destinationAirport;
    protected Airline $airline;
    protected Aircraft $aircraft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);

        $this->originAirport = Airport::factory()->create(['code' => 'CAI']);
        $this->destinationAirport = Airport::factory()->create(['code' => 'DXB']);
        $this->airline = Airline::factory()->create(['code' => 'MS']);
        $this->aircraft = Aircraft::factory()->create();
    }

    /**
     * Create a test flight.
     */
    protected function createFlight(array $attributes = []): Flight
    {
        return Flight::factory()->create(array_merge([
            'airline_id' => $this->airline->id,
            'aircraft_id' => $this->aircraft->id,
            'origin_airport_id' => $this->originAirport->id,
            'destination_airport_id' => $this->destinationAirport->id,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Search Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_search_flights(): void
    {
        $this->createFlight([
            'departure_time' => now()->addDay()->setTime(10, 0),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/flights?' . http_build_query([
            'origin' => 'CAI',
            'destination' => 'DXB',
            'date' => now()->addDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'flight_number',
                        'airline',
                        'origin',
                        'destination',
                        'schedule',
                        'pricing',
                    ],
                ],
            ]);
    }

    public function test_search_requires_origin_destination_date(): void
    {
        $response = $this->getJson('/api/flights');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['origin', 'destination', 'date']);
    }

    public function test_search_validates_airport_codes(): void
    {
        $response = $this->getJson('/api/flights?' . http_build_query([
            'origin' => 'INVALID',
            'destination' => 'X',
            'date' => now()->addDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['origin', 'destination']);
    }

    public function test_search_validates_date_not_in_past(): void
    {
        $response = $this->getJson('/api/flights?' . http_build_query([
            'origin' => 'CAI',
            'destination' => 'DXB',
            'date' => now()->subDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_can_filter_by_stops(): void
    {
        $this->createFlight(['stops' => 0, 'departure_time' => now()->addDay()]);
        $this->createFlight(['stops' => 1, 'departure_time' => now()->addDay()]);

        $response = $this->getJson('/api/flights?' . http_build_query([
            'origin' => 'CAI',
            'destination' => 'DXB',
            'date' => now()->addDay()->format('Y-m-d'),
            'stops' => 0,
        ]));

        $response->assertStatus(200);
        foreach ($response->json('data') as $flight) {
            $this->assertEquals(0, $flight['stops']);
        }
    }

    public function test_can_filter_by_price_range(): void
    {
        $this->createFlight([
            'base_price_egp' => 500000,
            'departure_time' => now()->addDay(),
        ]);
        $this->createFlight([
            'base_price_egp' => 1500000,
            'departure_time' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/flights?' . http_build_query([
            'origin' => 'CAI',
            'destination' => 'DXB',
            'date' => now()->addDay()->format('Y-m-d'),
            'price_min' => 4000,
            'price_max' => 6000,
        ]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /*
    |--------------------------------------------------------------------------
    | Single Flight Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_get_single_flight(): void
    {
        $flight = $this->createFlight();

        $response = $this->getJson("/api/flights/{$flight->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $flight->id,
                    'flight_number' => $flight->flight_number,
                ],
            ]);
    }

    public function test_returns_404_for_invalid_flight(): void
    {
        $response = $this->getJson('/api/flights/' . Str::uuid());

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Compare Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_compare_flights(): void
    {
        $flight1 = $this->createFlight();
        $flight2 = $this->createFlight();

        // Use proper array syntax for query string
        $response = $this->getJson('/api/flights/compare?flight_ids[]=' . $flight1->id . '&flight_ids[]=' . $flight2->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'comparison' => [
                        '*' => [
                            'id',
                            'flight_number',
                            'airline',
                            'route',
                            'duration_minutes',
                            'stops',
                            'total_price_formatted',
                        ],
                    ],
                    'count',
                ],
            ]);
    }

    public function test_compare_requires_at_least_two_flights(): void
    {
        $flight = $this->createFlight();

        $response = $this->getJson('/api/flights/compare?flight_ids[]=' . $flight->id);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['flight_ids']);
    }

    public function test_compare_maximum_five_flights(): void
    {
        $flights = collect();
        for ($i = 0; $i < 6; $i++) {
            $flights->push($this->createFlight());
        }

        $queryString = $flights->map(fn ($f) => 'flight_ids[]=' . $f->id)->implode('&');
        $response = $this->getJson('/api/flights/compare?' . $queryString);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['flight_ids']);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin CRUD Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_flight(): void
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'flight_number' => 'MS999',
            'airline_id' => $this->airline->id,
            'aircraft_id' => $this->aircraft->id,
            'origin_airport_id' => $this->originAirport->id,
            'destination_airport_id' => $this->destinationAirport->id,
            'departure_time' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'arrival_time' => now()->addDays(5)->addHours(3)->format('Y-m-d H:i:s'),
            'duration_minutes' => 180,
            'stops' => 0,
            'base_price_egp' => 850000,
            'is_refundable' => true,
        ];

        $response = $this->postJson('/api/admin/flights', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Flight created successfully',
            ]);

        $this->assertDatabaseHas('flights', ['flight_number' => 'MS999']);
    }

    public function test_admin_can_update_flight(): void
    {
        Sanctum::actingAs($this->admin);

        $flight = $this->createFlight();

        $response = $this->putJson("/api/admin/flights/{$flight->id}", [
            'base_price_egp' => 900000,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('flights', [
            'id' => $flight->id,
            'base_price_egp' => 900000,
        ]);
    }

    public function test_admin_can_delete_flight(): void
    {
        Sanctum::actingAs($this->admin);

        $flight = $this->createFlight();

        $response = $this->deleteJson("/api/admin/flights/{$flight->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('flights', ['id' => $flight->id]);
    }
}