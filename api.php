<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/* RUTAS */

// Registro de usuarios (customers)
Route::post('/register', [AuthController::class, 'register']);

// Login de usuarios ya registrados
Route::post('/login', [AuthController::class, 'login']);

// Edición de datos para usuarios registrados
Route::middleware('auth:sanctum')->put('/profile', [UserController::class, 'update']);

// Logout del usuario
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Obtener usuario autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
