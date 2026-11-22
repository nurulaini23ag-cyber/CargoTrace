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
            'destination_id' => ['sometimes', 'exists:locations,id', 'different:origin_id'],
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
            'expected_arrival' => ['sometimes', 'date', 'after_or_equal:expected_departure'],
            'estimated_arrival' => ['sometimes', 'nullable', 'date'],
            'actual_departure' => ['sometimes', 'nullable', 'date'],
            'actual_arrival' => ['sometimes', 'nullable', 'date'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.product_category' => ['nullable', 'string', 'max:50'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.variety' => ['nullable', 'string', 'max:100'],
            'items.*.grade' => ['nullable', 'string', 'max:50'],
            'items.*.item_description' => ['nullable', 'string', 'max:255'],
            'items.*.hs_code' => ['nullable', 'string', 'max:20'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.package_type' => ['nullable', 'string', 'max:50'],
            'items.*.package_count' => ['nullable', 'integer', 'min:1'],
            'items.*.net_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
            'items.*.volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'items.*.moisture_content' => ['nullable', 'numeric', 'min:0', 'max:35'],
            'items.*.temperature_target' => ['nullable', 'numeric'],
            'items.*.is_hazardous' => ['nullable', 'boolean'],
        ];
    }
}
