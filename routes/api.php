<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// Rutas de autenticación
// Ruta para iniciar sesión
Route::post('/login', [AuthController::class, 'login']);
// Ruta para cerrar sesión
Route::post('/logout', [AuthController::class, 'logout']);
// Ruta para registrar un nuevo usuario
Route::post('/register', [AuthController::class, 'register']);
// Ruta para obtener el perfil del usuario autenticado
Route::middleware('auth:sanctum')->get('/perfil', 
[AuthController::class, 'perfil']);
// Ruta de editar perfil de usuario autenticado
Route::middleware('auth:sanctum')->put('/perfil', 
[AuthController::class, 'updateProfile']);
// Ruta para obtener el perfil del usuario autenticado
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//ruta libre para ver todos los usuarios
Route::get('/users', [AuthController::class, 'index']);
