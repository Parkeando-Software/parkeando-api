<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Worker;

class WorkersSeeder extends Seeder
{
    public function run(): void
    {
        // Excluir usuarios con rol 'user' y 'pro'
        $workerUsers = User::whereHas('role', function ($query) {
            $query->whereNotIn('name', ['user', 'pro']);
        })->get();

        foreach ($workerUsers as $user) {
            Worker::create([
                'user_id' => $user->id,
                // Agrega aquí más atributos si los hubiera
            ]);
        }
    }
}
