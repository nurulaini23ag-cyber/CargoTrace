<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InteractiveMapAndWeatherTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer1;
    protected User $customer2;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'https://api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 28.5,
                    'relative_humidity_2m' => 75,
                    'apparent_temperature' => 31.0,
                    'precipitation' => 0.0,
                    'weather_code' => 1,
                    'wind_speed_10m' => 12.0,
                ],
                'current_units' => [
                    'temperature_2m' => '°C',
                    'wind_speed_10m' => 'km/h',
                ],
            ], 200),
            'https://nominatim.openstreetmap.org/*' => Http::response([
                [
                    'display_name' => 'Port of Rotterdam, Netherlands',
                    'lat' => '51.9540',
                    'lon' => '4.0530',
                    'type' => 'harbour',
                ],
            ], 200),
        ]);

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $this->customer1 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->customer2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->carrier = Carrier::create([
            'code' => 'MSC',
            'name' => 'Mediterranean Shipping Company',
            'mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDBLW',
            'name' => 'Belawan Port',
            'city' => 'Medan',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'type' => 'PORT',
        ]);

        $this->destination = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'country_code' => 'NL',
            'latitude' => 51.9540,
            'longitude' => 4.0530,
            'type' => 'PORT',
        ]);
    }

    public function test_authenticated_user_can_access_map_page(): void
    {
        $this->actingAs($this->operator);

        $shipment = Shipment::create([
            'shipment_number' => 'CTC-2026-0001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Green Arabica Coffee Beans',
            'weight' => 19200,
            'volume' => 33,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->addDays(15),
            'estimated_arrival' => now()->addDays(15),
        ]);

        $response = $this->get('/map');
        $response->assertStatus(200);
        $response->assertSee('Peta Kargo Global');
        $response->assertSee('CTC-2026-0001');
    }

    public function test_customer_sees_only_own_shipments_on_map(): void
    {
        $shipment1 = Shipment::create([
            'shipment_number' => 'CTC-CUST1-001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Cocoa Beans',
            'weight' => 20000,
            'volume' => 35,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(2),
            'expected_arrival' => now()->addDays(10),
            'estimated_arrival' => now()->addDays(10),
        ]);

        $shipment2 = Shipment::create([
            'shipment_number' => 'CTC-CUST2-002',
            'customer_id' => $this->customer2->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Coffee Beans',
            'weight' => 18000,
            'volume' => 30,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(3),
            'expected_arrival' => now()->addDays(12),
            'estimated_arrival' => now()->addDays(12),
        ]);

        // Acting as Customer 1
        $this->actingAs($this->customer1);
        $response = $this->get('/map');
        $response->assertStatus(200);
        $response->assertSee('CTC-CUST1-001');
        $response->assertDontSee('CTC-CUST2-002');
    }

    public function test_public_tracking_page_can_be_accessed_without_authentication(): void
    {
        $response = $this->get('/track');
        $response->assertStatus(200);
        $response->assertSee('PUBLIC TRACKING PORTAL');
        $response->assertSee('Lacak Posisi Kargo Kapal Laut Anda');
    }

    public function test_public_tracking_searches_and_displays_live_position(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CTC-2026-9999',
            'tracking_number' => 'CTC-2026-9999',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Fermented Cocoa Beans',
            'weight' => 22000,
            'volume' => 36,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(4),
            'expected_arrival' => now()->addDays(16),
            'estimated_arrival' => now()->addDays(16),
        ]);

        // Add a waypoint
        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_type' => 'WAYPOINT_PASSED',
            'location_name' => 'Suez Canal Transit Zone',
            'latitude' => 29.9744,
            'longitude' => 32.5598,
            'event_time' => now(),
            'description' => 'Vessel entered northbound convoy.',
            'created_by' => $this->operator->id,
        ]);

        $response = $this->get('/track?q=CTC-2026-9999');
        $response->assertStatus(200);
        $response->assertSee('POSISI KARGO SAAT INI');
        $response->assertSee('Suez Canal Transit Zone');
        $response->assertSee('29.9744');
    }

    public function test_public_tracking_shows_not_found_on_invalid_number(): void
    {
        $response = $this->get('/track?q=NON-EXISTENT-999');
        $response->assertStatus(200);
        $response->assertSee('Kargo Tidak Ditemukan');
    }

    public function test_shipment_model_getCurrentPosition_resolves_appropriate_status_coordinates(): void
    {
        // 1. Delivered shipment resolves to destination coordinates
        $delivered = Shipment::create([
            'shipment_number' => 'CTC-DELV-001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Roasted Coffee',
            'weight' => 10000,
            'volume' => 20,
            'current_status' => Shipment::STATUS_DELIVERED,
            'expected_departure' => now()->subDays(20),
            'expected_arrival' => now()->subDays(1),
            'estimated_arrival' => now()->subDays(1),
        ]);

        $posDelv = $delivered->getCurrentPosition();
        $this->assertEquals(51.9540, $posDelv['latitude']);
        $this->assertEquals(4.0530, $posDelv['longitude']);
        $this->assertTrue($posDelv['is_arrived']);
        $this->assertEquals(100, $posDelv['progress_percent']);

        // 2. Created shipment resolves to origin coordinates
        $created = Shipment::create([
            'shipment_number' => 'CTC-CRTD-001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Green Coffee',
            'weight' => 12000,
            'volume' => 22,
            'current_status' => Shipment::STATUS_CREATED,
            'expected_departure' => now()->addDays(3),
            'expected_arrival' => now()->addDays(23),
            'estimated_arrival' => now()->addDays(23),
        ]);

        $posCrtd = $created->getCurrentPosition();
        $this->assertEquals(3.7833, $posCrtd['latitude']);
        $this->assertEquals(98.6833, $posCrtd['longitude']);
        $this->assertFalse($posCrtd['is_arrived']);
    }

    public function test_shipment_show_page_renders_current_cargo_position_hud(): void
    {
        $this->actingAs($this->operator);

        $shipment = Shipment::create([
            'shipment_number' => 'CTC-SHOW-001',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Cocoa Powder',
            'weight' => 15000,
            'volume' => 28,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(7),
            'expected_arrival' => now()->addDays(12),
            'estimated_arrival' => now()->addDays(12),
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_type' => 'WAYPOINT_PASSED',
            'location_name' => 'Malacca Strait - Northbound Transit Lane',
            'latitude' => 2.5000,
            'longitude' => 101.5000,
            'event_time' => now(),
            'description' => 'Vessel transiting strait.',
            'created_by' => $this->operator->id,
        ]);

        $response = $this->get("/shipments/{$shipment->id}");
        $response->assertStatus(200);
        $response->assertSee('Posisi Kargo Saat Ini');
        $response->assertSee('Malacca Strait - Northbound Transit Lane');
        $response->assertSee('Geographic Maritime Tracking Map');
    }

    public function test_map_displays_google_maps_clean_style_with_leaflet_and_openstreetmap(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/map');
        $response->assertStatus(200);
        $response->assertSee('LEAFLET + OPENSTREETMAP');
        $response->assertSee('Google / OSM Standard');
        $response->assertSee('Semua Kapal');
        $response->assertSee('Lagi Transit');
        $response->assertSee('Di Barat (Eropa)');
        $response->assertSee('Sudah Sampai');
        $response->assertSee('Sinyal Mati (LKP)');
    }

    public function test_shipment_offline_signal_tracks_real_time_last_known_position(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CTC-SIGNAL-OFF-01',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Specialty Green Coffee',
            'weight' => 21000,
            'volume' => 38,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'special_instructions' => 'OFFLINE_BLINDSPOT_INDIAN_OCEAN',
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->addDays(15),
            'estimated_arrival' => now()->addDays(15),
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_type' => 'WAYPOINT_PASSED',
            'location_name' => 'Mid-Indian Ocean Deep Trench',
            'latitude' => -5.4321,
            'longitude' => 78.1234,
            'event_time' => now()->subHours(8),
            'description' => 'Last transmission before entering satellite blindspot.',
            'created_by' => $this->operator->id,
        ]);

        $pos = $shipment->getCurrentPosition();
        $this->assertTrue($pos['is_signal_offline']);
        $this->assertEquals('OFFLINE', $pos['signal_status']);
        $this->assertStringContainsString('Last Known Position', $pos['signal_label']);
        $this->assertEquals(-5.4321, $pos['latitude']);
        $this->assertEquals(78.1234, $pos['longitude']);

        $this->actingAs($this->admin);
        $response = $this->get('/map?q=CTC-SIGNAL-OFF-01');
        $response->assertStatus(200);
        $response->assertSee('CTC-SIGNAL-OFF-01');
        $response->assertSee('Mid-Indian Ocean Deep Trench');
        $response->assertSee('Last Known Position');
    }

    public function test_map_filters_by_sector_west_and_delivery_status(): void
    {
        $this->actingAs($this->admin);

        // Filter by DELIVERED
        $delivResponse = $this->get('/map?status=DELIVERED');
        $delivResponse->assertStatus(200);
        $delivResponse->assertSee('Peta Kargo Global');

        // Filter by Sector BARAT
        $westResponse = $this->get('/map?sector=BARAT');
        $westResponse->assertStatus(200);
        $westResponse->assertSee('Peta Kargo Global');
    }
}
