<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Historial_medico;

class HistorialMedicoController extends Controller
{
    public function index()
    {
        $historiales = Historial_medico::with(['paciente', 'doctor'])->get();
        return response()->json($historiales);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id_paciente',
            'doctor_id' => 'nullable|exists:doctores,id_doctor',
            'fecha' => 'required|date',
            'diagnostico' => 'nullable|string',
            'tratamiento' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $historial = Historial_medico::create($request->all());
        return response()->json($historial, 201);
    }

    public function show($id)
    {
        $historial = Historial_medico::with(['paciente', 'doctor'])->find($id);

        if (!$historial) {
            return response()->json(['message' => 'Historial no encontrado'], 404);
        }

        return response()->json($historial);
    }

    public function update(Request $request, $id)
    {
        $historial = Historial_medico::find($id);

        if (!$historial) {
            return response()->json(['message' => 'Historial no encontrado'], 404);
        }

        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id_paciente',
            'doctor_id' => 'nullable|exists:doctores,id_doctor',
            'fecha' => 'required|date',
            'diagnostico' => 'nullable|string',
            'tratamiento' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $historial->update($request->all());
        return response()->json($historial);
    }

    public function destroy($id)
    {
        $historial = Historial_medico::find($id);

        if (!$historial) {
            return response()->json(['message' => 'Historial no encontrado'], 404);
        }

        $historial->delete();
        return response()->json(['message' => 'Historial eliminado con éxito']);
    }
}
