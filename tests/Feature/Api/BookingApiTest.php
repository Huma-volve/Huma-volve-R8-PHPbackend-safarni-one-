<?php

namespace Tests\Feature\Api;

use App\Models\Airport;
use App\Models\Airline;
use App\Models\Aircraft;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Flight;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Flight $flight;
    protected Seat $seat;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required category FIRST (fixes foreign key constraint)
        Category::firstOrCreate(
            ['key' => 'flights'],
            ['title' => 'Flights', 'description' => 'Book flights to anywhere.']
        );

        $this->user = User::factory()->create(['is_admin' => false]);

        $airport1 = Airport::factory()->create(['code' => 'CAI']);
        $airport2 = Airport::factory()->create(['code' => 'DXB']);
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create();

        $this->flight = Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'origin_airport_id' => $airport1->id,
            'destination_airport_id' => $airport2->id,
            'departure_time' => now()->addDays(5),
            'arrival_time' => now()->addDays(5)->addHours(3),
        ]);

        $this->seat = Seat::factory()->create([
            'flight_id' => $this->flight->id,
            'is_available' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Booking Summary Tests
    |--------------------------------------------------------------------------
    */

    public function test_authenticated_user_can_create_booking_summary(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/bookings/summary', [
            'flight_id' => $this->flight->id,
            'passengers' => [
                [
                    'title' => 'Mr',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-15',
                    'passport_number' => 'A12345678',
                    'passport_expiry' => now()->addYears(5)->format('Y-m-d'),
                    'nationality' => 'EG',
                ],
            ],
            'seat_ids' => [$this->seat->id],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'flight_id',
                    'flight_number',
                    'passengers',
                    'pricing' => [
                        'base_price_per_person',
                        'total_price',
                        'formatted',
                    ],
                    'booking_token',
                    'expires_at',
                ],
            ]);
    }

    public function test_guest_cannot_create_booking_summary(): void
    {
        $response = $this->postJson('/api/bookings/summary', [
            'flight_id' => $this->flight->id,
            'passengers' => [
                [
                    'title' => 'Mr',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-15',
                    'passport_number' => 'A12345678',
                    'passport_expiry' => now()->addYears(5)->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_booking_summary_validates_passenger_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/bookings/summary', [
            'flight_id' => $this->flight->id,
            'passengers' => [
                [
                    'title' => 'Invalid',
                    'first_name' => '',
                    'last_name' => 'Doe',
                    'date_of_birth' => 'invalid-date',
                    'passport_number' => 'A12345678',
                    'passport_expiry' => now()->subYear()->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'passengers.0.title',
                'passengers.0.first_name',
                'passengers.0.date_of_birth',
                'passengers.0.passport_expiry',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_checkout_with_valid_token(): void
    {
        Sanctum::actingAs($this->user);

        // Manually create a booking summary in cache
        $bookingToken = bin2hex(random_bytes(32));

        $summary = [
            'flight_id' => $this->flight->id,
            'flight_number' => $this->flight->flight_number,
            'origin' => 'CAI',
            'destination' => 'DXB',
            'departure_time' => $this->flight->departure_time->toISOString(),
            'passengers' => [
                [
                    'title' => 'Mr',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-15',
                    'passport_number' => 'A12345678',
                    'passport_expiry' => now()->addYears(5)->format('Y-m-d'),
                    'nationality' => 'EG',
                ],
            ],
            'seat_ids' => [$this->seat->id],
            'pricing' => [
                'base_price_per_person' => $this->flight->base_price_egp,
                'passenger_count' => 1,
                'seat_modifiers' => 0,
                'subtotal' => $this->flight->base_price_egp,
                'tax_percentage' => 14,
                'tax_amount' => (int) ($this->flight->base_price_egp * 0.14),
                'total_price' => (int) ($this->flight->base_price_egp * 1.14),
            ],
            'booking_token' => $bookingToken,
            'expires_at' => now()->addMinutes(30)->toISOString(),
        ];

        Cache::put("booking_summary_{$bookingToken}", $summary, now()->addMinutes(30));

        // Then checkout
        $response = $this->postJson('/api/bookings/checkout', [
            'booking_token' => $bookingToken,
            'payment_method_id' => 'pm_test_123456',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Booking created successfully',
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'category' => 'flights',
        ]);
    }

    public function test_checkout_fails_with_invalid_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/bookings/checkout', [
            'booking_token' => str_repeat('a', 64),
            'payment_method_id' => 'pm_test_123456',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Booking session expired',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | User Bookings Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_list_own_bookings(): void
    {
        Sanctum::actingAs($this->user);

        // Create bookings for this user
        Booking::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_own_booking(): void
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $booking->id,
                ],
            ]);
    }

    public function test_user_cannot_view_other_user_booking(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Booking Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_cancel_own_booking(): void
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking cancelled successfully',
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }
}