<?php

namespace App\Http\Requests;

use App\Models\ShipmentException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    ShipmentException::STATUS_OPEN,
                    ShipmentException::STATUS_INVESTIGATING,
                    ShipmentException::STATUS_RESOLVED,
                ]),
            ],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
