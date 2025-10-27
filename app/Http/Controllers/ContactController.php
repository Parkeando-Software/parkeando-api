<?php

namespace App\Http\Controllers;

use App\Notifications\ContactFormNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\ContactFormRequest;

class ContactController extends Controller
{
    public function submit(ContactFormRequest $request)
    {
        try {
            // Enviar la notificación al email configurado
            Notification::route('mail', config('mail.contact_email', 'info@parkeando.es'))
                ->notify(new ContactFormNotification($request->validated()));

            return response()->json([
                'message' => '¡Mensaje enviado con éxito!',
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al enviar el mensaje',
                'success' => false
            ], 500);
        }
    }
}