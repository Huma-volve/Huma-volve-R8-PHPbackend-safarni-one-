<?php

namespace Tests\Feature\Api;

use App\Models\Airport;
use App\Models\Airline;
use App\Models\Aircraft;
use App\Models\Flight;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SeatApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Flight $flight;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $airport1 = Airport::factory()->create();
        $airport2 = Airport::factory()->create();
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create();

        $this->flight = Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'origin_airport_id' => $airport1->id,
            'destination_airport_id' => $airport2->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Seats Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_get_flight_seats(): void
    {
        Seat::factory()->count(10)->create([
            'flight_id' => $this->flight->id,
            'class' => 'economy',
        ]);

        Seat::factory()->count(4)->create([
            'flight_id' => $this->flight->id,
            'class' => 'business',
        ]);

        $response = $this->getJson("/api/flights/{$this->flight->id}/seats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'flight_id',
                    'seats_by_class',
                ],
            ]);
    }

    public function test_returns_404_for_invalid_flight_seats(): void
    {
        $response = $this->getJson('/api/flights/invalid-uuid/seats');

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Lock Seat Tests
    |--------------------------------------------------------------------------
    */

    public function test_authenticated_user_can_lock_seat(): void
    {
        Sanctum::actingAs($this->user);

        $seat = Seat::factory()->create([
            'flight_id' => $this->flight->id,
            'is_available' => true,
        ]);

        $response = $this->postJson('/api/seats/lock', [
            'seat_id' => $seat->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Seat locked successfully',
                ],
            ])
            ->assertJsonStructure([
                'data' => ['expires_at'],
            ]);

        // Verify seat is locked in cache
        $this->assertTrue(Cache::has("seat_lock_{$seat->id}"));
    }

    public function test_cannot_lock_unavailable_seat(): void
    {
        Sanctum::actingAs($this->user);

        $seat = Seat::factory()->create([
            'flight_id' => $this->flight->id,
            'is_available' => false,
        ]);

        $response = $this->postJson('/api/seats/lock', [
            'seat_id' => $seat->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_lock_already_locked_seat(): void
    {
        Sanctum::actingAs($this->user);

        $seat = Seat::factory()->create([
            'flight_id' => $this->flight->id,
            'is_available' => true,
        ]);

        // Lock the seat first
        Cache::put("seat_lock_{$seat->id}", true, now()->addMinutes(10));

        $response = $this->postJson('/api/seats/lock', [
            'seat_id' => $seat->id,
        ]);

        $response->assertStatus(400);
    }

    public function test_guest_cannot_lock_seat(): void
    {
        $seat = Seat::factory()->create([
            'flight_id' => $this->flight->id,
            'is_available' => true,
        ]);

        $response = $this->postJson('/api/seats/lock', [
            'seat_id' => $seat->id,
        ]);

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | Release Seat Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_release_locked_seat(): void
    {
        Sanctum::actingAs($this->user);

        $seat = Seat::factory()->create([
            'flight_id' => $this->flight->id,
            'is_available' => true,
        ]);

        // Lock the seat
        Cache::put("seat_lock_{$seat->id}", true, now()->addMinutes(10));

        $response = $this->deleteJson("/api/seats/{$seat->id}/release");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Seat released successfully',
            ]);

        $this->assertFalse(Cache::has("seat_lock_{$seat->id}"));
    }
}