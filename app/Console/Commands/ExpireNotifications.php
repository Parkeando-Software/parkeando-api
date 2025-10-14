<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireNotifications extends Command
{
    protected $signature = 'notifications:expire';
    protected $description = 'Expira automáticamente las notificaciones cuyo tiempo ya venció';

    public function handle()
    {
        $now = Carbon::now();

        // Minutos de gracia (configurables, 5 por defecto)
        $activeGrace   = config('parking.active_grace_minutes', 5);
        $assignedGrace = config('parking.assigned_grace_minutes', 5);

        // Expirar las ACTIVAS cuyo tiempo anunciado + gracia ya pasó
        $expiredActive = Notification::where('status', 'active')
            ->whereRaw(
                "(created_at + (in_minutes || ' minutes')::interval + INTERVAL '{$activeGrace} minutes') <= ?",
                [$now]
            )
            ->update(['status' => 'expired']);

        // Expirar las ASIGNADAS cuyo tiempo anunciado + gracia ya pasó
        $expiredAssigned = Notification::where('status', 'assigned')
            ->whereRaw(
                "(created_at + (in_minutes || ' minutes')::interval + INTERVAL '{$assignedGrace} minutes') <= ?",
                [$now]
            )
            ->update(['status' => 'expired']);

        $this->info("Se expiraron {$expiredActive} activas y {$expiredAssigned} asignadas.");
    }
}

