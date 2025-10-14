<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicle;
use Faker\Factory as Faker;

class VehiclesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $brands = ['Seat', 'Renault', 'Peugeot', 'Citroen', 'Volkswagen', 'Toyota', 'Ford', 'Opel'];

        foreach (User::all() as $user) {
            $numVehicles = rand(1, 2);

            for ($i = 0; $i < $numVehicles; $i++) {
                Vehicle::create([
                    'user_id' => $user->id,
                    'plate' => strtoupper($faker->bothify('####???')), // Ej: 1234ABC
                    'brand' => $faker->randomElement($brands),
                    'model' => $faker->word(),
                    'color' => $faker->safeColorName(),
                    'is_default' => $i === 0,
                ]);
            }
        }
    }
}
