<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customerA;
    protected User $customerB;
    protected Shipment $shipmentA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $this->customerA = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->customerB = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $carrier = Carrier::create(['code' => 'TEST', 'name' => 'Carrier Test', 'mode' => 'SEA']);
        $origin = Location::create(['code' => 'L1', 'name' => 'Loc 1', 'city' => 'C1', 'country' => 'ID', 'country_code' => 'ID', 'latitude' => 0, 'longitude' => 0]);
        $dest = Location::create(['code' => 'L2', 'name' => 'Loc 2', 'city' => 'C2', 'country' => 'SG', 'country_code' => 'SG', 'latitude' => 1, 'longitude' => 1]);

        $this->shipmentA = Shipment::create([
            'shipment_number' => 'TRC-OWN-001',
            'customer_id' => $this->customerA->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'cargo_type' => 'Garments',
            'weight' => 2000,
            'volume' => 10,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
        ]);
    }

    public function test_customer_can_view_own_shipment(): void
    {
        $response = $this->actingAs($this->customerA)
            ->get('/shipments/' . $this->shipmentA->id);

        $response->assertStatus(200);
        $response->assertSee('TRC-OWN-001');
    }

    public function test_customer_forbidden_from_viewing_other_customers_shipment(): void
    {
        $response = $this->actingAs($this->customerB)
            ->get('/shipments/' . $this->shipmentA->id);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_delete_shipment(): void
    {
        $response = $this->actingAs($this->customerA)
            ->delete('/shipments/' . $this->shipmentA->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('shipments', ['id' => $this->shipmentA->id, 'deleted_at' => null]);
    }

    public function test_operator_cannot_delete_shipment(): void
    {
        $response = $this->actingAs($this->operator)
            ->delete('/shipments/' . $this->shipmentA->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('shipments', ['id' => $this->shipmentA->id, 'deleted_at' => null]);
    }

    public function test_admin_can_soft_delete_shipment(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete('/shipments/' . $this->shipmentA->id);

        $response->assertRedirect('/shipments');
        $this->assertSoftDeleted('shipments', ['id' => $this->shipmentA->id]);
    }
}
