<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pagos;
use App\Models\Citas;
use Illuminate\Support\Facades\Log;

class PagosController extends Controller
{
    // Obtener todos los pagos
    public function index()
    {
        try {
            $pagos = Pagos::with('cita')->get();
            return response()->json($pagos);
        } catch (\Exception $e) {
            Log::error('Error al listar pagos: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al listar pagos'], 500);
        }
    }

    // Crear un nuevo pago
    public function store(Request $request)
    {
        try {
            // Validación inicial
            $validatedData = $request->validate([
                'cita_id' => 'required|exists:citas,id_cita',
                'monto' => 'required|numeric|min:0.01', // Verifica que el monto sea mayor a 0
                'fecha_pago' => 'required|date',
            ]);

            // Verificar si la cita ya está pagada
            $cita = Citas::find($validatedData['cita_id']);
            if ($cita->pagada == 1) {
                return response()->json(['message' => 'Esta cita ya ha sido pagada'], 400);
            }

            // Crear el pago
            $pago = Pagos::create($validatedData);

            // Actualizar el estado de la cita a "pagada"
            $cita->update(['pagada' => 1]);

            return response()->json(['message' => 'Pago creado exitosamente', 'pago' => $pago], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear pago: ' . json_encode($e->errors()));

            // Verificar si el error es específico del monto y personalizar el mensaje
            $errors = $e->errors();
            if (isset($errors['monto']) && $errors['monto'][0] === 'The monto field must be at least 0.01.') {
                return response()->json(['message' => 'El monto de la cita no puede ser $0'], 422);
            }

            // Retornar los otros errores si existen
            return response()->json(['errors' => $errors], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear pago'], 500);
        }
    }

    // Obtener un pago por ID
    public function show($id)
    {
        try {
            $pago = Pagos::with('cita')->find($id);
            if (!$pago) {
                return response()->json(['message' => 'Pago no encontrado'], 404);
            }
            return response()->json($pago);
        } catch (\Exception $e) {
            Log::error('Error al mostrar pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al mostrar pago'], 500);
        }
    }

    // Eliminar un pago
    public function destroy($id)
    {
        try {
            $pago = Pagos::find($id);
            if (!$pago) {
                return response()->json(['message' => 'Pago no encontrado'], 404);
            }

            // Obtener la cita asociada al pago
            $cita = Citas::find($pago->cita_id);
            if ($cita) {
                // Actualizar el estado de la cita a "no pagada" (pagada = 0)
                $cita->update(['pagada' => 0]);
            }

            // Eliminar el pago
            $pago->delete();

            return response()->json(['message' => 'Pago eliminado exitosamente y el estado de la cita ha sido revertido a no pagada']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al eliminar pago'], 500);
        }
    }
}
