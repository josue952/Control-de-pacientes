<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctores;

class DoctoresController extends Controller
{
    public function index()
    {
        $doctores = Doctores::with('usuario')->get();
        return response()->json($doctores);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'nullable|exists:usuarios,id_usuario',
            'especialidad' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:15',
        ]);

        $doctor = Doctores::create($request->all());
        return response()->json($doctor, 201);
    }

    public function show($id)
    {
        $doctor = Doctores::with('usuario')->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctores::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $request->validate([
            'usuario_id' => 'nullable|exists:usuarios,id_usuario',
            'especialidad' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:15',
        ]);

        $doctor->update($request->all());
        return response()->json($doctor);
    }

    public function destroy($id)
    {
        $doctor = Doctores::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        $doctor->delete();
        return response()->json(['message' => 'Doctor eliminado con éxito']);
    }
}
