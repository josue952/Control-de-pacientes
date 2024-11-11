<?php

namespace App\Http\Controllers;

use App\Models\Medicamentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MedicamentosController extends Controller
{
    // Listar todos los medicamentos
    public function index()
    {
        try {
            $medicamentos = Medicamentos::all();
            return response()->json($medicamentos);
        } catch (\Exception $e) {
            Log::error('Error al listar medicamentos: ' . $e->getMessage());
            return response()->json(['message' => 'Error al listar medicamentos'], 500);
        }
    }

    // Crear un nuevo medicamento
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:100|unique:medicamentos',
                'descripcion' => 'nullable|string',
                'cantidad' => 'nullable|integer',
                'dosis' => 'nullable|string|max:100',
            ]);

            $medicamento = Medicamentos::create($validatedData);

            return response()->json(['message' => 'Medicamento creado exitosamente', 'medicamento' => $medicamento], 201);
        } catch (ValidationException $e) {
            Log::error('Error de validación al crear medicamento: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear medicamento: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear medicamento'], 500);
        }
    }

    // Mostrar un medicamento específico
    public function show($id)
    {
        try {
            $medicamento = Medicamentos::find($id);

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento no encontrado'], 404);
            }

            return response()->json($medicamento);
        } catch (\Exception $e) {
            Log::error('Error al mostrar medicamento: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al mostrar medicamento'], 500);
        }
    }

    // Actualizar un medicamento específico
    public function update(Request $request, $id)
    {
        try {
            $medicamento = Medicamentos::find($id);

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento no encontrado'], 404);
            }

            $validatedData = $request->validate([
                'nombre' => 'required|string|max:100|unique:medicamentos,nombre,' . $id . ',id_medicamento',
                'descripcion' => 'nullable|string',
                'cantidad' => 'nullable|integer',
                'dosis' => 'nullable|string|max:100',
            ]);

            $medicamento->update($validatedData);

            return response()->json(['message' => 'Medicamento actualizado exitosamente', 'medicamento' => $medicamento]);
        } catch (ValidationException $e) {
            Log::error('Error de validación al actualizar medicamento: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar medicamento: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar medicamento'], 500);
        }
    }

    // Eliminar un medicamento específico
    public function destroy($id)
    {
        try {
            $medicamento = Medicamentos::find($id);

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento no encontrado'], 404);
            }

            $medicamento->delete();

            return response()->json(['message' => 'Medicamento eliminado exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar medicamento: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al eliminar medicamento'], 500);
        }
    }
}
