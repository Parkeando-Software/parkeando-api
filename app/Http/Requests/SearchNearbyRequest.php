<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchNearbyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // puedes poner lógica de roles si lo necesitas
    }

    public function rules(): array
    {
        return [
            'lat'  => ['required', 'numeric', 'between:-90,90'],
            'lng'  => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => 'La latitud es obligatoria.',
            'lng.required' => 'La longitud es obligatoria.',
            'lat.between'  => 'La latitud debe estar entre -90 y 90.',
            'lng.between'  => 'La longitud debe estar entre -180 y 180.',
        ];
    }
}
