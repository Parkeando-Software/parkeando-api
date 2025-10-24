<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DeleteRequest;
use App\Models\User;
use App\Notifications\DeleteAccountCompletedNotification;

class ProcessDeleteRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:process-delete-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa las solicitudes de eliminación de cuenta que han cumplido el período de espera de 30 días';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando procesamiento de solicitudes de eliminación...');

        // Buscar solicitudes confirmadas que han cumplido 30 días
        $requestsToProcess = DeleteRequest::where('status', 'processing')
            ->where('confirmed_at', '<=', now()->subDays(30))
            ->get();

        if ($requestsToProcess->isEmpty()) {
            $this->info('No hay solicitudes de eliminación pendientes de procesar.');
            return 0;
        }

        $this->info("Encontradas {$requestsToProcess->count()} solicitudes para procesar.");

        $processed = 0;
        $errors = 0;

        foreach ($requestsToProcess as $deleteRequest) {
            try {
                DB::beginTransaction();

                // Obtener el usuario
                $user = User::where('email', $deleteRequest->user_email)->first();

                if (!$user) {
                    $this->warn("Usuario no encontrado para email: {$deleteRequest->user_email}");
                    $deleteRequest->markAsCompleted();
                    DB::commit();
                    $processed++;
                    continue;
                }

                // Eliminar datos relacionados del usuario
                $this->deleteUserData($user);

                // Eliminar el usuario
                $user->delete();

                // Marcar la solicitud como completada
                $deleteRequest->markAsCompleted();

                // Log de la eliminación
                Log::info('Cuenta eliminada exitosamente', [
                    'request_id' => $deleteRequest->id,
                    'user_email' => $deleteRequest->user_email,
                    'processed_at' => $deleteRequest->processed_at,
                ]);

                DB::commit();
                $processed++;

                $this->info("✓ Cuenta eliminada: {$deleteRequest->user_email}");

            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;

                Log::error('Error al procesar eliminación de cuenta', [
                    'request_id' => $deleteRequest->id,
                    'user_email' => $deleteRequest->user_email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->error("✗ Error al eliminar cuenta {$deleteRequest->user_email}: {$e->getMessage()}");
            }
        }

        $this->info("Procesamiento completado:");
        $this->info("- Cuentas eliminadas: {$processed}");
        $this->info("- Errores: {$errors}");

        return 0;
    }

    /**
     * Eliminar todos los datos relacionados con el usuario.
     *
     * @param User $user
     * @return void
     */
    private function deleteUserData(User $user): void
    {
        // Eliminar vehículos
        $user->vehicles()->delete();

        // Eliminar notificaciones
        $user->notifications()->delete();

        // Eliminar solicitudes de espera
        $user->waitRequests()->delete();

        // Eliminar historial de aparcamiento
        $user->parkingHistory()->delete();

        // Eliminar suscripción
        if ($user->subscription) {
            $user->subscription->delete();
        }

        // Eliminar datos de cliente
        if ($user->customer) {
            $user->customer->delete();
        }

        // Eliminar datos de trabajador
        if ($user->worker) {
            $user->worker->delete();
        }

        // Eliminar cuentas sociales
        $user->socialAccounts()->delete();

        // Eliminar tokens de acceso
        $user->tokens()->delete();

        // Eliminar notificaciones de la tabla notifications
        DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->delete();
    }
}
