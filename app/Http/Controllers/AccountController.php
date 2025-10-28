<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ActivateAccountNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Si no acepta términos, no se crea el usuario
        if (empty($validated['accept_terms']) || ! $validated['accept_terms']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debe aceptar los términos y condiciones para registrarse.',
            ], 422);
        }

        $token = Str::random(60);

        // Crear usuario base
        $user = User::create([
            'role_id'           => 1,
            'username'          => $validated['username'],       // ← añadido (obligatorio y único)
            'name'              => $validated['name'] ?? null,   // ← ahora nullable
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'phone'             => $validated['phone'] ?? null,
            'accept_terms'      => true,
            'account_activated' => false,
            'activation_token'  => $token,
        ]);

        // Crear cliente asociado
        Customer::create([
            'user_id'    => $user->id,
            'points'     => 0,
            'reputation' => 5.0,
            'city'       => $validated['city'] ?? null,
        ]);

        // Enviar email de activación
        $user->notify(new ActivateAccountNotification($token));

        // Enviar solo confirmación (sin token ni datos)
        return response()->json([
            'status'  => 'success',
            'message' => 'Registro completado. Revise su correo para activar la cuenta.',
        ], 201);
    }

    public function activateAccount(string $token)
    {
        $user = User::where('activation_token', $token)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El enlace de activación no es válido o ya ha sido utilizado.',
            ], 400);
        }

        $user->update([
            'account_activated' => true,
            'activation_token'  => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tu cuenta ha sido activada correctamente. Ya puedes iniciar sesión.',
        ]);
    }

    /**
     * Login del usuario (de momento mantenemos el login solo con email, en un futuro cuando haya trabajadores se incluye
     * login con usuario)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        // Bloquea acceso si la cuenta no está activada
        if (! $user->account_activated) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Esta cuenta aún no está activada. Revise su correo electrónico.',
            ], 403);
        }

        // Bloquea acceso si los términos no fueron aceptados
        if (! $user->accept_terms) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Debe aceptar los términos y condiciones para iniciar sesión.',
            ], 403);
        }

        // Revoca tokens previos y genera uno nuevo
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user'         => new UserResource($user->load(['customer', 'role'])),
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

    /**
     * Envía un email con el enlace para restablecer la contraseña
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No existe una cuenta registrada con este email.',
            ], 404);
        }

        // Generar token de reset
        $token = Password::getRepository()->create($user);

        // Enviar notificación por email
        $user->notify(new ResetPasswordNotification($token));

        return response()->json([
            'status'  => 'success',
            'message' => 'Se ha enviado un enlace de restablecimiento a tu email.',
        ], 200);
    }

    /**
     * Restablece la contraseña del usuario usando el token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verificar que el token sea válido
        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No existe una cuenta registrada con este email.',
            ], 404);
        }

        // Verificar el token usando el sistema de Laravel
        $status = Password::getRepository()->exists($user, $validated['token']);

        if (! $status) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El token de restablecimiento no es válido o ha expirado.',
            ], 400);
        }

        // Actualizar la contraseña
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Eliminar el token usado
        Password::getRepository()->delete($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tu contraseña ha sido restablecida exitosamente.',
        ], 200);
    }
}

