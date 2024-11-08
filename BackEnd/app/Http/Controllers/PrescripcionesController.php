<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescripciones;

class PrescripcionesController extends Controller
{
    public function index()
    {
        $prescripciones = Prescripciones::with(['historialMedico', 'medicamento'])->get();
        return response()->json($prescripciones);
    }

    public function store(Request $request)
    {
        $request->validate([
            'historial_id' => 'nullable|exists:historial_medico,id_historial',
            'medicamento_id' => 'nullable|exists:medicamentos,id_medicamento',
            'dosis_prescrita' => 'nullable|string',
            'duracion' => 'nullable|string',
        ]);

        $prescripcion = Prescripciones::create($request->all());
        return response()->json($prescripcion, 201);
    }

    public function show($id)
    {
        $prescripcion = Prescripciones::with(['historialMedico', 'medicamento'])->find($id);

        if (!$prescripcion) {
            return response()->json(['message' => 'Prescripción no encontrada'], 404);
        }

        return response()->json($prescripcion);
    }

    public function update(Request $request, $id)
    {
        $prescripcion = Prescripciones::find($id);

        if (!$prescripcion) {
            return response()->json(['message' => 'Prescripción no encontrada'], 404);
        }

        $request->validate([
            'historial_id' => 'nullable|exists:historial_medico,id_historial',
            'medicamento_id' => 'nullable|exists:medicamentos,id_medicamento',
            'dosis_prescrita' => 'nullable|string',
            'duracion' => 'nullable|string',
        ]);

        $prescripcion->update($request->all());
        return response()->json($prescripcion);
    }

    public function destroy($id)
    {
        $prescripcion = Prescripciones::find($id);

        if (!$prescripcion) {
            return response()->json(['message' => 'Prescripción no encontrada'], 404);
        }

        $prescripcion->delete();
        return response()->json(['message' => 'Prescripción eliminada con éxito']);
    }
}
