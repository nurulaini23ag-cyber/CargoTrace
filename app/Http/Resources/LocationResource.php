<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'city' => $this->city,
            'country' => $this->country,
            'country_code' => $this->country_code,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'type' => $this->type,
        ];
    }
}
