<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recetas;

class RecetasController extends Controller
{
    public function index()
    {
        $recetas = Recetas::with(['consulta', 'medicamento'])->get();
        return response()->json($recetas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'consulta_id' => 'nullable|exists:consultas,id_consulta',
            'medicamento_id' => 'nullable|exists:medicamentos,id_medicamento',
            'dosis_prescrita' => 'nullable|string',
            'duracion' => 'nullable|string',
        ]);

        $receta = Recetas::create($request->all());
        return response()->json($receta, 201);
    }

    public function show($id)
    {
        $receta = Recetas::with(['consulta', 'medicamento'])->find($id);

        if (!$receta) {
            return response()->json(['message' => 'Receta no encontrada'], 404);
        }

        return response()->json($receta);
    }

    public function update(Request $request, $id)
    {
        $receta = Recetas::find($id);

        if (!$receta) {
            return response()->json(['message' => 'Receta no encontrada'], 404);
        }

        $request->validate([
            'consulta_id' => 'nullable|exists:consultas,id_consulta',
            'medicamento_id' => 'nullable|exists:medicamentos,id_medicamento',
            'dosis_prescrita' => 'nullable|string',
            'duracion' => 'nullable|string',
        ]);

        $receta->update($request->all());
        return response()->json($receta);
    }

    public function destroy($id)
    {
        $receta = Recetas::find($id);

        if (!$receta) {
            return response()->json(['message' => 'Receta no encontrada'], 404);
        }

        $receta->delete();
        return response()->json(['message' => 'Receta eliminada con éxito']);
    }
}
