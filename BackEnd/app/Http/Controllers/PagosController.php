<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pagos;

class PagosController extends Controller
{
    public function index()
    {
        $pagos = Pagos::with('paciente')->get();
        return response()->json($pagos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id_paciente',
            'monto' => 'required|numeric',
            'fecha_pago' => 'required|date',
        ]);

        $pago = Pagos::create($request->all());
        return response()->json($pago, 201);
    }

    public function show($id)
    {
        $pago = Pagos::with('paciente')->find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        return response()->json($pago);
    }

    public function update(Request $request, $id)
    {
        $pago = Pagos::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id_paciente',
            'monto' => 'required|numeric',
            'fecha_pago' => 'required|date',
        ]);

        $pago->update($request->all());
        return response()->json($pago);
    }

    public function destroy($id)
    {
        $pago = Pagos::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $pago->delete();
        return response()->json(['message' => 'Pago eliminado con éxito']);
    }
}
