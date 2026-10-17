<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiShipmentEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $dest;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->carrier = Carrier::create(['code' => 'APIC', 'name' => 'API Carrier', 'mode' => 'SEA', 'is_active' => true]);
        $this->origin = Location::create(['code' => 'APIO', 'name' => 'Origin Hub', 'city' => 'Jakarta', 'country' => 'Indonesia', 'country_code' => 'ID', 'latitude' => -6.1, 'longitude' => 106.8, 'type' => 'PORT']);
        $this->dest = Location::create(['code' => 'APID', 'name' => 'Dest Hub', 'city' => 'Singapore', 'country' => 'Singapore', 'country_code' => 'SG', 'latitude' => 1.2, 'longitude' => 103.8, 'type' => 'PORT']);

        $this->shipment = Shipment::create([
            'shipment_number' => 'TRC-API-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->dest->id,
            'cargo_type' => 'API Machinery',
            'weight' => 5000,
            'volume' => 20,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
        ]);
    }

    public function test_unauthenticated_request_is_rejected_401(): void
    {
        $response = $this->getJson('/api/shipments');
        $response->assertStatus(401);
    }

    public function test_get_shipments_list_returns_200_with_pagination(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/shipments');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'shipment_number', 'current_status', 'carrier', 'origin', 'destination']
            ],
            'meta' => ['current_page', 'last_page', 'total']
        ]);
    }

    public function test_customer_receives_only_own_shipments(): void
    {
        $otherCustomer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $otherShipment = Shipment::create([
            'shipment_number' => 'TRC-OTHER-999',
            'customer_id' => $otherCustomer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->dest->id,
            'cargo_type' => 'Other Goods',
            'weight' => 100,
            'volume' => 1,
            'current_status' => Shipment::STATUS_CREATED,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(2),
        ]);

        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/shipments');
        $response->assertStatus(200);
        $shipmentNumbers = collect($response->json('data'))->pluck('shipment_number');

        $this->assertContains('TRC-API-001', $shipmentNumbers);
        $this->assertNotContains('TRC-OTHER-999', $shipmentNumbers);
    }

    public function test_post_shipment_creates_record_with_201(): void
    {
        Sanctum::actingAs($this->operator);

        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->dest->id,
            'cargo_type' => 'Telecommunications Gear',
            'weight' => 1200.50,
            'volume' => 8.20,
            'expected_departure' => now()->addDay()->toDateTimeString(),
            'expected_arrival' => now()->addDays(7)->toDateTimeString(),
        ];

        $response = $this->postJson('/api/shipments', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('shipments', ['cargo_type' => 'Telecommunications Gear']);
    }

    public function test_post_shipment_validates_required_fields_422(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->postJson('/api/shipments', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_id', 'carrier_id', 'origin_id', 'destination_id', 'cargo_type', 'weight', 'volume']);
    }

    public function test_get_shipment_by_id_returns_200(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/shipments/' . $this->shipment->id);
        $response->assertStatus(200);
        $response->assertJsonPath('data.shipment_number', 'TRC-API-001');
    }

    public function test_get_nonexistent_shipment_returns_404(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/shipments/99999');
        $response->assertStatus(404);
    }

    public function test_post_tracking_event_appends_event_201(): void
    {
        Sanctum::actingAs($this->operator);

        $payload = [
            'event_type' => 'PORT_CLEARANCE',
            'location_name' => 'Tanjung Priok Outbound Scan',
            'latitude' => -6.10,
            'longitude' => 106.88,
            'description' => 'Container cleared for loading.',
        ];

        $response = $this->postJson("/api/shipments/{$this->shipment->id}/tracking", $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('data.event_type', 'PORT_CLEARANCE');
        $this->assertDatabaseHas('tracking_events', ['shipment_id' => $this->shipment->id, 'event_type' => 'PORT_CLEARANCE']);
    }

    public function test_post_exception_logs_issue_and_adjusts_eta_201(): void
    {
        Sanctum::actingAs($this->operator);

        $payload = [
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'MEDIUM',
            'impact_hours' => 12,
            'description' => 'Physical document verification hold by customs.',
        ];

        $response = $this->postJson("/api/shipments/{$this->shipment->id}/exceptions", $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('shipment_exceptions', [
            'shipment_id' => $this->shipment->id,
            'exception_type' => 'CUSTOMS_HOLD',
            'status' => 'OPEN',
        ]);

        // Verify delay was calculated and shipment marked delayed
        $this->assertTrue($this->shipment->fresh()->is_delayed);
    }

    public function test_patch_exception_resolves_issue_200(): void
    {
        Sanctum::actingAs($this->operator);

        $exception = ShipmentException::create([
            'shipment_id' => $this->shipment->id,
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'MEDIUM',
            'status' => 'OPEN',
            'detected_at' => now(),
            'description' => 'Hold pending docs',
            'logged_by' => $this->operator->id,
        ]);

        $response = $this->patchJson("/api/exceptions/{$exception->id}", [
            'status' => 'RESOLVED',
            'resolution_notes' => 'Permit presented and container cleared.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'RESOLVED');
        $this->assertEquals('RESOLVED', $exception->fresh()->status);
        $this->assertNotNull($exception->fresh()->resolved_at);
    }

    public function test_carriers_and_locations_endpoints_200(): void
    {
        Sanctum::actingAs($this->operator);

        $carRes = $this->getJson('/api/carriers');
        $carRes->assertStatus(200);
        $carRes->assertJsonPath('status', 'success');

        $locRes = $this->getJson('/api/locations');
        $locRes->assertStatus(200);
        $locRes->assertJsonPath('status', 'success');
    }
}
