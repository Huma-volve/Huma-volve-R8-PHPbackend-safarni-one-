<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Airport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AirportApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->user = User::factory()->create(['role' => UserRole::USER]);
    }

    /*
    |--------------------------------------------------------------------------
    | Public Routes Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_list_all_airports(): void
    {
        Airport::factory()->count(5)->create();

        $response = $this->getJson('/api/airports');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'city', 'location', 'timezone'],
                ],
            ]);
    }

    public function test_can_search_airports(): void
    {
        Airport::factory()->create(['city' => 'Cairo', 'code' => 'CAI']);
        Airport::factory()->create(['city' => 'Dubai', 'code' => 'DXB']);

        $response = $this->getJson('/api/airports?search=Cairo');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_get_single_airport(): void
    {
        $airport = Airport::factory()->create();

        $response = $this->getJson("/api/airports/{$airport->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $airport->id,
                    'code' => $airport->code,
                ],
            ]);
    }

    public function test_can_find_airport_by_code(): void
    {
        $airport = Airport::factory()->create(['code' => 'CAI']);

        $response = $this->getJson('/api/airports/code/CAI');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'CAI',
                ],
            ]);
    }

    public function test_returns_404_for_invalid_airport_code(): void
    {
        $response = $this->getJson('/api/airports/code/XXX');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Airport not found',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Routes Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_airport(): void
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'code' => 'TST',
            'name' => 'Test Airport',
            'city' => 'Test City',
            'latitude' => 30.1234567,
            'longitude' => 31.1234567,
            'timezone' => 'Africa/Cairo',
        ];

        $response = $this->postJson('/api/admin/airports', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Airport created successfully',
            ]);

        $this->assertDatabaseHas('airports', ['code' => 'TST']);
    }

    public function test_admin_can_update_airport(): void
    {
        Sanctum::actingAs($this->admin);

        $airport = Airport::factory()->create();

        $response = $this->putJson("/api/admin/airports/{$airport->id}", [
            'name' => 'Updated Airport Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Airport updated successfully',
            ]);

        $this->assertDatabaseHas('airports', [
            'id' => $airport->id,
            'name' => 'Updated Airport Name',
        ]);
    }

    public function test_admin_can_delete_airport(): void
    {
        Sanctum::actingAs($this->admin);

        $airport = Airport::factory()->create();

        $response = $this->deleteJson("/api/admin/airports/{$airport->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Airport deleted successfully',
            ]);

        $this->assertDatabaseMissing('airports', ['id' => $airport->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Tests
    |--------------------------------------------------------------------------
    */

    public function test_create_airport_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/airports', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'city', 'latitude', 'longitude']);
    }

    public function test_create_airport_validates_code_format(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/airports', [
            'code' => 'TOOLONG',
            'name' => 'Test',
            'city' => 'Test',
            'latitude' => 30.0,
            'longitude' => 31.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_create_airport_validates_unique_code(): void
    {
        Sanctum::actingAs($this->admin);

        Airport::factory()->create(['code' => 'CAI']);

        $response = $this->postJson('/api/admin/airports', [
            'code' => 'CAI',
            'name' => 'Another Cairo Airport',
            'city' => 'Cairo',
            'latitude' => 30.0,
            'longitude' => 31.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_create_airport_validates_latitude_range(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/airports', [
            'code' => 'TST',
            'name' => 'Test',
            'city' => 'Test',
            'latitude' => 100.0, // Invalid: > 90
            'longitude' => 31.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }
}
