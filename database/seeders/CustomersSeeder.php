<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Role;
use Faker\Factory as Faker;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener los roles válidos
        $validRoleIds = Role::whereIn('name', ['user', 'pro'])->pluck('id');

        // Seleccionar usuarios con esos roles
        $users = User::whereIn('role_id', $validRoleIds)->get();

        foreach ($users as $user) {
            Customer::create([
                'user_id' => $user->id,
                'points' => $faker->numberBetween(0, 1000),
                'reputation' => $faker->randomFloat(2, 3.0, 5.0),
                'city' => $faker->city,
            ]);
        }
    }
}
