<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ya no se normaliza el username.
     * Se conserva el formato original (por ejemplo "Billy").
     */
    protected function prepareForValidation(): void
    {
        // Solo trim para eliminar espacios accidentales
        if ($this->has('username')) {
            $this->merge([
                'username' => trim((string) $this->input('username')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:4',
                'max:30',
                // ahora permite mayúsculas también
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username'),
            ],

            'name' => 'nullable|string|max:255',

            'email' => 'required|email|unique:users,email',

            'password' => [
                'required',
                'string',
                'min:8',
                'max:16',
                // debe incluir al menos una mayúscula, un número y un símbolo
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{8,16}$/',
            ],

            'phone' => 'nullable|string|max:20',

            'city'  => 'nullable|string|max:255',

            'accept_terms' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique'   => 'Este nombre de usuario ya está en uso.',
            'username.regex'    => 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'username.min'      => 'El nombre de usuario debe tener al menos :min caracteres.',
            'username.max'      => 'El nombre de usuario no puede superar los :max caracteres.',

            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'email.unique'      => 'Este correo electrónico ya está registrado.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos :min caracteres.',
            'password.max'      => 'La contraseña no puede superar los :max caracteres.',
            'password.regex'    => 'La contraseña debe incluir al menos una mayúscula, un número y un símbolo.',

            'accept_terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ];
    }
}
