<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Si no acepta términos, no se crea el usuario
        if (empty($validated['accept_terms']) || !$validated['accept_terms']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Debe aceptar los términos y condiciones para registrarse.'
            ], 422);
        }

        // Crear usuario base
        $user = User::create([
            'role_id'           => 1,
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'phone'             => $validated['phone'] ?? null,
            'accept_terms'      => true,
            'account_activated' => false,
        ]);

        // Crear cliente asociado
        Customer::create([
            'user_id'    => $user->id,
            'points'     => 0,
            'reputation' => 5.0,
            'city'       => $validated['city'] ?? null,
        ]);

        // Enviar solo confirmación (sin token ni datos)
        return response()->json([
            'status'  => 'success',
            'message' => 'Registro completado. Revise su correo para activar la cuenta.',
        ], 201);
    }

    /**
     * Login del usuario
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales incorrectas.'
            ], 401);
        }

        // Bloquea acceso si la cuenta no está activada
        if (!$user->account_activated) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Esta cuenta aún no está activada. Revise su correo electrónico.'
            ], 403);
        }

        // Revoca tokens previos y genera uno nuevo
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => new UserResource($user->load(['customer', 'role'])),
        ], 201);
    }

    /**
     * Logout del usuario
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout exitoso']);
        }

        return response()->json(['message' => 'Acceso denegado'], 403);
    }
}
