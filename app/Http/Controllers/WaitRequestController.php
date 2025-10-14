<?php

namespace App\Http\Controllers;

use App\Models\WaitRequest;
use App\Http\Requests\StoreWaitRequest;
use App\Http\Requests\UpdateWaitRequest;
use Illuminate\Support\Facades\DB;

class WaitRequestController extends Controller
{
    /**
     * Mostrar solicitudes del usuario autenticado.
     */
    public function index()
    {
        return WaitRequest::where('user_id', auth()->id())->get();
    }

    /**
     * Crear una nueva solicitud de espera.
     */
    public function store(StoreWaitRequest $request)
    {
        $waitRequest = WaitRequest::create([
            'notification_id' => $request->notification_id,
            'user_id'         => auth()->id(),
            'status'          => 'pending',
        ]);

        return response()->json([
            'message' => 'Solicitud creada correctamente.',
            'data'    => $waitRequest,
        ], 201);
    }

    /**
     * Actualizar estado de la solicitud (ej. aceptar/rechazar).
     */
    public function update(UpdateWaitRequest $request, WaitRequest $waitRequest)
    {
        // Validación: solo el dueño de la notificación puede aceptar/rechazar
        if ($waitRequest->notification->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'No tienes permiso para actualizar esta solicitud.',
            ], 403);
        }

        DB::transaction(function () use ($waitRequest, $request) {
            if ($request->status === WaitRequest::STATUS_ACCEPTED) {
                // Marcar esta como aceptada
                $waitRequest->update(['status' => WaitRequest::STATUS_ACCEPTED]);

                // Rechazar todas las demás de la misma notificación
                WaitRequest::where('notification_id', $waitRequest->notification_id)
                    ->where('id', '!=', $waitRequest->id)
                    ->update(['status' => WaitRequest::STATUS_REJECTED]);

                // También podríamos actualizar el status de la notificación a "assigned"
                $waitRequest->notification->update(['status' => 'assigned']);
            } else {
                // Solo actualizar con el estado recibido (rejected por ej.)
                $waitRequest->update(['status' => $request->status]);
            }
        });

        return response()->json([
            'message' => 'Estado de la solicitud actualizado.',
            'data'    => $waitRequest->fresh(),
        ]);
    }
}
