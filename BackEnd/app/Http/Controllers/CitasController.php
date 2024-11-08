<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Citas;

class CitasController extends Controller
{
    public function index()
    {
        $citas = Citas::with(['paciente', 'doctor'])->get();
        return response()->json($citas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id_paciente',
            'doctor_id' => 'nullable|exists:doctores,id_doctor',
            'fecha_cita' => 'required|date',
            'hora_cita' => 'required',
            'motivo_consulta' => 'nullable|string|max:255',
            'estado' => 'nullable|in:Pendiente,Completada,Cancelada',
        ]);

        $cita = Citas::create($request->all());
        return response()->json($cita, 201);
    }

    public function show($id)
    {
        $cita = Citas::with(['paciente', 'doctor'])->find($id);

        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Citas::find($id);

        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id_paciente',
            'doctor_id' => 'nullable|exists:doctores,id_doctor',
            'fecha_cita' => 'required|date',
            'hora_cita' => 'required',
            'motivo_consulta' => 'nullable|string|max:255',
            'estado' => 'nullable|in:Pendiente,Completada,Cancelada',
        ]);

        $cita->update($request->all());
        return response()->json($cita);
    }

    public function destroy($id)
    {
        $cita = Citas::find($id);

        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $cita->delete();
        return response()->json(['message' => 'Cita eliminada con éxito']);
    }
}
