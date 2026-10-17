<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaAndDelayTest extends TestCase
{
    use RefreshDatabase;

    protected EtaService $etaService;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->etaService = app(EtaService::class);

        SystemSetting::set('delay_threshold_low', 6.0);
        SystemSetting::set('delay_threshold_medium', 24.0);

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $carrier = Carrier::create(['code' => 'TEST', 'name' => 'Test Carrier', 'mode' => 'SEA']);
        $origin = Location::create(['code' => 'LOC1', 'name' => 'Loc 1', 'city' => 'City1', 'country' => 'Country1', 'country_code' => 'C1', 'latitude' => 0, 'longitude' => 0]);
        $dest = Location::create(['code' => 'LOC2', 'name' => 'Loc 2', 'city' => 'City2', 'country' => 'Country2', 'country_code' => 'C2', 'latitude' => 10, 'longitude' => 10]);

        $this->shipment = Shipment::create([
            'shipment_number' => 'TRC-TEST-001',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'cargo_type' => 'Test Cargo',
            'weight' => 1000,
            'volume' => 5,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => Carbon::parse('2026-11-01 10:00:00'),
            'expected_arrival' => Carbon::parse('2026-11-10 12:00:00'),
            'estimated_arrival' => Carbon::parse('2026-11-10 12:00:00'),
        ]);
    }

    public function test_on_time_shipment_has_no_delay(): void
    {
        $eval = $this->etaService->evaluateDelay($this->shipment, Carbon::parse('2026-11-10 10:00:00'));

        $this->assertFalse($eval['is_delayed']);
        $this->assertEquals(0.0, $eval['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_NONE, $eval['delay_severity']);
    }

    public function test_low_severity_delay_calculation(): void
    {
        // Expected: 12:00:00, Estimated: 16:00:00 (+4 hours)
        $newEta = Carbon::parse('2026-11-10 16:00:00');
        $eval = $this->etaService->evaluateDelay($this->shipment, $newEta);

        $this->assertTrue($eval['is_delayed']);
        $this->assertEquals(4.0, $eval['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_LOW, $eval['delay_severity']);
        $this->assertStringContainsString('4.00 hours', $eval['delay_reason']);
    }

    public function test_medium_severity_delay_calculation(): void
    {
        // Expected: 12:00:00, Estimated: next day 02:00:00 (+14 hours)
        $newEta = Carbon::parse('2026-11-11 02:00:00');
        $eval = $this->etaService->evaluateDelay($this->shipment, $newEta);

        $this->assertTrue($eval['is_delayed']);
        $this->assertEquals(14.0, $eval['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_MEDIUM, $eval['delay_severity']);
    }

    public function test_high_severity_delay_calculation(): void
    {
        // Expected: 12:00:00, Estimated: 2026-11-12 18:00:00 (+54 hours)
        $newEta = Carbon::parse('2026-11-12 18:00:00');
        $eval = $this->etaService->evaluateDelay($this->shipment, $newEta);

        $this->assertTrue($eval['is_delayed']);
        $this->assertEquals(54.0, $eval['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_HIGH, $eval['delay_severity']);
    }

    public function test_configurable_threshold_updates_severity(): void
    {
        // Change threshold: Low <= 12, Medium <= 18 (e.g. Group B scenario)
        SystemSetting::set('delay_threshold_low', 12.0);
        SystemSetting::set('delay_threshold_medium', 18.0);

        // 10 hour delay should now be classified as LOW under the new 12h threshold
        $newEta = Carbon::parse('2026-11-10 22:00:00');
        $eval = $this->etaService->evaluateDelay($this->shipment, $newEta);

        $this->assertEquals(10.0, $eval['delay_hours']);
        $this->assertEquals(Shipment::SEVERITY_LOW, $eval['delay_severity']);
    }
}
