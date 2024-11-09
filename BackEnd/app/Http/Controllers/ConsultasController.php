<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultas;
use App\Models\Citas;

class ConsultasController extends Controller
{
    /**
     * Listar todas las consultas.
     */
    public function index()
    {
        $consultas = Consultas::with(['cita', 'paciente', 'doctor', 'examen'])->get();
        return response()->json($consultas);
    }

    /**
     * Crear una nueva consulta.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cita_id' => 'required|exists:citas,id_cita',
            'examen_id' => 'nullable|exists:examenes,id_examen',
            'diagnostico' => 'required|string|max:255',
            'enfermedad' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'tratamiento' => 'nullable|string',
        ]);

        // Obtener paciente y doctor de la cita
        $cita = Citas::findOrFail($request->cita_id);

        $consulta = Consultas::create([
            'cita_id' => $validatedData['cita_id'],
            'paciente_id' => $cita->paciente_id,
            'doctor_id' => $cita->doctor_id,
            'examen_id' => $validatedData['examen_id'] ?? null, // Examen opcional
            'diagnostico' => $validatedData['diagnostico'],
            'enfermedad' => $validatedData['enfermedad'],
            'observaciones' => $validatedData['observaciones'],
            'tratamiento' => $validatedData['tratamiento'],
        ]);

        return response()->json($consulta, 201);
    }

    /**
     * Mostrar una consulta específica.
     */
    public function show($id)
    {
        $consulta = Consultas::with(['cita', 'paciente', 'doctor', 'examen'])->find($id);

        if (!$consulta) {
            return response()->json(['message' => 'Consulta no encontrada'], 404);
        }

        return response()->json($consulta);
    }

    /**
     * Actualizar una consulta específica.
     */
    public function update(Request $request, $id)
    {
        $consulta = Consultas::find($id);

        if (!$consulta) {
            return response()->json(['message' => 'Consulta no encontrada'], 404);
        }

        $validatedData = $request->validate([
            'examen_id' => 'nullable|exists:examenes,id_examen',
            'diagnostico' => 'required|string|max:255',
            'enfermedad' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'tratamiento' => 'nullable|string',
        ]);

        $consulta->update([
            'examen_id' => $validatedData['examen_id'] ?? $consulta->examen_id,
            'diagnostico' => $validatedData['diagnostico'],
            'enfermedad' => $validatedData['enfermedad'],
            'observaciones' => $validatedData['observaciones'],
            'tratamiento' => $validatedData['tratamiento'],
        ]);

        return response()->json($consulta);
    }

    /**
     * Eliminar una consulta específica.
     */
    public function destroy($id)
    {
        $consulta = Consultas::find($id);

        if (!$consulta) {
            return response()->json(['message' => 'Consulta no encontrada'], 404);
        }

        $consulta->delete();
        return response()->json(['message' => 'Consulta eliminada con éxito']);
    }
}
