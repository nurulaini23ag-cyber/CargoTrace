<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\SystemSetting;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\EtaService;
use App\Services\ExceptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionManagementAndDelayTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $belawanPort;
    protected Location $rotterdamPort;
    protected EtaService $etaService;
    protected ExceptionService $exceptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->etaService = app(EtaService::class);
        $this->exceptionService = app(ExceptionService::class);

        $this->admin = User::create([
            'name' => 'Admin Logistics',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->operator = User::create([
            'name' => 'Operator Station',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OPERATOR,
        ]);

        $this->customer = User::create([
            'name' => 'Consignee Trading B.V.',
            'email' => 'buyer@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->carrier = Carrier::create([
            'code' => 'CMA',
            'name' => 'CMA CGM',
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

    protected function createShipment(string $status = Shipment::STATUS_IN_TRANSIT): Shipment
    {
        static $n = 500;
        $n++;
        return Shipment::create([
            'shipment_number' => "CTC-2026-X{$n}",
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->belawanPort->id,
            'destination_id' => $this->rotterdamPort->id,
            'cargo_type' => 'Raw Fermented Cocoa Beans',
            'weight' => 20000,
            'volume' => 34.5,
            'current_status' => $status,
            'expected_departure' => Carbon::parse('2026-11-01 10:00:00'),
            'expected_arrival' => Carbon::parse('2026-11-20 12:00:00'),
            'estimated_arrival' => Carbon::parse('2026-11-20 12:00:00'),
        ]);
    }

    public function test_deterministic_delay_calculation_and_severity_classification(): void
    {
        SystemSetting::set('delay_threshold_low', 24.0);
        SystemSetting::set('delay_threshold_medium', 72.0);

        $shipment = $this->createShipment();

        // 1. On time: ETA <= Expected
        $onTime = $this->etaService->evaluateDelay($shipment, Carbon::parse('2026-11-20 10:00:00'));
        $this->assertFalse($onTime['is_delayed']);
        $this->assertEquals(0.0, $onTime['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_NONE, $onTime['delay_severity']);

        // 2. LOW Severity: 0 < delay <= 24h (e.g. +12 hours)
        $lowDelay = $this->etaService->evaluateDelay($shipment, Carbon::parse('2026-11-21 00:00:00'));
        $this->assertTrue($lowDelay['is_delayed']);
        $this->assertEquals(12.0, $lowDelay['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_LOW, $lowDelay['delay_severity']);

        // 3. MEDIUM Severity: 24h < delay <= 72h (e.g. +48 hours)
        $medDelay = $this->etaService->evaluateDelay($shipment, Carbon::parse('2026-11-22 12:00:00'));
        $this->assertTrue($medDelay['is_delayed']);
        $this->assertEquals(48.0, $medDelay['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_MEDIUM, $medDelay['delay_severity']);

        // 4. HIGH Severity: delay > 72h (e.g. +96 hours)
        $highDelay = $this->etaService->evaluateDelay($shipment, Carbon::parse('2026-11-24 12:00:00'));
        $this->assertTrue($highDelay['is_delayed']);
        $this->assertEquals(96.0, $highDelay['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_HIGH, $highDelay['delay_severity']);
    }

    public function test_minimum_delay_threshold_tolerance(): void
    {
        // Require delay > 2.0 hours before setting is_delayed = true
        SystemSetting::set('delay_threshold_min', 2.0);
        SystemSetting::set('delay_threshold_low', 24.0);

        $shipment = $this->createShipment();

        // Minor delay: 1 hour -> should NOT flag is_delayed
        $minor = $this->etaService->evaluateDelay($shipment, Carbon::parse('2026-11-20 13:00:00'));
        $this->assertFalse($minor['is_delayed']);
        $this->assertEquals(1.0, $minor['delay_hours']);

        // Significant delay: 3 hours -> should flag is_delayed = true
        $significant = $this->etaService->evaluateDelay($shipment, Carbon::parse('2026-11-20 15:00:00'));
        $this->assertTrue($significant['is_delayed']);
        $this->assertEquals(3.0, $significant['delay_hours']);
    }

    public function test_log_exception_updates_eta_and_dispatches_notification(): void
    {
        $shipment = $this->createShipment();

        $exception = $this->exceptionService->logException($shipment, [
            'exception_type' => ShipmentException::TYPE_CUSTOMS_HOLD,
            'severity' => ShipmentException::SEVERITY_HIGH,
            'impact_hours' => 36,
            'description' => 'Cocoa phytosanitary quarantine sampling hold at Rotterdam customs terminal.',
            'location_name' => 'Port of Rotterdam Terminal Gate',
        ], $this->operator);

        $this->assertEquals(ShipmentException::STATUS_OPEN, $exception->status);
        $this->assertEquals(36, $exception->impact_hours);

        // Shipment ETA was adjusted by +36h
        $freshShipment = $shipment->fresh();
        $this->assertTrue($freshShipment->is_delayed);
        $this->assertEquals(36.0, $freshShipment->delay_hours);

        // Tracking event was appended with is_exception_event = true
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'EXCEPTION_OCCURRED',
            'is_exception_event' => 1,
        ]);

        // Customer received an alert notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'shipment_id' => $shipment->id,
            'type' => 'EXCEPTION_ALERT',
        ]);
    }

    public function test_exception_resolution_workflow(): void
    {
        $shipment = $this->createShipment();

        $exception = $this->exceptionService->logException($shipment, [
            'exception_type' => ShipmentException::TYPE_WEATHER_DELAY,
            'severity' => ShipmentException::SEVERITY_MEDIUM,
            'impact_hours' => 18,
            'description' => 'Rough seas in Bay of Biscay forcing reduced vessel speed.',
        ], $this->operator);

        // Step 1: Advance to INVESTIGATING
        $this->exceptionService->updateStatus($exception, ShipmentException::STATUS_INVESTIGATING, 'Vessel captain coordinating route deviation', $this->operator);
        $this->assertEquals(ShipmentException::STATUS_INVESTIGATING, $exception->fresh()->status);
        $this->assertNull($exception->fresh()->resolved_at);

        // Step 2: Advance to RESOLVED
        $this->exceptionService->updateStatus($exception, ShipmentException::STATUS_RESOLVED, 'Weather cleared. Vessel resumed cruising speed at 21 knots.', $this->operator);
        $resolved = $exception->fresh();
        $this->assertEquals(ShipmentException::STATUS_RESOLVED, $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
        $this->assertEquals($this->operator->id, $resolved->resolved_by);
        $this->assertStringContainsString('Weather cleared', $resolved->resolution_notes);

        // Confirmation tracking event was appended
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'EXCEPTION_RESOLVED',
        ]);
    }

    public function test_artisan_delay_check_command_aliases(): void
    {
        $shipment = $this->createShipment();
        // Give it an overdue estimated arrival
        $shipment->update([
            'estimated_arrival' => Carbon::parse('2026-11-22 12:00:00'),
            'is_delayed' => false,
        ]);

        // Run via alias cargo:check-delays
        $this->artisan('cargo:check-delays')
            ->expectsOutputToContain('Starting automated shipment schedule and delay audit...')
            ->assertExitCode(0);

        $this->assertTrue($shipment->fresh()->is_delayed);
        $this->assertEquals(48.0, $shipment->fresh()->delay_hours);
    }

    public function test_admin_can_update_threshold_settings_and_apply_presets(): void
    {
        // 1. Update settings form
        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'delay_threshold_min' => 1.5,
            'delay_threshold_low' => 10.0,
            'delay_threshold_medium' => 30.0,
            'group_scenario' => 'CUSTOM',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(1.5, (float) SystemSetting::get('delay_threshold_min'));
        $this->assertEquals(10.0, (float) SystemSetting::get('delay_threshold_low'));
        $this->assertEquals(30.0, (float) SystemSetting::get('delay_threshold_medium'));

        // 2. Apply Group B preset (Slide 23)
        $responseB = $this->actingAs($this->admin)->post(route('settings.preset', 'B'));
        $responseB->assertSessionHas('success');
        $this->assertEquals('B', SystemSetting::get('group_scenario'));
        $this->assertEquals(8.0, (float) SystemSetting::get('delay_threshold_low'));
        $this->assertEquals(18.0, (float) SystemSetting::get('delay_threshold_medium'));
    }
}
