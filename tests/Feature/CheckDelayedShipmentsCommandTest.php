<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckDelayedShipmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_delay_check_command_executes_successfully(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $carrier = Carrier::create(['code' => 'CMD', 'name' => 'Cmd Carrier', 'mode' => 'SEA']);
        $origin = Location::create(['code' => 'O1', 'name' => 'Origin', 'city' => 'A', 'country' => 'ID', 'country_code' => 'ID', 'latitude' => 0, 'longitude' => 0]);
        $dest = Location::create(['code' => 'D1', 'name' => 'Dest', 'city' => 'B', 'country' => 'NL', 'country_code' => 'NL', 'latitude' => 1, 'longitude' => 1]);

        $shipment = Shipment::create([
            'shipment_number' => 'TRC-CMD-001',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'cargo_type' => 'Machinery',
            'weight' => 5000,
            'volume' => 15,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->subDay(),
            'estimated_arrival' => now()->addHours(12),
        ]);

        $this->artisan('shipments:check-delays')
            ->expectsOutputToContain('Starting automated shipment schedule and delay audit...')
            ->assertExitCode(0);

        $this->assertTrue($shipment->fresh()->is_delayed);
    }
}
