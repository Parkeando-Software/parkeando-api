<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\WaitRequest;

class UpdateWaitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([WaitRequest::STATUS_ACCEPTED, WaitRequest::STATUS_REJECTED]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado debe ser accepted o rejected.',
        ];
    }
}
