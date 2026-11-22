<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $belawanPort;
    protected Location $rotterdamPort;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->operator = User::create([
            'name' => 'Operator User',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OPERATOR,
        ]);

        $this->customer = User::create([
            'name' => 'Buyer Importer',
            'email' => 'buyer@test.com',
            'password' => bcrypt('password'),
            'company_name' => 'Amsterdam Cocoa Trading B.V.',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->carrier = Carrier::create([
            'code' => 'MSCU',
            'name' => 'Mediterranean Shipping Company',
            'mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->belawanPort = Location::create([
            'code' => 'IDBLW',
            'name' => 'Belawan Port',
            'city' => 'Medan',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'type' => 'PORT',
        ]);

        $this->rotterdamPort = Location::create([
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

    public function test_admin_and_operator_can_view_create_page(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('shipments.create'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Create Cocoa');

        $responseOp = $this->actingAs($this->operator)->get(route('shipments.create'));
        $responseOp->assertStatus(200);
        $responseOp->assertSee('Create Cocoa');
    }

    public function test_customer_cannot_view_create_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('shipments.create'));
        $response->assertStatus(403);
    }

    public function test_can_create_shipment_with_coffee_items_and_commodity_attributes(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Green Arabica Coffee Beans - Grade 1',
            'weight' => 19200,
            'volume' => 33.2,
            'expected_departure' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(26)->format('Y-m-d H:i:s'),
            'special_instructions' => 'Keep humidity below 65%, use GrainPro Hermetic Bags',
            'items' => [
                [
                    'product_category' => 'COFFEE',
                    'product_name' => 'Sumatra Gayo Highland Arabica',
                    'variety' => 'Typica & Tim Tim',
                    'grade' => 'Specialty Grade 1',
                    'package_type' => 'JUTE_BAG_60KG',
                    'package_count' => 320,
                    'net_weight' => 19200,
                    'weight_kg' => 19200,
                    'volume_cbm' => 33.2,
                    'moisture_content' => 11.5,
                    'temperature_target' => 18.0,
                    'hs_code' => '0901.11.10',
                    'is_hazardous' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->operator)->post(route('shipments.store'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shipments', [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'cargo_type' => 'Green Arabica Coffee Beans - Grade 1',
            'current_status' => Shipment::STATUS_CREATED,
        ]);

        $shipment = Shipment::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($shipment);
        $this->assertStringStartsWith('CTC-', $shipment->shipment_number);

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'product_category' => 'COFFEE',
            'product_name' => 'Sumatra Gayo Highland Arabica',
            'variety' => 'Typica & Tim Tim',
            'grade' => 'Specialty Grade 1',
            'package_count' => 320,
            'moisture_content' => 11.5,
        ]);
    }

    public function test_can_create_shipment_with_cocoa_items_and_commodity_attributes(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Fermented Cocoa Beans - Good Fermented Grade',
            'weight' => 20000,
            'volume' => 34.5,
            'expected_departure' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(24)->format('Y-m-d H:i:s'),
            'special_instructions' => 'Kraft paper lining, moisture traps inserted',
            'items' => [
                [
                    'product_category' => 'COCOA',
                    'product_name' => 'Fermented Cocoa Beans',
                    'variety' => 'Forastero Ghana',
                    'grade' => 'Good Fermented Grade (GFG)',
                    'package_type' => 'JUTE_BAG_60KG',
                    'package_count' => 333,
                    'net_weight' => 20000,
                    'weight_kg' => 20000,
                    'volume_cbm' => 34.5,
                    'moisture_content' => 7.2,
                    'temperature_target' => 18.0,
                    'hs_code' => '1801.00.00',
                    'is_hazardous' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('shipments.store'), $payload);

        $response->assertSessionHasNoErrors();
        $shipment = Shipment::where('cargo_type', 'Fermented Cocoa Beans - Good Fermented Grade')->first();
        $this->assertNotNull($shipment);

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'product_category' => 'COCOA',
            'variety' => 'Forastero Ghana',
            'grade' => 'Good Fermented Grade (GFG)',
            'moisture_content' => 7.2,
        ]);
    }

    public function test_origin_and_destination_must_be_different(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->belawanPort->id, // Identical to origin
            'cargo_type' => 'Green Coffee',
            'weight' => 5000,
            'volume' => 10,
            'expected_departure' => now()->addDay()->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->operator)->post(route('shipments.store'), $payload);
        $response->assertSessionHasErrors('destination_id');
    }

    public function test_expected_arrival_must_be_after_or_equal_to_expected_departure(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Green Coffee',
            'weight' => 5000,
            'volume' => 10,
            'expected_departure' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(2)->format('Y-m-d H:i:s'), // Earlier than departure
        ];

        $response = $this->actingAs($this->operator)->post(route('shipments.store'), $payload);
        $response->assertSessionHasErrors('expected_arrival');
    }

    public function test_can_update_shipment_and_sync_items(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CTC-2026-9999',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Initial Cargo',
            'weight' => 10000,
            'volume' => 20,
            'current_status' => Shipment::STATUS_CREATED,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(20),
            'estimated_arrival' => now()->addDays(20),
        ]);

        $updatePayload = [
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Updated Specialty Arabica',
            'weight' => 18000,
            'volume' => 30,
            'expected_departure' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(25)->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'product_category' => 'COFFEE',
                    'product_name' => 'Java Preanger Specialty',
                    'variety' => 'Typica',
                    'grade' => 'Grade 1',
                    'package_type' => 'JUTE_BAG_60KG',
                    'package_count' => 300,
                    'net_weight' => 18000,
                    'moisture_content' => 11.2,
                ],
            ],
        ];

        $response = $this->actingAs($this->operator)->put(route('shipments.update', $shipment->id), $updatePayload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'cargo_type' => 'Updated Specialty Arabica',
            'weight' => 18000,
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'product_name' => 'Java Preanger Specialty',
            'package_count' => 300,
            'moisture_content' => 11.2,
        ]);
    }

    public function test_can_filter_shipments_by_commodity_category(): void
    {
        $coffeeShipment = Shipment::create([
            'shipment_number' => 'CTC-2026-0001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Green Coffee Beans',
            'weight' => 10000,
            'volume' => 20,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->addDays(15),
            'estimated_arrival' => now()->addDays(15),
        ]);
        $coffeeShipment->items()->create([
            'product_category' => 'COFFEE',
            'product_name' => 'Arabica Gayo',
            'item_description' => 'Sumatra Arabica Gayo Coffee',
            'weight_kg' => 10000,
        ]);

        $cocoaShipment = Shipment::create([
            'shipment_number' => 'CTC-2026-0002',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Fermented Cocoa Beans',
            'weight' => 15000,
            'volume' => 25,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(3),
            'expected_arrival' => now()->addDays(18),
            'estimated_arrival' => now()->addDays(18),
        ]);
        $cocoaShipment->items()->create([
            'product_category' => 'COCOA',
            'product_name' => 'Sulawesi Cocoa',
            'item_description' => 'Sulawesi Fermented Cocoa Beans',
            'weight_kg' => 15000,
        ]);

        // Filter COFFEE
        $responseCoffee = $this->actingAs($this->admin)->get(route('shipments.index', ['cargo_category' => 'COFFEE']));
        $responseCoffee->assertStatus(200);
        $responseCoffee->assertSee('CTC-2026-0001');
        $responseCoffee->assertDontSee('CTC-2026-0002');

        // Filter COCOA
        $responseCocoa = $this->actingAs($this->admin)->get(route('shipments.index', ['cargo_category' => 'COCOA']));
        $responseCocoa->assertStatus(200);
        $responseCocoa->assertSee('CTC-2026-0002');
        $responseCocoa->assertDontSee('CTC-2026-0001');
    }

    public function test_admin_can_soft_delete_shipment(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CTC-2026-DEL1',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'To be deleted',
            'weight' => 5000,
            'volume' => 10,
            'current_status' => Shipment::STATUS_CREATED,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(10),
            'estimated_arrival' => now()->addDays(10),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('shipments.destroy', $shipment->id));
        $response->assertRedirect(route('shipments.index'));

        $this->assertSoftDeleted('shipments', ['id' => $shipment->id]);
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
    }
}
