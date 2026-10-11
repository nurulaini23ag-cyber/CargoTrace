<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array
    {
        return [
            'carrier_id' => ['sometimes', 'exists:carriers,id'],
            'origin_id' => ['sometimes', 'exists:locations,id'],
            'destination_id' => ['sometimes', 'exists:locations,id'],
            'cargo_type' => ['sometimes', 'string', 'max:100'],
            'weight' => ['sometimes', 'numeric', 'min:0.01'],
            'volume' => ['sometimes', 'numeric', 'min:0.01'],
            'current_status' => [
                'sometimes',
                Rule::in([
                    Shipment::STATUS_CREATED,
                    Shipment::STATUS_BOOKED,
                    Shipment::STATUS_PICKED_UP,
                    Shipment::STATUS_IN_TRANSIT,
                    Shipment::STATUS_DELIVERED,
                    Shipment::STATUS_CANCELLED,
                ]),
            ],
            'expected_departure' => ['sometimes', 'date'],
            'expected_arrival' => ['sometimes', 'date'],
            'estimated_arrival' => ['sometimes', 'nullable', 'date'],
            'actual_departure' => ['sometimes', 'nullable', 'date'],
            'actual_arrival' => ['sometimes', 'nullable', 'date'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
