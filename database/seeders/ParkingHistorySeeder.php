<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Faker\Factory as Faker;

class ParkingHistorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $users = User::query()->pluck('id');
        if ($users->isEmpty()) {
            $this->command?->warn('No hay usuarios; se omite ParkingHistorySeeder.');
            return;
        }

        foreach ($users as $userId) {
            $events = random_int(2, 5);

            for ($i = 0; $i < $events; $i++) {
                // Coordenadas aleatorias (zona Madrid)
                $lat = $faker->latitude(40.30, 40.50);
                $lng = $faker->longitude(-3.75, -3.65);

                // Fecha aleatoria en últimos 30 días
                $createdAt = now()
                    ->subDays(random_int(0, 30))
                    ->subMinutes(random_int(0, 1440));

                DB::table('parking_history')->insert([
                    'user_id'    => $userId,
                    'type'       => $faker->randomElement(['released', 'occupied']),
                    // OJO: (lng, lat) y casteo a ::geography
                    'location'   => DB::raw("ST_SetSRID(ST_MakePoint($lng, $lat), 4326)::geography"),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
