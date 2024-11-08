<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\ConsultasController; // Añadido controlador de consultas
use App\Http\Controllers\RecetasController; // Añadido controlador de recetas

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
        Route::get('/', [PacientesController::class, 'index']);
        Route::post('/', [PacientesController::class, 'store']);
        Route::get('/{id}', [PacientesController::class, 'show']);
        Route::put('/{id}', [PacientesController::class, 'update']);
        Route::delete('/{id}', [PacientesController::class, 'destroy']);
    });

    Route::prefix('doctores')->group(function () {
        Route::get('/', [DoctoresController::class, 'index']);
        Route::post('/', [DoctoresController::class, 'store']);
        Route::get('/{id}', [DoctoresController::class, 'show']);
        Route::put('/{id}', [DoctoresController::class, 'update']);
        Route::delete('/{id}', [DoctoresController::class, 'destroy']);
    });

    Route::prefix('citas')->group(function () {
        Route::get('/', [CitasController::class, 'index']);
        Route::post('/', [CitasController::class, 'store']);
        Route::get('/{id}', [CitasController::class, 'show']);
        Route::put('/{id}', [CitasController::class, 'update']);
        Route::delete('/{id}', [CitasController::class, 'destroy']);
    });

    Route::prefix('pagos')->group(function () {
        Route::get('/', [PagosController::class, 'index']);
        Route::post('/', [PagosController::class, 'store']);
        Route::get('/{id}', [PagosController::class, 'show']);
        Route::put('/{id}', [PagosController::class, 'update']);
        Route::delete('/{id}', [PagosController::class, 'destroy']);
    });

    // Rutas para Consultas
    Route::prefix('consultas')->group(function () {
        Route::get('/', [ConsultasController::class, 'index']); // Listar todas las consultas
        Route::post('/', [ConsultasController::class, 'store']); // Crear una nueva consulta
        Route::get('/{id}', [ConsultasController::class, 'show']); // Mostrar una consulta específica por ID
        Route::put('/{id}', [ConsultasController::class, 'update']); // Actualizar una consulta específica por ID
        Route::delete('/{id}', [ConsultasController::class, 'destroy']); // Eliminar una consulta específica por ID
    });

    // Rutas para Recetas
    Route::prefix('recetas')->group(function () {
        Route::get('/', [RecetasController::class, 'index']); // Listar todas las recetas
        Route::post('/', [RecetasController::class, 'store']); // Crear una nueva receta
        Route::get('/{id}', [RecetasController::class, 'show']); // Mostrar una receta específica por ID
        Route::put('/{id}', [RecetasController::class, 'update']); // Actualizar una receta específica por ID
        Route::delete('/{id}', [RecetasController::class, 'destroy']); // Eliminar una receta específica por ID
    });

    //Queda pendiente las rutas para medicamentos
});
