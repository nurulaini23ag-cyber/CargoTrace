<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\EtaService;
use Illuminate\Console\Command;

class CheckDelayedShipmentsCommand extends Command
{
    protected $signature = 'shipments:check-delays';
    protected $description = 'Evaluate active in-transit shipments against expected arrivals and classify delays';

    public function handle(EtaService $etaService): int
    {
        $this->info('Starting automated shipment schedule and delay audit...');

        $activeShipments = Shipment::whereIn('current_status', [
            Shipment::STATUS_BOOKED,
            Shipment::STATUS_PICKED_UP,
            Shipment::STATUS_IN_TRANSIT,
        ])->get();

        $updatedCount = 0;

        foreach ($activeShipments as $shipment) {
            $metrics = $etaService->evaluateDelay($shipment);

            if ($metrics['is_delayed'] !== $shipment->is_delayed || $metrics['delay_severity'] !== $shipment->delay_severity) {
                $shipment->update([
                    'is_delayed' => $metrics['is_delayed'],
                    'delay_hours' => $metrics['delay_hours'],
                    'delay_severity' => $metrics['delay_severity'],
                    'delay_reason' => $metrics['delay_reason'],
                ]);
                $updatedCount++;
            }
        }

        $this->info("Audit completed. Total active reviewed: {$activeShipments->count()}, updated: {$updatedCount}.");
        return Command::SUCCESS;
    }
}
