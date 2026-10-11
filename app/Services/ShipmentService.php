<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    protected EtaService $etaService;
    protected AuditService $auditService;

    public function __construct(EtaService $etaService, AuditService $auditService)
    {
        $this->etaService = $etaService;
        $this->auditService = $auditService;
    }

    /**
     * Allowed status transitions mapped strictly by business rules.
     */
    protected array $allowedTransitions = [
        Shipment::STATUS_CREATED => [Shipment::STATUS_BOOKED, Shipment::STATUS_CANCELLED],
        Shipment::STATUS_BOOKED => [Shipment::STATUS_PICKED_UP, Shipment::STATUS_CANCELLED],
        Shipment::STATUS_PICKED_UP => [Shipment::STATUS_IN_TRANSIT, Shipment::STATUS_CANCELLED],
        Shipment::STATUS_IN_TRANSIT => [Shipment::STATUS_DELIVERED],
        Shipment::STATUS_DELIVERED => [], // Terminal: cannot revert
        Shipment::STATUS_CANCELLED => [], // Terminal: cannot revert
    ];

    /**
     * Check if a status transition is valid according to business rules.
     */
    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        return in_array($newStatus, $this->allowedTransitions[$currentStatus] ?? [], true);
    }

    /**
     * Validate status transition or throw DomainException.
     */
    public function validateTransition(string $currentStatus, string $newStatus): void
    {
        if ($currentStatus === $newStatus) {
            return;
        }

        if ($currentStatus === Shipment::STATUS_DELIVERED) {
            throw new DomainException("Terminal state violation: Shipment has already been DELIVERED and cannot transition to {$newStatus}.");
        }

        if ($currentStatus === Shipment::STATUS_CANCELLED) {
            throw new DomainException("Terminal state violation: Shipment has been CANCELLED and cannot transition to {$newStatus}.");
        }

        if (!$this->canTransition($currentStatus, $newStatus)) {
            $allowed = implode(', ', $this->allowedTransitions[$currentStatus] ?? ['None']);
            throw new DomainException("Invalid status transition from '{$currentStatus}' to '{$newStatus}'. Allowed next states: [{$allowed}].");
        }
    }

    /**
     * Generate unique shipment tracking number.
     */
    public function generateShipmentNumber(): string
    {
        $year = date('Y');
        do {
            $code = 'TRC-' . $year . '-' . strtoupper(Str::random(6));
        } while (Shipment::where('shipment_number', $code)->exists());

        return $code;
    }

    /**
     * Create a new shipment with items, initial tracking event, and audit log.
     */
    public function createShipment(array $data, ?User $actor = null): Shipment
    {
        return DB::transaction(function () use ($data, $actor) {
            $shipmentNumber = $data['shipment_number'] ?? $this->generateShipmentNumber();
            $expectedDeparture = Carbon::parse($data['expected_departure']);
            $expectedArrival = Carbon::parse($data['expected_arrival']);
            $estimatedArrival = isset($data['estimated_arrival'])
                ? Carbon::parse($data['estimated_arrival'])
                : $expectedArrival;

            $initialStatus = $data['current_status'] ?? Shipment::STATUS_CREATED;

            $shipment = new Shipment([
                'shipment_number' => $shipmentNumber,
                'customer_id' => $data['customer_id'],
                'carrier_id' => $data['carrier_id'],
                'origin_id' => $data['origin_id'],
                'destination_id' => $data['destination_id'],
                'cargo_type' => $data['cargo_type'],
                'weight' => $data['weight'],
                'volume' => $data['volume'],
                'current_status' => $initialStatus,
                'expected_departure' => $expectedDeparture,
                'expected_arrival' => $expectedArrival,
                'estimated_arrival' => $estimatedArrival,
                'actual_departure' => isset($data['actual_departure']) ? Carbon::parse($data['actual_departure']) : null,
                'actual_arrival' => isset($data['actual_arrival']) ? Carbon::parse($data['actual_arrival']) : null,
                'special_instructions' => $data['special_instructions'] ?? null,
            ]);

            // Evaluate delay
            $delayMetrics = $this->etaService->evaluateDelay($shipment, $estimatedArrival);
            $shipment->is_delayed = $delayMetrics['is_delayed'];
            $shipment->delay_hours = $delayMetrics['delay_hours'];
            $shipment->delay_severity = $delayMetrics['delay_severity'];
            $shipment->delay_reason = $delayMetrics['delay_reason'];
            $shipment->save();

            // Create items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $shipment->items()->create([
                        'item_description' => $itemData['item_description'],
                        'hs_code' => $itemData['hs_code'] ?? null,
                        'quantity' => $itemData['quantity'] ?? 1,
                        'package_type' => $itemData['package_type'] ?? 'BOX',
                        'weight_kg' => $itemData['weight_kg'] ?? 0,
                        'volume_cbm' => $itemData['volume_cbm'] ?? 0,
                        'is_hazardous' => !empty($itemData['is_hazardous']),
                    ]);
                }
            }

            // Create initial tracking event
            $origin = $shipment->origin;
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => 'CREATED',
                'status' => $initialStatus,
                'event_time' => now(),
                'location_id' => $shipment->origin_id,
                'location_name' => $origin ? $origin->name : 'Origin Depot',
                'latitude' => $origin ? $origin->latitude : null,
                'longitude' => $origin ? $origin->longitude : null,
                'description' => 'Shipment booking created in CargoTrace system.',
                'is_exception_event' => false,
                'created_by' => $actor ? $actor->id : $shipment->customer_id,
            ]);

            // Create notification for customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'type' => 'STATUS_CHANGE',
                'title' => "Shipment Created: {$shipment->shipment_number}",
                'message' => "Your shipment #{$shipment->shipment_number} ({$shipment->cargo_type}) has been registered and is pending booking.",
            ]);

            // Audit
            $this->auditService->log(
                'SHIPMENT_CREATED',
                'Shipment',
                $shipment->id,
                "Shipment #{$shipment->shipment_number} created with status {$initialStatus}.",
                null,
                $shipment->toArray(),
                $actor ? $actor->id : null
            );

            return $shipment->load(['items', 'origin', 'destination', 'carrier', 'customer']);
        });
    }

    /**
     * Update an existing shipment.
     */
    public function updateShipment(Shipment $shipment, array $data, ?User $actor = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $oldValues = $shipment->toArray();

            // Status transition check if status is changing
            if (isset($data['current_status']) && $data['current_status'] !== $shipment->current_status) {
                $this->validateTransition($shipment->current_status, $data['current_status']);
            }

            $shipment->fill($data);

            // Re-evaluate delay if dates changed
            if (isset($data['expected_arrival']) || isset($data['estimated_arrival'])) {
                $est = isset($data['estimated_arrival']) ? Carbon::parse($data['estimated_arrival']) : $shipment->estimated_arrival;
                $delayMetrics = $this->etaService->evaluateDelay($shipment, $est);
                $shipment->is_delayed = $delayMetrics['is_delayed'];
                $shipment->delay_hours = $delayMetrics['delay_hours'];
                $shipment->delay_severity = $delayMetrics['delay_severity'];
                $shipment->delay_reason = $delayMetrics['delay_reason'];
            }

            // Status side effects
            if ($shipment->current_status === Shipment::STATUS_DELIVERED && !$shipment->actual_arrival) {
                $shipment->actual_arrival = now();
            }
            if ($shipment->current_status === Shipment::STATUS_PICKED_UP && !$shipment->actual_departure) {
                $shipment->actual_departure = now();
            }

            $shipment->save();

            // Audit
            $this->auditService->log(
                'SHIPMENT_UPDATED',
                'Shipment',
                $shipment->id,
                "Shipment #{$shipment->shipment_number} details updated.",
                $oldValues,
                $shipment->toArray(),
                $actor ? $actor->id : null
            );

            return $shipment;
        });
    }

    /**
     * Transition shipment status explicitly.
     */
    public function transitionStatus(Shipment $shipment, string $newStatus, ?string $reason = null, ?User $actor = null): Shipment
    {
        $this->validateTransition($shipment->current_status, $newStatus);

        return DB::transaction(function () use ($shipment, $newStatus, $reason, $actor) {
            $oldStatus = $shipment->current_status;
            $shipment->current_status = $newStatus;

            if ($newStatus === Shipment::STATUS_DELIVERED && !$shipment->actual_arrival) {
                $shipment->actual_arrival = now();
            }
            if ($newStatus === Shipment::STATUS_PICKED_UP && !$shipment->actual_departure) {
                $shipment->actual_departure = now();
            }

            $shipment->save();

            // Notification to customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'type' => 'STATUS_CHANGE',
                'title' => "Shipment Status Updated: {$newStatus}",
                'message' => "Shipment #{$shipment->shipment_number} changed from {$oldStatus} to {$newStatus}." . ($reason ? " Note: {$reason}" : ""),
            ]);

            // Audit log
            $this->auditService->log(
                'STATUS_TRANSITION',
                'Shipment',
                $shipment->id,
                "Status transitioned from {$oldStatus} to {$newStatus}. " . ($reason ?? ''),
                ['current_status' => $oldStatus],
                ['current_status' => $newStatus],
                $actor ? $actor->id : null
            );

            return $shipment;
        });
    }

    /**
     * Soft delete shipment.
     */
    public function deleteShipment(Shipment $shipment, ?User $actor = null): bool
    {
        $old = $shipment->toArray();
        $deleted = $shipment->delete();

        if ($deleted) {
            $this->auditService->log(
                'SHIPMENT_DELETED',
                'Shipment',
                $shipment->id,
                "Shipment #{$shipment->shipment_number} soft-deleted.",
                $old,
                null,
                $actor ? $actor->id : null
            );
        }

        return (bool) $deleted;
    }
}
