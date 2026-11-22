<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array
    {
        return [
            'shipment_number' => ['nullable', 'string', 'unique:shipments,shipment_number', 'max:50'],
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['required', 'string', 'max:100'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'volume' => ['required', 'numeric', 'min:0.01'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after_or_equal:expected_departure'],
            'estimated_arrival' => ['nullable', 'date', 'after_or_equal:expected_departure'],
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
