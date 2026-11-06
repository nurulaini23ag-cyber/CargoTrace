<?php

namespace Tests\Unit;

use App\Models\Shipment;
use App\Models\SystemSetting;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_variance_returns_not_delayed(): void
    {
        $etaService = app(EtaService::class);
        $shipment = new Shipment([
            'expected_arrival' => Carbon::parse('2026-11-20 12:00:00'),
            'estimated_arrival' => Carbon::parse('2026-11-20 12:00:00'),
        ]);

        $result = $etaService->evaluateDelay($shipment);
        $this->assertFalse($result['is_delayed']);
        $this->assertEquals(0, $result['delay_hours']);
        $this->assertEquals('NONE', $result['delay_severity']);
    }

    public function test_delay_threshold_classification(): void
    {
        SystemSetting::set('delay_threshold_low', 6.0);
        SystemSetting::set('delay_threshold_medium', 24.0);

        $etaService = app(EtaService::class);
        $shipment = new Shipment([
            'expected_arrival' => Carbon::parse('2026-11-20 12:00:00'),
        ]);

        // 4 hours: LOW
        $evalLow = $etaService->evaluateDelay($shipment, Carbon::parse('2026-11-20 16:00:00'));
        $this->assertEquals('LOW', $evalLow['delay_severity']);

        // 12 hours: MEDIUM
        $evalMed = $etaService->evaluateDelay($shipment, Carbon::parse('2026-11-21 00:00:00'));
        $this->assertEquals('MEDIUM', $evalMed['delay_severity']);

        // 30 hours: HIGH
        $evalHigh = $etaService->evaluateDelay($shipment, Carbon::parse('2026-11-21 18:00:00'));
        $this->assertEquals('HIGH', $evalHigh['delay_severity']);
    }
}
