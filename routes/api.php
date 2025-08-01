<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NutricionistaController;
use App\Http\Controllers\TurnoController;
// 
// Ruta para iniciar sesión
Route::post('/login', [AuthController::class, 'login']);
// Ruta para cerrar sesión
Route::post('/logout', [AuthController::class, 'logout']);
// Ruta para registrar un nuevo usuario
Route::post('/register', [AuthController::class, 'register']);
// Ruta para obtener el perfil del usuario autenticado
Route::middleware('auth:sanctum')->get(
    '/perfil',
    [AuthController::class, 'perfil']
);
// Ruta de editar perfil de usuario autenticado
Route::middleware('auth:sanctum')->put(
    '/perfil',
    [AuthController::class, 'updateProfile']
);
// Ruta para obtener el perfil del usuario autenticado
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
//ruta para eliminar usuario autenticado
Route::middleware('auth:sanctum')->delete('/user/{id}', [AuthController::class, 'destroy']);
//ruta libre para ver todos los usuarios
Route::get('/users', [AuthController::class, 'index']);
//Ruta para editar a un usurio desde el usuario adminitrador
Route::middleware('auth:sanctum')->put('/user/{id}', [AuthController::class, 'editAdmin']);
//ruta para los turnos reservados por el paciente autenticado
Route::middleware('auth:sanctum')->get('/turnos/mis-turnos', [AuthController::class, 'turnosReservados']);


// Rutas para Datos Personales
use App\Http\Controllers\DatosPersonalesController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/datos-personales', [DatosPersonalesController::class, 'index']);
    Route::post('/datos-personales', [DatosPersonalesController::class, 'store']);
    Route::get('/datos-personales/{id}', [DatosPersonalesController::class, 'show']);
    Route::put('/datos-personales', [DatosPersonalesController::class, 'update']);
    Route::delete('/datos-personales', [DatosPersonalesController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/turnos/masivo', [TurnoController::class, 'crearTurnosMasivos']); // Nutricionista crea turnos masivos
    Route::post('/turnos', [TurnoController::class, 'crearTurno']); // Nutricionista crea
    Route::post('/turnos/{id}/reservar', [TurnoController::class, 'reservarTurno']); // Paciente reserva
    Route::post('/turnos/{id}/cancelar', [TurnoController::class, 'cancelarTurno']); // Cancelar
    Route::post('/turnos/{id}/asignar', [TurnoController::class, 'asignarTurno']); // Nutricionista asigna
    Route::get('/paciente/turnos', [TurnoController::class, 'turnosReservadosPorPaciente']); //turnos reservados por paciente
    Route::get('/nutricionistas/turnos/reservados', [TurnoController::class, 'turnosReservadosNutricionista']); //turnos reservados que tiene el nutricionista
    Route::get('/turnos/mis-turnos', [TurnoController::class, 'misTurnos']); // Mis turnos
    Route::post('/turnos/{id}/atender', [TurnoController::class, 'atenderTurno']);//ruta para atender y que el estado pase a completado
    Route::get('/turnos/completados', [TurnoController::class, 'turnosCompletadosNutricionista']);//tunos completados por nutricionista
    Route::get('/turnos/completados/paciente', [TurnoController::class, 'turnosCompletadosPaciente']);//turnos completados por paciente
    Route::delete('/turnos/{id}', [TurnoController::class, 'eliminarTurno']); //eliminar turno
    

});

// Rutas para Nutricionista
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/nutricionistas', [NutricionistaController::class, 'indexNutricionista']); //rutas para el nutricionista
    Route::get('/nutricionistas/turnos', [NutricionistaController::class, 'turnosNutricionista']); //obtener turno por nutricionistas
    Route::get('/nutricionistas/turnos/{id}', [NutricionistaController::class, 'turnosNutricionistaEspecifico']); //obtener turno por nutricionista especifico
    Route::get('/nutricionistas/listar', [NutricionistaController::class, 'listarNutricionistas']); //listar nutricionistas
    Route::get('/nutricionistas/turnos/{id}', [NutricionistaController::class, 'turnosPorNutricionista']);
});
