<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment_number,
            'current_status' => $this->current_status,
            'cargo' => [
                'type' => $this->cargo_type,
                'weight_kg' => (float) $this->weight,
                'volume_cbm' => (float) $this->volume,
            ],
            'parties' => [
                'customer' => $this->whenLoaded('customer', fn () => [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'company' => $this->customer->company_name,
                ]),
                'carrier' => new CarrierResource($this->whenLoaded('carrier')),
            ],
            'route' => [
                'origin' => new LocationResource($this->whenLoaded('origin')),
                'destination' => new LocationResource($this->whenLoaded('destination')),
            ],
            'schedule' => [
                'expected_departure' => $this->expected_departure?->toIso8601String(),
                'expected_arrival' => $this->expected_arrival?->toIso8601String(),
                'estimated_arrival' => $this->estimated_arrival?->toIso8601String(),
                'actual_departure' => $this->actual_departure?->toIso8601String(),
                'actual_arrival' => $this->actual_arrival?->toIso8601String(),
            ],
            'delay' => [
                'is_delayed' => (bool) $this->is_delayed,
                'delay_hours' => (float) $this->delay_hours,
                'severity' => $this->delay_severity,
                'reason' => $this->delay_reason,
            ],
            'tracking_events' => TrackingEventResource::collection($this->whenLoaded('trackingEvents')),
            'exceptions' => ExceptionResource::collection($this->whenLoaded('exceptions')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
