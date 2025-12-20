<?php

namespace Tests\Feature\Api;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AirlineApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    /*
    |--------------------------------------------------------------------------
    | Public Routes Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_list_all_airlines(): void
    {
        Airline::factory()->count(5)->create();

        $response = $this->getJson('/api/airlines');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'logo_url', 'is_active'],
                ],
            ]);
    }

    public function test_can_filter_active_airlines(): void
    {
        Airline::factory()->count(3)->create(['is_active' => true]);
        Airline::factory()->count(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/airlines?active_only=true');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_search_airlines(): void
    {
        Airline::factory()->create(['name' => 'EgyptAir', 'code' => 'MS']);
        Airline::factory()->create(['name' => 'Emirates', 'code' => 'EK']);

        $response = $this->getJson('/api/airlines?search=Egypt');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_get_single_airline(): void
    {
        $airline = Airline::factory()->create();

        $response = $this->getJson("/api/airlines/{$airline->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $airline->id,
                    'code' => $airline->code,
                ],
            ]);
    }

    public function test_can_find_airline_by_code(): void
    {
        Airline::factory()->create(['code' => 'MS']);

        $response = $this->getJson('/api/airlines/code/MS');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'MS',
                ],
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Routes Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_airline(): void
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'code' => 'TS',
            'name' => 'Test Airlines',
            'logo_url' => 'https://example.com/logo.png',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/admin/airlines', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Airline created successfully',
            ]);

        $this->assertDatabaseHas('airlines', ['code' => 'TS']);
    }

    public function test_admin_can_update_airline(): void
    {
        Sanctum::actingAs($this->admin);

        $airline = Airline::factory()->create();

        $response = $this->putJson("/api/admin/airlines/{$airline->id}", [
            'name' => 'Updated Airline Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Airline updated successfully',
            ]);

        $this->assertDatabaseHas('airlines', [
            'id' => $airline->id,
            'name' => 'Updated Airline Name',
        ]);
    }

    public function test_admin_can_delete_airline(): void
    {
        Sanctum::actingAs($this->admin);

        $airline = Airline::factory()->create();

        $response = $this->deleteJson("/api/admin/airlines/{$airline->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('airlines', ['id' => $airline->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Tests
    |--------------------------------------------------------------------------
    */

    public function test_create_airline_validates_code_length(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/airlines', [
            'code' => 'TOOLONG',
            'name' => 'Test Airlines',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_create_airline_validates_unique_code(): void
    {
        Sanctum::actingAs($this->admin);

        Airline::factory()->create(['code' => 'MS']);

        $response = $this->postJson('/api/admin/airlines', [
            'code' => 'MS',
            'name' => 'Another Airline',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}