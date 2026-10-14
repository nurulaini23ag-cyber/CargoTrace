<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\SystemSetting;
use Carbon\Carbon;

class EtaService
{
    /**
     * Calculate and apply delay metrics on a shipment.
     *
     * @param Shipment $shipment
     * @param Carbon|null $newEstimatedArrival
     * @param string|null $customReason
     * @return array Calculated delay metrics
     */
    public function evaluateDelay(Shipment $shipment, ?Carbon $newEstimatedArrival = null, ?string $customReason = null): array
    {
        $expectedArrival = Carbon::parse($shipment->expected_arrival);
        $estimatedArrival = $newEstimatedArrival ? Carbon::parse($newEstimatedArrival) : ($shipment->estimated_arrival ? Carbon::parse($shipment->estimated_arrival) : $expectedArrival);

        // Fetch configurable thresholds from SystemSettings or defaults
        $thresholdLow = (float) SystemSetting::get('delay_threshold_low', 6.0);
        $thresholdMedium = (float) SystemSetting::get('delay_threshold_medium', 24.0);

        if ($estimatedArrival->greaterThan($expectedArrival)) {
            // Difference in fractional hours
            $diffInMinutes = $expectedArrival->diffInMinutes($estimatedArrival, false);
            $delayHours = round(abs($diffInMinutes) / 60, 2);
            $isDelayed = true;

            // Determine severity
            if ($delayHours <= $thresholdLow) {
                $severity = Shipment::SEVERITY_LOW;
            } elseif ($delayHours <= $thresholdMedium) {
                $severity = Shipment::SEVERITY_MEDIUM;
            } else {
                $severity = Shipment::SEVERITY_HIGH;
            }

            $reason = $customReason ?? sprintf(
                'ETA (%s) exceeds scheduled arrival (%s) by %.2f hours. Thresholds: Low <= %gh, Medium <= %gh, High > %gh.',
                $estimatedArrival->format('Y-m-d H:i'),
                $expectedArrival->format('Y-m-d H:i'),
                $delayHours,
                $thresholdLow,
                $thresholdMedium,
                $thresholdMedium
            );
        } else {
            $isDelayed = false;
            $delayHours = 0.0;
            $severity = Shipment::SEVERITY_NONE;
            $reason = 'Shipment is on schedule. ETA is on or before expected arrival.';
        }

        return [
            'estimated_arrival' => $estimatedArrival,
            'is_delayed' => $isDelayed,
            'delay_hours' => $delayHours,
            'delay_severity' => $severity,
            'delay_reason' => $reason,
        ];
    }

    /**
     * Update shipment ETA and delay fields and notify if newly delayed.
     */
    public function applyEtaUpdate(Shipment $shipment, Carbon $newEstimatedArrival, ?string $reason = null): Shipment
    {
        $wasDelayed = $shipment->is_delayed;
        $metrics = $this->evaluateDelay($shipment, $newEstimatedArrival, $reason);

        $shipment->update([
            'estimated_arrival' => $metrics['estimated_arrival'],
            'is_delayed' => $metrics['is_delayed'],
            'delay_hours' => $metrics['delay_hours'],
            'delay_severity' => $metrics['delay_severity'],
            'delay_reason' => $metrics['delay_reason'],
        ]);

        // Send delay alert notification to customer if newly delayed
        if ($metrics['is_delayed'] && !$wasDelayed && $shipment->customer_id) {
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'type' => 'DELAY_ALERT',
                'title' => "Shipment {$shipment->shipment_number} Delay Alert ({$metrics['delay_severity']})",
                'message' => "Your shipment has encountered a delay of {$metrics['delay_hours']} hours. {$metrics['delay_reason']}",
            ]);
        }

        return $shipment;
    }
}
