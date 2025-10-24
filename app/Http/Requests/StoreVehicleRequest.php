<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asegúrate de gestionar la lógica de autorización si es necesario
    }

   public function rules(): array
{
    $userId = $this->user()->id;
    $vehicleId = $this->route('vehicle')?->id ?? null;

    return [
        'plate' => [
            'required',
            'regex:/^(?:[0-9]{4}[A-Z]{3}|[A-Z]{1,2}-?[0-9]{1,6}-?[A-Z]{0,2})$/',//regex de matricula moderna y provinciales
            Rule::unique('vehicles', 'plate') //la matricula debe ser unica
                ->where('user_id', $userId)
                ->ignore($vehicleId), //se ignora la matricula unica en el vehiculo que se intente editar
        ],
        'brand' => 'nullable|string|max:50',
        'model' => 'nullable|string|max:50',
        'color' => 'nullable|string|max:30',
        'is_default' => 'boolean',
    ];
}

    public function messages(): array
    {
        return [
            'plate.required' => 'La matrícula es obligatoria.',
            'plate.regex' => 'La matrícula debe tener el formato 1234ABC.',
            'plate.unique' => 'Ya has registrado un vehículo con esa matrícula.',
            'is_default.boolean' => 'El campo "is_default" debe ser verdadero o falso.',
        ];
    }
}
