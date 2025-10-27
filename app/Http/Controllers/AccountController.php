<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\DeleteAccountRequest;
use App\Models\DeleteRequest;
use App\Models\User;
use App\Notifications\DeleteAccountConfirmationNotification;
use App\Notifications\DeleteAccountCancellationNotification;

class AccountController extends Controller
{
    /**
     * Procesar solicitud de eliminación de cuenta.
     *
     * @param DeleteAccountRequest $request
     * @return JsonResponse
     */
    public function deleteRequest(DeleteAccountRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Generar token de confirmación único
            $confirmationToken = Str::random(64);

            // Crear la solicitud de eliminación
            $deleteRequest = DeleteRequest::create([
                'user_email' => $request->validated()['email'],
                'reason' => $request->validated()['reason'],
                'additional_info' => $request->validated()['additional_info'] ?? null,
                'confirmation_token' => $confirmationToken,
                'status' => 'pending',
            ]);

            // Obtener el usuario para enviar la notificación
            $user = User::where('email', $request->validated()['email'])->first();

            if ($user) {
                // Enviar email de confirmación
                $user->notify(new DeleteAccountConfirmationNotification($deleteRequest));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de eliminación recibida correctamente',
                'request_id' => $deleteRequest->id,
                'estimated_days' => 30,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Confirmar solicitud de eliminación de cuenta.
     *
     * @param string $token
     * @return JsonResponse
     */
    public function confirmDeleteRequest(string $token): JsonResponse
    {
        try {
            $deleteRequest = DeleteRequest::where('confirmation_token', $token)
                ->where('status', 'pending')
                ->first();

            if (!$deleteRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token de confirmación inválido o expirado',
                    'code' => 'INVALID_TOKEN',
                ], 404);
            }

            // Marcar como confirmada
            $deleteRequest->markAsConfirmed();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de eliminación confirmada correctamente',
                'request_id' => $deleteRequest->id,
                'status' => 'processing',
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Cancelar solicitud de eliminación de cuenta.
     *
     * @param string $token
     * @return JsonResponse
     */
    public function cancelDeleteRequest(string $token): JsonResponse
    {
        try {
            $deleteRequest = DeleteRequest::where('confirmation_token', $token)
                ->where('status', 'pending')
                ->first();

            if (!$deleteRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token de confirmación inválido o expirado',
                    'code' => 'INVALID_TOKEN',
                ], 404);
            }

            // Marcar como cancelada
            $deleteRequest->markAsCancelled();

            // Obtener el usuario para enviar notificación de cancelación
            $user = User::where('email', $deleteRequest->user_email)->first();

            if ($user) {
                $user->notify(new DeleteAccountCancellationNotification($deleteRequest));
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de eliminación cancelada correctamente',
                'request_id' => $deleteRequest->id,
                'status' => 'cancelled',
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Obtener estado de una solicitud de eliminación.
     *
     * @param string $token
     * @return JsonResponse
     */
    public function getDeleteRequestStatus(string $token): JsonResponse
    {
        try {
            $deleteRequest = DeleteRequest::where('confirmation_token', $token)->first();

            if (!$deleteRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token de confirmación inválido',
                    'code' => 'INVALID_TOKEN',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'request_id' => $deleteRequest->id,
                'status' => $deleteRequest->status,
                'created_at' => $deleteRequest->created_at,
                'confirmed_at' => $deleteRequest->confirmed_at,
                'cancelled_at' => $deleteRequest->cancelled_at,
                'processed_at' => $deleteRequest->processed_at,
                'can_be_cancelled' => $deleteRequest->canBeCancelled(),
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }
}
