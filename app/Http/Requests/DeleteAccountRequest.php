<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\DeleteRequest;

class DeleteAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                'exists:users,email',
            ],
            'reason' => [
                'required',
                'string',
                'in:' . implode(',', array_keys(DeleteRequest::REASONS)),
            ],
            'additional_info' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.exists' => 'No existe una cuenta con este email.',
            'reason.required' => 'El motivo de eliminación es obligatorio.',
            'reason.in' => 'El motivo seleccionado no es válido.',
            'additional_info.max' => 'La información adicional no puede exceder los 1000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'reason' => 'motivo',
            'additional_info' => 'información adicional',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar si ya existe una solicitud pendiente para este email
            $existingRequest = DeleteRequest::where('user_email', $this->validated()['email'])
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                $validator->errors()->add('email', 'Ya existe una solicitud de eliminación pendiente para este email.');
            }
        });
    }
}
