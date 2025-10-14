<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ParkingHistory;

class StoreParkingHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([ParkingHistory::TYPE_RELEASED, ParkingHistory::TYPE_OCCUPIED])],
            'lat'  => ['required', 'numeric', 'between:-90,90'],
            'lng'  => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'      => 'El tipo debe ser released u occupied.',
            'lat.required' => 'La latitud es obligatoria.',
            'lng.required' => 'La longitud es obligatoria.',
        ];
    }
}
