<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Notification;

class StoreWaitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'notification_id' => [
                'required',
                'integer',
                'exists:notifications,id',
                // Evita duplicar solicitudes del mismo user para la misma notificación
                Rule::unique('wait_requests', 'notification_id')
                    ->where(fn ($q) => $q->where('user_id', auth()->id())),
            ],

            // No dejamos crear accepted/rejected desde el store; sólo pending
            'status' => ['nullable', Rule::in(['pending'])],
        ];
    }

    public function messages(): array
    {
        return [
            'notification_id.required' => 'La notificación es obligatoria.',
            'notification_id.exists'   => 'La notificación indicada no existe.',
            'notification_id.unique'   => 'Ya has enviado una solicitud para esta notificación.',
            'status.in'                => 'El estado inicial debe ser "pending".',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalizar: si no viene status, lo fijamos a pending
        $this->merge([
            'status' => $this->input('status', 'pending'),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $notification = Notification::find($this->notification_id);

            if (! $notification) {
                return; // 'exists' ya fallará con su mensaje
            }

            // La notificación debe estar activa
            if ($notification->status !== 'active') {
                $v->errors()->add('notification_id', 'La notificación no está activa.');
            }

            // El creador de la notificación no puede solicitar su propia plaza
            if ($notification->user_id === auth()->id()) {
                $v->errors()->add('notification_id', 'No puedes solicitar tu propia notificación.');
            }
        });
    }
}
