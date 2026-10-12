<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    protected ShipmentService $shipmentService;
    protected AuditService $auditService;

    public function __construct(ShipmentService $shipmentService, AuditService $auditService)
    {
        $this->shipmentService = $shipmentService;
        $this->auditService = $auditService;
    }

    /**
     * Add a historical tracking event to a shipment.
     * Enforces chronological sequence validation and updates shipment status if relevant.
     */
    public function addEvent(Shipment $shipment, array $data, User $actor): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $eventTime = Carbon::parse($data['event_time'] ?? now());

            // Validate time sequence: new event cannot be earlier than previous tracking events
            $latestEvent = TrackingEvent::where('shipment_id', $shipment->id)
                ->orderBy('event_time', 'desc')
                ->first();
            if ($latestEvent && $eventTime->lessThan(Carbon::parse($latestEvent->event_time))) {
                throw new DomainException(sprintf(
                    "Chronological sequence violation: Event timestamp (%s) cannot be earlier than the most recent existing event (%s).",
                    $eventTime->toDateTimeString(),
                    Carbon::parse($latestEvent->event_time)->toDateTimeString()
                ));
            }

            // Status transition check if tracking event conveys a new shipment status
            $targetStatus = $data['status'] ?? $shipment->current_status;
            if ($targetStatus !== $shipment->current_status) {
                $this->shipmentService->transitionStatus($shipment, $targetStatus, $data['description'] ?? null, $actor);
            }

            $trackingEvent = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => $data['event_type'] ?? 'MILESTONE_UPDATE',
                'status' => $targetStatus,
                'event_time' => $eventTime,
                'location_id' => $data['location_id'] ?? null,
                'location_name' => $data['location_name'] ?? 'In Transit Waypoint',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => $data['description'],
                'is_exception_event' => !empty($data['is_exception_event']),
                'created_by' => $actor->id,
            ]);

            // Notify customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'type' => 'STATUS_CHANGE',
                'title' => "Tracking Update: {$trackingEvent->location_name}",
                'message' => "Shipment #{$shipment->shipment_number}: {$trackingEvent->description}",
            ]);

            // Audit
            $this->auditService->log(
                'TRACKING_EVENT_ADDED',
                'TrackingEvent',
                $trackingEvent->id,
                "Tracking event [{$trackingEvent->event_type}] added to shipment #{$shipment->shipment_number} at {$trackingEvent->location_name}.",
                null,
                $trackingEvent->toArray(),
                $actor->id
            );

            return $trackingEvent;
        });
    }

    /**
     * Get chronologically ordered timeline for a shipment.
     */
    public function getTimeline(Shipment $shipment): array
    {
        $events = $shipment->trackingEvents()
            ->with(['location', 'creator'])
            ->orderBy('event_time', 'asc')
            ->get();

        $latestEvent = $events->last();

        return [
            'shipment' => $shipment,
            'current_location' => $latestEvent ? [
                'name' => $latestEvent->location_name,
                'latitude' => $latestEvent->latitude,
                'longitude' => $latestEvent->longitude,
                'last_updated' => $latestEvent->event_time,
                'status' => $latestEvent->status,
            ] : null,
            'events' => $events,
        ];
    }
}
