<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Shipment;
use App\Services\EtaService;
use Illuminate\Console\Command;

class CheckDelayedShipmentsCommand extends Command
{
    protected $signature = 'shipments:check-delays';
    protected $aliases = ['check:delayed-shipments', 'cargo:check-delays'];
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
            $wasDelayed = (bool) $shipment->is_delayed;
            $metrics = $etaService->evaluateDelay($shipment);

            if ($metrics['is_delayed'] !== $shipment->is_delayed || $metrics['delay_severity'] !== $shipment->delay_severity) {
                $shipment->update([
                    'is_delayed' => $metrics['is_delayed'],
                    'delay_hours' => $metrics['delay_hours'],
                    'delay_severity' => $metrics['delay_severity'],
                    'delay_reason' => $metrics['delay_reason'],
                ]);

                if ($metrics['is_delayed'] && !$wasDelayed && $shipment->customer_id) {
                    Notification::create([
                        'user_id' => $shipment->customer_id,
                        'shipment_id' => $shipment->id,
                        'type' => 'DELAY_ALERT',
                        'title' => "Shipment {$shipment->shipment_number} Delay Alert ({$metrics['delay_severity']})",
                        'message' => "Your shipment has encountered a schedule deviation of {$metrics['delay_hours']} hours. {$metrics['delay_reason']}",
                    ]);
                }

                $updatedCount++;
            }
        }

        $this->info("Audit completed. Total active reviewed: {$activeShipments->count()}, updated: {$updatedCount}.");
        return Command::SUCCESS;
    }
}
