<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\HistorialMedicoController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\PrescripcionesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [UsuarioController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [UsuarioController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::post('/', [UsuarioController::class, 'store']);
        Route::get('/{id}', [UsuarioController::class, 'show']);
        Route::put('/{id}', [UsuarioController::class, 'update']);
        Route::delete('/{id}', [UsuarioController::class, 'destroy']);
    });

    Route::prefix('pacientes')->group(function () {
        Route::get('/', [PacientesController::class, 'index']); // Listar todos los pacientes
        Route::post('/', [PacientesController::class, 'store']); // Crear un nuevo paciente
        Route::get('/{id}', [PacientesController::class, 'show']); // Mostrar un paciente específico por ID
        Route::put('/{id}', [PacientesController::class, 'update']); // Actualizar un paciente específico por ID
        Route::delete('/{id}', [PacientesController::class, 'destroy']); // Eliminar un paciente específico por ID
    });

    Route::prefix('doctores')->group(function () {
        Route::get('/', [DoctoresController::class, 'index']); // Listar todos los doctores
        Route::post('/', [DoctoresController::class, 'store']); // Crear un nuevo doctor
        Route::get('/{id}', [DoctoresController::class, 'show']); // Mostrar un doctor específico por ID
        Route::put('/{id}', [DoctoresController::class, 'update']); // Actualizar un doctor específico por ID
        Route::delete('/{id}', [DoctoresController::class, 'destroy']); // Eliminar un doctor específico por ID
    });

    Route::prefix('citas')->group(function () {
        Route::get('/', [CitasController::class, 'index']); // Listar todas las citas
        Route::post('/', [CitasController::class, 'store']); // Crear una nueva cita
        Route::get('/{id}', [CitasController::class, 'show']); // Mostrar una cita específica por ID
        Route::put('/{id}', [CitasController::class, 'update']); // Actualizar una cita específica por ID
        Route::delete('/{id}', [CitasController::class, 'destroy']); // Eliminar una cita específica por ID
    });

    Route::prefix('historial-medico')->group(function () {
        Route::get('/', [HistorialMedicoController::class, 'index']); // Listar todos los historiales médicos
        Route::post('/', [HistorialMedicoController::class, 'store']); // Crear un nuevo historial médico
        Route::get('/{id}', [HistorialMedicoController::class, 'show']); // Mostrar un historial médico específico por ID
        Route::put('/{id}', [HistorialMedicoController::class, 'update']); // Actualizar un historial médico específico por ID
        Route::delete('/{id}', [HistorialMedicoController::class, 'destroy']); // Eliminar un historial médico específico por ID
    });

    Route::prefix('pagos')->group(function () {
        Route::get('/', [PagosController::class, 'index']); // Listar todos los pagos
        Route::post('/', [PagosController::class, 'store']); // Crear un nuevo pago
        Route::get('/{id}', [PagosController::class, 'show']); // Mostrar un pago específico por ID
        Route::put('/{id}', [PagosController::class, 'update']); // Actualizar un pago específico por ID
        Route::delete('/{id}', [PagosController::class, 'destroy']); // Eliminar un pago específico por ID
    });

    Route::prefix('prescripciones')->group(function () {
        Route::get('/', [PrescripcionesController::class, 'index']); // Listar todas las prescripciones
        Route::post('/', [PrescripcionesController::class, 'store']); // Crear una nueva prescripción
        Route::get('/{id}', [PrescripcionesController::class, 'show']); // Mostrar una prescripción específica por ID
        Route::put('/{id}', [PrescripcionesController::class, 'update']); // Actualizar una prescripción específica por ID
        Route::delete('/{id}', [PrescripcionesController::class, 'destroy']); // Eliminar una prescripción específica por ID
    });

});
