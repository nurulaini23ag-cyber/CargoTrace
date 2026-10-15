<?php

namespace App\Http\Requests;

use App\Models\ShipmentException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array
    {
        return [
            'exception_type' => [
                'required',
                'string',
                Rule::in([
                    ShipmentException::TYPE_CUSTOMS_HOLD,
                    ShipmentException::TYPE_WEATHER_DELAY,
                    ShipmentException::TYPE_PORT_CONGESTION,
                    ShipmentException::TYPE_DAMAGED,
                    ShipmentException::TYPE_MISROUTED,
                    ShipmentException::TYPE_DOCUMENTATION_ISSUE,
                ]),
            ],
            'severity' => ['nullable', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'detected_at' => ['nullable', 'date'],
            'impact_hours' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:1000'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
