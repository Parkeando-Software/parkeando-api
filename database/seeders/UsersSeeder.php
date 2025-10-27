<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener los IDs de los roles
        $roleIds = Role::pluck('id', 'name');
        $adminRoleId = Role::where('name', 'admin')->value('id');
        $superadminRoleId = Role::where('name', 'superadmin')->value('id');

        // === Superadmin ===
        User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@parkeando.es',
            'password' => Hash::make('password123'),
            'phone' => $faker->phoneNumber,
            'role_id' => $superadminRoleId,
            'accept_terms' => true,
            'account_activated' => true,
        ]);

        // === Admin general ===
        User::create([
            'username' => 'admin',
            'name' => 'Admin General',
            'email' => 'admin@parkeando.es',
            'password' => Hash::make('password123'),
            'phone' => $faker->phoneNumber,
            'role_id' => $adminRoleId,
            'accept_terms' => true,
            'account_activated' => true,
        ]);

        // === Usuarios normales o pro ===
        $counter = 1;
        foreach (range(1, 48) as $i) {
            User::create([
                'username' => 'Parki' . $counter++, // 👈 autoincremental
                'name' => $faker->optional()->name, // puede ser null
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password123'),
                'phone' => $faker->phoneNumber,
                'role_id' => $faker->randomElement([
                    $roleIds['user'],
                    $roleIds['pro'],
                ]),
                'accept_terms' => true,
                'account_activated' => true,
            ]);
        }
    }
}
