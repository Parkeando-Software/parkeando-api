<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaitRequest;
use App\Models\User;
use App\Models\Notification;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class WaitRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Tomamos notificaciones activas
        $notifications = Notification::where('status', 'active')->get();

        foreach ($notifications as $notification) {
            // Solo algunas notificaciones tendrán solicitudes
            if (rand(0, 1)) {
                // Entre 1 y 3 usuarios distintos piden la plaza
                $requestingUsers = User::where('id', '!=', $notification->user_id)
                    ->inRandomOrder()
                    ->take(rand(1, 3))
                    ->get();

                $acceptedAssigned = false;

                foreach ($requestingUsers as $user) {
                    // Si ya hay un "accepted", todos los demás deben ser "rejected" o "pending"
                    $status = $acceptedAssigned
                        ? $faker->randomElement(['pending', 'rejected'])
                        : $faker->randomElement(['pending', 'accepted', 'rejected']);

                    // Forzar a que como máximo haya 1 aceptado
                    if ($status === 'accepted') {
                        $acceptedAssigned = true;
                    }

                    // Insertar (se respeta la restricción única notification_id+user_id)
                    DB::table('wait_requests')->insert([
                        'notification_id' => $notification->id,
                        'user_id'         => $user->id,
                        'status'          => $status,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }
    }
}
