<?php

namespace App\Http\Controllers;

use App\Models\Recetas;
use App\Models\Medicamentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RecetasController extends Controller
{
    // Listar todas las recetas
    public function index()
    {
        try {
            $recetas = Recetas::with(['consulta', 'medicamento'])->get();
            return response()->json($recetas);
        } catch (\Exception $e) {
            Log::error('Error al listar recetas: ' . $e->getMessage());
            return response()->json(['message' => 'Error al listar recetas'], 500);
        }
    }

    // Crear una nueva receta
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'consulta_id' => 'nullable|exists:consultas,id_consulta',
                'medicamento_id' => 'nullable|exists:medicamentos,id_medicamento',
                'cantidad' => 'required|integer|min:1',
                'dosis_prescrita' => 'nullable|string|max:100',
                'duracion' => 'nullable|string|max:100',
            ]);

            $medicamento = Medicamentos::find($validatedData['medicamento_id']);
            
            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento no encontrado'], 404);
            }

            // Validar que la cantidad no exceda el stock disponible
            if ($validatedData['cantidad'] > $medicamento->cantidad) {
                return response()->json(['message' => 'La cantidad de medicamentos supera al stock actual, elija una cantidad adecuada'], 422);
            }

            // Restar la cantidad solicitada del stock del medicamento
            $medicamento->cantidad -= $validatedData['cantidad'];
            $medicamento->save();

            $receta = Recetas::create($validatedData);

            return response()->json(['message' => 'Receta creada exitosamente', 'receta' => $receta], 201);
        } catch (ValidationException $e) {
            Log::error('Error de validación al crear receta: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear receta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear receta'], 500);
        }
    }

    // Mostrar una receta específica
    public function show($id)
    {
        try {
            $receta = Recetas::with(['consulta', 'medicamento'])->find($id);

            if (!$receta) {
                return response()->json(['message' => 'Receta no encontrada'], 404);
            }

            return response()->json($receta);
        } catch (\Exception $e) {
            Log::error('Error al mostrar receta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al mostrar receta'], 500);
        }
    }

    // Actualizar una receta específica
    public function update(Request $request, $id)
    {
        try {
            $receta = Recetas::find($id);

            if (!$receta) {
                return response()->json(['message' => 'Receta no encontrada'], 404);
            }

            $validatedData = $request->validate([
                'consulta_id' => 'nullable|exists:consultas,id_consulta',
                'medicamento_id' => 'nullable|exists:medicamentos,id_medicamento',
                'cantidad' => 'required|integer|min:1',
                'dosis_prescrita' => 'nullable|string|max:100',
                'duracion' => 'nullable|string|max:100',
            ]);

            $medicamento = Medicamentos::find($validatedData['medicamento_id']);

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento no encontrado'], 404);
            }

            // Ajustar el stock según la nueva cantidad en la receta
            $diferenciaCantidad = $validatedData['cantidad'] - $receta->cantidad;
            if ($diferenciaCantidad > $medicamento->cantidad) {
                return response()->json(['message' => 'La cantidad de medicamentos supera al stock actual, elija una cantidad adecuada'], 422);
            }

            // Actualizar el stock del medicamento
            $medicamento->cantidad -= $diferenciaCantidad;
            $medicamento->save();

            // Actualizar la receta con los nuevos datos
            $receta->update($validatedData);

            return response()->json(['message' => 'Receta actualizada exitosamente', 'receta' => $receta]);
        } catch (ValidationException $e) {
            Log::error('Error de validación al actualizar receta: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar receta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar receta'], 500);
        }
    }

    // Eliminar una receta específica
    public function destroy($id)
    {
        try {
            $receta = Recetas::find($id);

            if (!$receta) {
                return response()->json(['message' => 'Receta no encontrada'], 404);
            }

            $medicamento = Medicamentos::find($receta->medicamento_id);

            if ($medicamento) {
                // Devolver la cantidad al stock del medicamento
                $medicamento->cantidad += $receta->cantidad;
                $medicamento->save();
            }

            $receta->delete();

            return response()->json(['message' => 'Receta eliminada exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar receta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al eliminar receta'], 500);
        }
    }
}
