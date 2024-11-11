<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Examenes;
use Illuminate\Support\Facades\Log;

class ExamenesController extends Controller
{
    // Obtener todos los exámenes
    public function index()
    {
        try {
            $examenes = Examenes::all();
            return response()->json($examenes);
        } catch (\Exception $e) {
            Log::error('Error al listar exámenes: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al listar exámenes'], 500);
        }
    }

    // Crear un nuevo examen
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'consulta_id' => 'nullable|exists:consultas,id_consulta',
                'tipo_examen' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'fecha_examen' => 'required|date',
                'resultados' => 'nullable|string',
                'observaciones' => 'nullable|string',
            ]);

            // Validar que el tipo de examen no esté vacío
            if (empty($validatedData['tipo_examen'])) {
                return response()->json([
                    'message' => 'El campo tipo de examen es obligatorio.'
                ], 422);
            }

            // Crear el examen
            $examen = Examenes::create($validatedData);

            return response()->json(['message' => 'Examen creado exitosamente', 'examen' => $examen], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear examen: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear examen: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear examen'], 500);
        }
    }

    // Obtener un examen por ID
    public function show($id)
    {
        try {
            $examen = Examenes::find($id);
            if (!$examen) {
                return response()->json(['message' => 'Examen no encontrado'], 404);
            }
            return response()->json($examen);
        } catch (\Exception $e) {
            Log::error('Error al mostrar examen: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al mostrar examen'], 500);
        }
    }

    // Actualizar un examen
    public function update(Request $request, $id)
    {
        try {
            $examen = Examenes::find($id);
            if (!$examen) {
                return response()->json(['message' => 'Examen no encontrado'], 404);
            }

            $validatedData = $request->validate([
                'tipo_examen' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'fecha_examen' => 'required|date',
                'resultados' => 'nullable|string',
                'observaciones' => 'nullable|string',
            ]);

            // Verificar que la fecha de examen no sea en el futuro
            if (isset($validatedData['fecha_examen']) && now()->lt($validatedData['fecha_examen'])) {
                return response()->json([
                    'message' => 'La fecha de examen no puede ser en el futuro.'
                ], 422);
            }

            $examen->update($validatedData);

            return response()->json(['message' => 'Examen actualizado exitosamente', 'examen' => $examen]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al actualizar examen: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar examen: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar examen'], 500);
        }
    }

    // Eliminar un examen
    public function destroy($id)
    {
        try {
            $examen = Examenes::find($id);
            if (!$examen) {
                return response()->json(['message' => 'Examen no encontrado'], 404);
            }

            $examen->delete();

            return response()->json(['message' => 'Examen eliminado exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar examen: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al eliminar examen'], 500);
        }
    }
}
