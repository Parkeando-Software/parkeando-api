<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            // name ahora puede ponerse a null
            'name'     => ['sometimes','nullable','string','max:255'],

            'username' => [
                'sometimes',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9._-]+$/', // mismo formato que en RegisterRequest
                Rule::unique('users', 'username')->ignore($user->id),
            ],

            'email' => ['sometimes','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'phone' => ['sometimes','nullable','string','max:20'],
            'city'  => ['sometimes','nullable','string','max:100'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            // Actualiza User solo con lo que venga
            $user->fill(array_intersect_key(
                $validated,
                array_flip(['name','username','email','phone'])
            ))->save();

            // Actualiza City si viene en la request
            if (array_key_exists('city', $validated) && $user->customer) {
                $user->customer()->update(['city' => $validated['city']]);
            }
        });

        $user->load('customer');

        return new UserResource($user); // HTTP 200 por defecto
    }
}


