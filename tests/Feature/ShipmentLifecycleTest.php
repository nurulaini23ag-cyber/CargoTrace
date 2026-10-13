<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected ShipmentService $shipmentService;
    protected TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipmentService = app(ShipmentService::class);
        $this->trackingService = app(TrackingService::class);

        $this->operator = User::create([
            'name' => 'Test Operator',
            'email' => 'op@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OPERATOR,
        ]);

        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'cust@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->carrier = Carrier::create([
            'code' => 'MAEU',
            'name' => 'Maersk',
            'mode' => 'SEA',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDJKT',
            'name' => 'Jakarta Port',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -6.1,
            'longitude' => 106.8,
            'type' => 'PORT',
        ]);

        $this->destination = Location::create([
            'code' => 'NLRTM',
            'name' => 'Rotterdam Port',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'country_code' => 'NL',
            'latitude' => 51.9,
            'longitude' => 4.0,
            'type' => 'PORT',
        ]);
    }

    public function test_valid_shipment_lifecycle_transitions(): void
    {
        // 1. Create Shipment (initial status CREATED)
        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Electronics',
            'weight' => 5000,
            'volume' => 12,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(10),
        ], $this->operator);

        $this->assertEquals(Shipment::STATUS_CREATED, $shipment->current_status);

        // 2. CREATED -> BOOKED
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_BOOKED, 'Slot booked', $this->operator);
        $this->assertEquals(Shipment::STATUS_BOOKED, $shipment->fresh()->current_status);

        // 3. BOOKED -> PICKED_UP
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_PICKED_UP, 'Cargo received', $this->operator);
        $this->assertEquals(Shipment::STATUS_PICKED_UP, $shipment->fresh()->current_status);
        $this->assertNotNull($shipment->fresh()->actual_departure);

        // 4. PICKED_UP -> IN_TRANSIT
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_IN_TRANSIT, 'Departed port', $this->operator);
        $this->assertEquals(Shipment::STATUS_IN_TRANSIT, $shipment->fresh()->current_status);

        // 5. IN_TRANSIT -> DELIVERED
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_DELIVERED, 'Customer signed', $this->operator);
        $this->assertEquals(Shipment::STATUS_DELIVERED, $shipment->fresh()->current_status);
        $this->assertNotNull($shipment->fresh()->actual_arrival);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Garments',
            'weight' => 2000,
            'volume' => 8,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(5),
        ], $this->operator);

        $this->expectException(DomainException::class);
        // Direct transition from CREATED to DELIVERED is forbidden by business rules
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_DELIVERED);
    }

    public function test_terminal_delivered_status_cannot_revert(): void
    {
        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Coffee',
            'weight' => 1000,
            'volume' => 4,
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->subDay(),
        ], $this->operator);

        // Advance to terminal DELIVERED status
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_BOOKED);
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_PICKED_UP);
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_IN_TRANSIT);
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_DELIVERED);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Terminal state violation');

        // Cannot revert back to CREATED or IN_TRANSIT
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_IN_TRANSIT);
    }

    public function test_tracking_events_are_append_only_and_chronological(): void
    {
        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Textiles',
            'weight' => 3000,
            'volume' => 6,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(8),
        ], $this->operator);

        $t1 = now()->addHour();
        $t2 = now()->addHours(3);

        $event1 = $this->trackingService->addEvent($shipment, [
            'event_type' => 'CONTAINER_GATE_IN',
            'location_name' => 'Tanjung Priok Gate',
            'event_time' => $t1,
            'description' => 'Container arrived at terminal yard.',
        ], $this->operator);

        $event2 = $this->trackingService->addEvent($shipment, [
            'event_type' => 'LOADED_ON_VESSEL',
            'location_name' => 'Berth 2',
            'event_time' => $t2,
            'description' => 'Container lifted onto vessel deck.',
        ], $this->operator);

        // Previous event is not overwritten (append-only)
        $this->assertDatabaseHas('tracking_events', ['id' => $event1->id]);
        $this->assertDatabaseHas('tracking_events', ['id' => $event2->id]);
        $this->assertCount(3, $shipment->trackingEvents); // initial CREATED + 2 custom events

        // Chronological order violation check: adding an event with timestamp earlier than $t2 must fail
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Chronological sequence violation');

        $this->trackingService->addEvent($shipment, [
            'event_type' => 'INVALID_PAST_EVENT',
            'location_name' => 'Ghost Station',
            'event_time' => now()->addHours(2), // Earlier than $t2 (which is addHours(3))
            'description' => 'Should fail due to chronological ordering.',
        ], $this->operator);
    }
}
