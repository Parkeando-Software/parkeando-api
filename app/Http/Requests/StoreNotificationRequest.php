<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // aquí podrías limitar según roles si lo necesitas
    }

    public function rules(): array
    {
        return [
            'lat'        => ['required', 'numeric', 'between:-90,90'],
            'lng'        => ['required', 'numeric', 'between:-180,180'],
            'in_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'status'     => ['in:active,assigned,occupied,expired'], 
            // normalmente será "active" al crear

            // 🔵 Campo opcional para indicar zona azul
            'blue_zone'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => 'La latitud es obligatoria.',
            'lng.required' => 'La longitud es obligatoria.',
            'lat.between'  => 'La latitud debe estar entre -90 y 90.',
            'lng.between'  => 'La longitud debe estar entre -180 y 180.',
            'in_minutes.min' => 'El tiempo mínimo debe ser de 1 minuto.',
        ];
    }

    /**
     * Permite aceptar también el campo "zona_azul" desde el frontend,
     * normalizándolo a "blue_zone" como boolean.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('zona_azul') && !$this->has('blue_zone')) {
            $this->merge([
                'blue_zone' => (bool) $this->input('zona_azul'),
            ]);
        }
    }
}
