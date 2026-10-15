<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExceptionService
{
    protected EtaService $etaService;
    protected AuditService $auditService;

    public function __construct(EtaService $etaService, AuditService $auditService)
    {
        $this->etaService = $etaService;
        $this->auditService = $auditService;
    }

    /**
     * Log a new shipment exception, post tracking alert event, and update ETA if impact_hours > 0.
     */
    public function logException(Shipment $shipment, array $data, User $actor): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $detectedAt = isset($data['detected_at']) ? Carbon::parse($data['detected_at']) : now();
            $impactHours = (int) ($data['impact_hours'] ?? 0);

            $exception = ShipmentException::create([
                'shipment_id' => $shipment->id,
                'exception_type' => $data['exception_type'],
                'severity' => $data['severity'] ?? ShipmentException::SEVERITY_MEDIUM,
                'status' => ShipmentException::STATUS_OPEN,
                'detected_at' => $detectedAt,
                'impact_hours' => $impactHours,
                'description' => $data['description'],
                'logged_by' => $actor->id,
            ]);

            // Append tracking event flagged as exception
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => 'EXCEPTION_OCCURRED',
                'status' => $shipment->current_status,
                'event_time' => $detectedAt,
                'location_id' => $data['location_id'] ?? null,
                'location_name' => $data['location_name'] ?? 'Operational Checkpoint',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => "[EXCEPTION: {$exception->exception_type}] ({$exception->severity}) {$exception->description}",
                'is_exception_event' => true,
                'created_by' => $actor->id,
            ]);

            // If exception causes delay impact hours, adjust estimated arrival
            if ($impactHours > 0) {
                $currentEta = $shipment->estimated_arrival ? Carbon::parse($shipment->estimated_arrival) : Carbon::parse($shipment->expected_arrival);
                $newEta = $currentEta->copy()->addHours($impactHours);

                $reason = "ETA adjusted by +{$impactHours}h due to {$exception->exception_type} exception.";
                $this->etaService->applyEtaUpdate($shipment, $newEta, $reason);
            }

            // In-app notification to customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'type' => 'EXCEPTION_ALERT',
                'title' => "Shipment Exception Alert: {$exception->exception_type}",
                'message' => "Shipment #{$shipment->shipment_number} has an active exception [{$exception->exception_type} - {$exception->severity}]: {$exception->description}",
            ]);

            // Audit
            $this->auditService->log(
                'EXCEPTION_CREATED',
                'ShipmentException',
                $exception->id,
                "Exception {$exception->exception_type} logged on shipment #{$shipment->shipment_number}.",
                null,
                $exception->toArray(),
                $actor->id
            );

            return $exception;
        });
    }

    /**
     * Update exception status (e.g., INVESTIGATING, RESOLVED) with resolution notes.
     */
    public function updateStatus(ShipmentException $exception, string $newStatus, ?string $notes = null, User $actor): ShipmentException
    {
        return DB::transaction(function () use ($exception, $newStatus, $notes, $actor) {
            $oldValues = $exception->toArray();

            $updateData = [
                'status' => $newStatus,
            ];

            if ($notes) {
                $updateData['resolution_notes'] = $notes;
            }

            if ($newStatus === ShipmentException::STATUS_RESOLVED) {
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = $actor->id;
            }

            $exception->update($updateData);

            // If resolved, add a tracking event confirming resolution
            if ($newStatus === ShipmentException::STATUS_RESOLVED) {
                TrackingEvent::create([
                    'shipment_id' => $exception->shipment_id,
                    'event_type' => 'EXCEPTION_RESOLVED',
                    'status' => $exception->shipment->current_status,
                    'event_time' => now(),
                    'location_name' => 'Operational Checkpoint',
                    'description' => "[EXCEPTION RESOLVED] {$exception->exception_type} marked as resolved. Notes: " . ($notes ?? 'Resolved by operations.'),
                    'is_exception_event' => false,
                    'created_by' => $actor->id,
                ]);

                // Notification to customer
                Notification::create([
                    'user_id' => $exception->shipment->customer_id,
                    'shipment_id' => $exception->shipment_id,
                    'type' => 'EXCEPTION_ALERT',
                    'title' => "Exception Resolved: {$exception->exception_type}",
                    'message' => "The exception [{$exception->exception_type}] on shipment #{$exception->shipment->shipment_number} has been resolved.",
                ]);
            }

            // Audit
            $this->auditService->log(
                'EXCEPTION_UPDATED',
                'ShipmentException',
                $exception->id,
                "Exception #{$exception->id} status changed to {$newStatus}.",
                $oldValues,
                $exception->toArray(),
                $actor->id
            );

            return $exception;
        });
    }
}
