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

class TrackingTimelineStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $belawanPort;
    protected Location $rotterdamPort;
    protected ShipmentService $shipmentService;
    protected TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipmentService = app(ShipmentService::class);
        $this->trackingService = app(TrackingService::class);

        $this->admin = User::create([
            'name' => 'Admin Logistics',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->operator = User::create([
            'name' => 'Operator Port',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OPERATOR,
        ]);

        $this->customer = User::create([
            'name' => 'Consignee Buyer',
            'email' => 'buyer@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->carrier = Carrier::create([
            'code' => 'MAEU',
            'name' => 'Maersk Line',
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

    protected function createSampleShipment(string $status = Shipment::STATUS_CREATED): Shipment
    {
        static $seq = 100;
        $seq++;
        return Shipment::create([
            'shipment_number' => "CTC-2026-T{$seq}",
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Sumatra Arabica Gayo Coffee',
            'weight' => 19200,
            'volume' => 33.2,
            'current_status' => $status,
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(20),
            'estimated_arrival' => now()->addDays(20),
        ]);
    }

    public function test_linear_status_lifecycle_progression(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_CREATED);
        $this->assertEquals(Shipment::STATUS_CREATED, $shipment->current_status);

        // 1. CREATED -> BOOKED
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_BOOKED, 'Slot confirmed', $this->operator);
        $this->assertEquals(Shipment::STATUS_BOOKED, $shipment->fresh()->current_status);

        // 2. BOOKED -> PICKED_UP
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_PICKED_UP, 'Container gated in', $this->operator);
        $this->assertEquals(Shipment::STATUS_PICKED_UP, $shipment->fresh()->current_status);
        $this->assertNotNull($shipment->fresh()->actual_departure);

        // 3. PICKED_UP -> IN_TRANSIT
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_IN_TRANSIT, 'Vessel departed', $this->operator);
        $this->assertEquals(Shipment::STATUS_IN_TRANSIT, $shipment->fresh()->current_status);

        // 4. IN_TRANSIT -> DELIVERED
        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_DELIVERED, 'Consignee received', $this->operator);
        $this->assertEquals(Shipment::STATUS_DELIVERED, $shipment->fresh()->current_status);
        $this->assertNotNull($shipment->fresh()->actual_arrival);
    }

    public function test_invalid_status_skipping_throws_domain_exception(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_CREATED);

        // Attempt invalid skip: CREATED -> DELIVERED
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid status transition from 'CREATED' to 'DELIVERED'");

        $this->shipmentService->transitionStatus($shipment, Shipment::STATUS_DELIVERED);
    }

    public function test_cancellation_allowed_from_created_booked_picked_up_only(): void
    {
        // Cancel from CREATED
        $s1 = $this->createSampleShipment(Shipment::STATUS_CREATED);
        $this->shipmentService->transitionStatus($s1, Shipment::STATUS_CANCELLED, 'Cancelled early');
        $this->assertEquals(Shipment::STATUS_CANCELLED, $s1->fresh()->current_status);

        // Cancel from BOOKED
        $s2 = $this->createSampleShipment(Shipment::STATUS_BOOKED);
        $this->shipmentService->transitionStatus($s2, Shipment::STATUS_CANCELLED, 'Carrier cancelled booking');
        $this->assertEquals(Shipment::STATUS_CANCELLED, $s2->fresh()->current_status);

        // Cancel from PICKED_UP
        $s3 = $this->createSampleShipment(Shipment::STATUS_PICKED_UP);
        $this->shipmentService->transitionStatus($s3, Shipment::STATUS_CANCELLED, 'Quality inspection failed at CFS');
        $this->assertEquals(Shipment::STATUS_CANCELLED, $s3->fresh()->current_status);

        // Cancel from IN_TRANSIT must fail
        $s4 = $this->createSampleShipment(Shipment::STATUS_IN_TRANSIT);
        $this->expectException(DomainException::class);
        $this->shipmentService->transitionStatus($s4, Shipment::STATUS_CANCELLED);
    }

    public function test_terminal_states_cannot_transition(): void
    {
        // Delivered is terminal
        $deliveredShipment = $this->createSampleShipment(Shipment::STATUS_DELIVERED);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Terminal state violation');
        $this->shipmentService->transitionStatus($deliveredShipment, Shipment::STATUS_IN_TRANSIT);
    }

    public function test_tracking_events_are_append_only_and_cannot_be_updated_or_deleted(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_CREATED);

        $event = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'event_type' => 'BOOKED',
            'status' => 'BOOKED',
            'event_time' => now(),
            'location_name' => 'Jakarta Depot',
            'description' => 'Original booking note',
            'created_by' => $this->operator->id,
        ]);

        // Attempting to update throws DomainException
        try {
            $event->update(['description' => 'Hacked description']);
            $this->fail('Expected DomainException was not thrown on update.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Tracking events are append-only', $e->getMessage());
        }

        // Attempting to delete throws DomainException
        try {
            $event->delete();
            $this->fail('Expected DomainException was not thrown on delete.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Tracking events are immutable', $e->getMessage());
        }

        // Event remains in database untouched
        $this->assertDatabaseHas('tracking_events', [
            'id' => $event->id,
            'description' => 'Original booking note',
        ]);
    }

    public function test_tracking_events_must_be_chronological(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_CREATED);

        $time1 = now();
        $this->trackingService->addEvent($shipment, [
            'event_type' => 'BOOKED',
            'event_time' => $time1,
            'location_name' => 'Belawan Port',
            'description' => 'First event at POL',
        ], $this->operator);

        // Attempt to log an event with timestamp earlier than time1
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Chronological sequence violation');

        $this->trackingService->addEvent($shipment, [
            'event_type' => 'PICKED_UP',
            'event_time' => $time1->copy()->subHours(2), // 2 hours in the past
            'location_name' => 'Belawan Yard',
            'description' => 'Backdated event',
        ], $this->operator);
    }

    public function test_auto_update_status_and_live_coordinates_from_tracking_event(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_BOOKED);

        // Operator logs PICKED_UP event -> should auto-advance shipment status to PICKED_UP
        $this->trackingService->addEvent($shipment, [
            'event_type' => 'PICKED_UP',
            'location_name' => 'CFS Belawan Terminal',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'event_time' => now()->addMinute(),
            'description' => 'Container sealed with high-security bolt seal.',
        ], $this->operator);

        $freshShipment = $shipment->fresh();
        $this->assertEquals(Shipment::STATUS_PICKED_UP, $freshShipment->current_status);
        $this->assertEquals('CFS Belawan Terminal', $freshShipment->current_location_name);
        $this->assertEquals(3.7833, $freshShipment->current_latitude);
        $this->assertEquals(98.6833, $freshShipment->current_longitude);
    }

    public function test_operator_can_add_tracking_event_via_web_form(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_BOOKED);

        $response = $this->actingAs($this->operator)
            ->post(route('shipments.tracking.store', $shipment->id), [
                'event_type' => 'PICKED_UP',
                'location_name' => 'Belawan CFS Yard 2',
                'latitude' => 3.7850,
                'longitude' => 98.6850,
                'event_time' => now()->addMinute()->format('Y-m-d H:i:s'),
                'description' => 'Container gate-in completed. Bags moisture verified at 11.4%.',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'PICKED_UP',
            'location_name' => 'Belawan CFS Yard 2',
        ]);
        $this->assertEquals(Shipment::STATUS_PICKED_UP, $shipment->fresh()->current_status);
    }

    public function test_customer_cannot_add_tracking_event(): void
    {
        $shipment = $this->createSampleShipment(Shipment::STATUS_BOOKED);

        $response = $this->actingAs($this->customer)
            ->post(route('shipments.tracking.store', $shipment->id), [
                'event_type' => 'IN_TRANSIT',
                'location_name' => 'Unauthorized Post',
                'description' => 'Customer trying to append event',
            ]);

        $response->assertStatus(403);
    }
}
