<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WaitRequestController;
use App\Http\Controllers\ParkingHistoryController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\InboxController;


/* RUTAS */

// Social Login
Route::post('/auth/google',  [SocialAuthController::class, 'google']);
Route::post('/auth/facebook',[SocialAuthController::class, 'facebook']);

// Registro de usuarios (customers)
Route::post('/register', [AuthController::class, 'register']);

// Activación cuenta
Route::get('/activate/{token}', [AuthController::class, 'activateAccount'])->name('activate.account');

// Login de usuarios ya registrados
Route::post('/login', [AuthController::class, 'login']);

// Restablecimiento de contraseña
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Logout del usuario
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Obtener usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('customer');
});

// Update de usuario (datos de perfil)
Route::middleware('auth:sanctum')->patch('/profile', [UserController::class, 'update']);

//Mostrar, crear, actualizar y borrar vehiculos de un usuario

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('vehicles', VehicleController::class)->except(['show']);
});

//Todo lo relacionado con notificaciones de plazas

Route::middleware(['auth:sanctum'])->group(function () {
    // Notificaciones (plazas libres)
    Route::apiResource('notifications', NotificationController::class)
        ->only(['index', 'store', 'update','show'])
        ->names([
            'index'  => 'notifications.index',
            'store'  => 'notifications.store',
            'update' => 'notifications.update',
            'show'   => 'notifications.show',
        ]);

    // Búsqueda de plazas cercanas (máx 500 m)
    // POST con JSON { lat, lng } validado por SearchNearbyRequest
    Route::post('notifications/nearby', [NotificationController::class, 'searchNearby'])
        ->name('notifications.nearby')
        ->middleware('throttle:60,1'); // opcional

    // Solicitudes de espera
    Route::apiResource('wait-requests', WaitRequestController::class)
        ->only(['index', 'store', 'update'])
        ->names([
            'index'  => 'wait-requests.index',
            'store'  => 'wait-requests.store',
            'update' => 'wait-requests.update',
        ]);

    // Historial
    Route::apiResource('parking-history', ParkingHistoryController::class)
        ->only(['index', 'store'])
        ->names([
            'index' => 'parking-history.index',
            'store' => 'parking-history.store',
        ]);
});


//Asignacion de puntos al usuario que deja plaza libre si la plaza es ocupada

Route::middleware('auth:sanctum')->post('/notifications/{id}/confirm', [NotificationController::class, 'confirm']);

//Toast asignar puntos asignacion plaza
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me/inbox', [InboxController::class, 'index']);
    Route::post('/me/inbox/read', [InboxController::class, 'markRead']);
});