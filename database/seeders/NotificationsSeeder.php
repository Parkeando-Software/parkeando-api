<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // IDs de usuarios para elegir asignatarios rápidamente
        $allUserIds = User::pluck('id')->all();

        // Para no romper el índice único parcial (1 pending por usuario),
        // llevamos un flag por usuario de si ya generamos una active/assigned
        foreach (User::inRandomOrder()->take(20)->get() as $user) {
            $pendingCreated = false; // active|assigned ya generado para este usuario
            $num = rand(1, 2);

            for ($i = 0; $i < $num; $i++) {
                // Coordenadas (zona Madrid aproximada)
                $lat = $faker->latitude(40.4, 40.5);
                $lng = $faker->longitude(-3.75, -3.65);

                // Elegimos estado, pero respetando el límite de "pending"
                $possibleStatuses = ['active', 'assigned', 'occupied', 'expired'];
                if ($pendingCreated) {
                    // Si ya hay un pending, evitamos active/assigned para este usuario
                    $possibleStatuses = ['occupied', 'expired'];
                }
                $status = $faker->randomElement($possibleStatuses);

                $assignedToId = null;
                $assignedAt   = null;

                // Tiempos base
                // Empezamos desde "ahora" y ajustamos según estado para coherencia
                $inMinutes = rand(1, 10);
                $now = Carbon::now();

                // Timestamps por defecto
                $createdAt = $now->copy()->subMinutes(rand(0, 30));
                $updatedAt = $now->copy();

                // Reglas por estado
                switch ($status) {
                    case 'active':
                        // Aún no expirado: created_at dentro de su ventana de validez
                        $createdAt = $now->copy()->subMinutes(rand(0, max(0, $inMinutes - 1)));
                        $pendingCreated = true;
                        break;

                    case 'assigned':
                        // Necesita asignatario distinto del liberador
                        $candidateIds = array_values(array_filter($allUserIds, fn ($id) => $id !== $user->id));
                        if (empty($candidateIds)) {
                            // Si no hay candidato, degradamos a active
                            $status = 'active';
                            $createdAt = $now->copy()->subMinutes(rand(0, max(0, $inMinutes - 1)));
                            $pendingCreated = true;
                            break;
                        }
                        $assignedToId = $candidateIds[array_rand($candidateIds)];
                        $createdAt = $now->copy()->subMinutes(rand(0, max(0, $inMinutes - 1)));
                        $assignedAt = (clone $createdAt)->addMinutes(rand(0, 2));
                        $pendingCreated = true;
                        break;

                    case 'occupied':
                        // Necesita asignatario y assigned_at antes de "ahora"
                        $candidateIds = array_values(array_filter($allUserIds, fn ($id) => $id !== $user->id));
                        if (empty($candidateIds)) {
                            // Si no hay candidatos, degradamos a expired coherente
                            $status = 'expired';
                            $createdAt = $now->copy()->subMinutes($inMinutes + rand(1, 10));
                            break;
                        }
                        $assignedToId = $candidateIds[array_rand($candidateIds)];
                        $createdAt  = $now->copy()->subMinutes(rand(5, 30));
                        $assignedAt = $now->copy()->subMinutes(rand(2, 4));
                        break;

                    case 'expired':
                    default:
                        $createdAt = $now->copy()->subMinutes($inMinutes + rand(1, 20));
                        break;
                }

                // 🔵 Nuevo campo: zona azul (true/false aleatorio)
                $blueZone = (bool)rand(0, 1);

                DB::table('notifications')->insert([
                    'user_id'             => $user->id,       // liberador
                    'assigned_to_user_id' => $assignedToId,   // ocupante (nullable)
                    'assigned_at'         => $assignedAt,     // nullable
                    'in_minutes'          => $inMinutes,
                    'blue_zone'           => $blueZone,       // 🔵 nuevo campo
                    'status'              => $status,
                    'location'            => DB::raw("ST_SetSRID(ST_MakePoint($lng, $lat), 4326)::geography"),
                    'created_at'          => $createdAt,
                    'updated_at'          => $updatedAt,
                ]);
            }
        }
    }
}
