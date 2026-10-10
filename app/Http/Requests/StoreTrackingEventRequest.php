<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'event_time' => ['nullable', 'date'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:1000'],
            'is_exception_event' => ['nullable', 'boolean'],
        ];
    }
}
