<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\DoctoresController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\ConsultasController;
use App\Http\Controllers\RecetasController;
use App\Http\Controllers\ExamenesController;
use App\Http\Controllers\MedicamentosController;
use App\Http\Controllers\ReportController;
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

    // Rutas para Exámenes
    Route::prefix('examenes')->group(function () {
        Route::get('/', [ExamenesController::class, 'index']); // Listar todos los exámenes
        Route::post('/', [ExamenesController::class, 'store']); // Crear un nuevo examen
        Route::get('/{id}', [ExamenesController::class, 'show']); // Mostrar un examen específico por ID
        Route::put('/{id}', [ExamenesController::class, 'update']); // Actualizar un examen específico por ID
        Route::delete('/{id}', [ExamenesController::class, 'destroy']); // Eliminar un examen específico por ID
    });

    // Rutas para Medicamentos
    Route::prefix('medicamentos')->group(function () {
        Route::get('/', [MedicamentosController::class, 'index']); // Listar todos los medicamentos
        Route::post('/', [MedicamentosController::class, 'store']); // Crear un nuevo medicamento
        Route::get('/{id}', [MedicamentosController::class, 'show']); // Mostrar un medicamento específico por ID
        Route::put('/{id}', [MedicamentosController::class, 'update']); // Actualizar un medicamento específico por ID
        Route::delete('/{id}', [MedicamentosController::class, 'destroy']); // Eliminar un medicamento específico por ID
    });

    //Ruta para generar reportes

    //Reportes para pacientes
    Route::get('/reportes/expediente/{paciente_id}', [ReportController::class, 'generarExpedientePaciente']);
    // Ruta para generar el reporte de recetas de un paciente por rango de fechas
    Route::get('/reportes/recetas/{paciente_id}', [ReportController::class, 'generarRecetasPaciente']);
    // Ruta para generar el reporte de exámenes de un paciente por rango de fechas
    Route::get('reportes/examenes/{paciente_id}', [ReportController::class, 'generarExamenesPaciente']);
    // Ruta para generar el reporte de consultas de un doctor
    Route::get('reportes/consultas/doctor/{doctor_id}', [ReportController::class, 'generarConsultasPorDoctor']);    
});
