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
     * Normaliza el username (minúsculas y trim) antes de validar.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge([
                'username' => strtolower(trim((string) $this->input('username'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // ahora requerido y único en users
            'username' => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9._-]+$/',     // minúsculas, números, punto, guion, guion_bajo
                Rule::unique('users', 'username'),
            ],

            // ahora nullable
            'name'         => 'nullable|string|max:255',

            'email'        => 'required|email|unique:users,email',
            'password'     => [
                'required',
                'string',
                'min:8',
                'max:16',
                // tu regex original: mayúscula + símbolo (si quieres exigir dígito, añade (?=.*\d))
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{8,16}$/',

            ],
            'phone'        => 'nullable|string|max:20',

            // Si vas a crear también el Customer justo después, puedes aceptar city aquí:
            'city'         => 'nullable|string|max:255',

            'accept_terms' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique'   => 'Este nombre de usuario ya está en uso.',
            'username.regex'    => 'El nombre de usuario solo puede contener letras minúsculas, números, puntos, guiones y guiones bajos.',
            'username.min'      => 'El nombre de usuario debe tener al menos :min caracteres.',
            'username.max'      => 'El nombre de usuario no puede superar los :max caracteres.',
            'accept_terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ];
    }
}
