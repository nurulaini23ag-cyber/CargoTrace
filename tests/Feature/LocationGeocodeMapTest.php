<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationGeocodeMapTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
        ]);
    }

    public function test_admin_and_operator_can_view_locations_directory(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/locations');
        $response->assertStatus(200);
        $response->assertSee('Direktori Titik Lokasi', false);
        $response->assertSee('Google Maps', false);

        $this->actingAs($this->operator);
        $responseOperator = $this->get('/locations');
        $responseOperator->assertStatus(200);
    }

    public function test_geocode_lookup_returns_port_of_belawan_medan(): void
    {
        $this->actingAs($this->operator);

        $response = $this->getJson('/locations/geocode-lookup?q=Belawan+Medan');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertNotEmpty($response->json('data'));

        $first = $response->json('data.0');
        $this->assertStringContainsString('Belawan', $first['name']);
        $this->assertEquals('Medan', $first['city']);
        $this->assertEquals('Indonesia', $first['country']);
        $this->assertEquals('ID', $first['country_code']);
        $this->assertEquals(3.7833, round((float) $first['latitude'], 4));
        $this->assertEquals(98.6833, round((float) $first['longitude'], 4));
        $this->assertEquals('IDBLW', $first['suggested_code']);
    }

    public function test_reverse_geocode_returns_coordinates_metadata(): void
    {
        $this->actingAs($this->operator);

        $response = $this->getJson('/locations/reverse-geocode?lat=3.7833&lng=98.6833');

        $response->assertStatus(200);
        $this->assertContains($response->json('status'), ['success', 'fallback']);
        $this->assertNotNull($response->json('data.city'));
        $this->assertNotNull($response->json('data.country'));
    }

    public function test_operator_can_store_new_location_from_map(): void
    {
        $this->actingAs($this->operator);

        $payload = [
            'code' => 'IDBLW',
            'name' => 'Port of Belawan (Medan)',
            'city' => 'Medan',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'type' => 'PORT',
        ];

        $response = $this->post('/locations', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('locations', [
            'code' => 'IDBLW',
            'city' => 'Medan',
            'country_code' => 'ID',
        ]);
    }
}
