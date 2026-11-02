<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExceptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_id' => $this->shipment_id,
            'exception_type' => $this->exception_type,
            'severity' => $this->severity,
            'status' => $this->status,
            'detected_at' => $this->detected_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'impact_hours' => (int) $this->impact_hours,
            'description' => $this->description,
            'resolution_notes' => $this->resolution_notes,
            'logged_by' => $this->whenLoaded('logger', fn () => $this->logger->name),
            'resolved_by' => $this->whenLoaded('resolver', fn () => $this->resolver?->name),
        ];
    }
}
