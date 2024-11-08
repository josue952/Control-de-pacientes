<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultas;

class ConsultasController extends Controller
{
    /**
     * Listar todas las consultas.
     */
    public function index()
    {
        $consultas = Consultas::with(['cita', 'paciente', 'doctor'])->get();
        return response()->json($consultas);
    }

    /**
     * Crear una nueva consulta.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cita_id' => 'required|exists:citas,id_cita',
            'diagnostico' => 'required|string|max:255',
            'enfermedad' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'tratamiento' => 'nullable|string',
        ]);

        // Se obtiene paciente y doctor de la cita
        $cita = \App\Models\Citas::findOrFail($request->cita_id);
        
        $consulta = Consultas::create([
            'cita_id' => $request->cita_id,
            'paciente_id' => $cita->paciente_id,
            'doctor_id' => $cita->doctor_id,
            'diagnostico' => $request->diagnostico,
            'enfermedad' => $request->enfermedad,
            'observaciones' => $request->observaciones,
            'tratamiento' => $request->tratamiento,
        ]);

        return response()->json($consulta, 201);
    }

    /**
     * Mostrar una consulta específica.
     */
    public function show($id)
    {
        $consulta = Consultas::with(['cita', 'paciente', 'doctor'])->find($id);

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

        $request->validate([
            'diagnostico' => 'required|string|max:255',
            'enfermedad' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'tratamiento' => 'nullable|string',
        ]);

        $consulta->update([
            'diagnostico' => $request->diagnostico,
            'enfermedad' => $request->enfermedad,
            'observaciones' => $request->observaciones,
            'tratamiento' => $request->tratamiento,
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
