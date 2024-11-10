<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultas;
use App\Models\Citas;
use App\Models\Examenes;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConsultasController extends Controller
{
    // Listar todas las consultas y datos del paciente y doctor asociados
    public function index()
    {
        try {
            // Cargar consultas con relaciones de paciente y doctor
            $consultas = Consultas::with(['paciente.usuario', 'doctor.usuario'])->get();

            // Formatear la respuesta para incluir el nombre del paciente y del doctor
            $consultas = $consultas->map(function ($consulta) {
                return [
                    'id_consulta' => $consulta->id_consulta,
                    'cita_id' => $consulta->cita_id,
                    'paciente_id' => $consulta->paciente_id,
                    'doctor_id' => $consulta->doctor_id,
                    'examen_id' => $consulta->examen_id,
                    'diagnostico' => $consulta->diagnostico,
                    'enfermedad' => $consulta->enfermedad,
                    'observaciones' => $consulta->observaciones,
                    'tratamiento' => $consulta->tratamiento,
                    'created_at' => $consulta->created_at,
                    'updated_at' => $consulta->updated_at,
                    'pacienteNombre' => $consulta->paciente->usuario->nombre_completo ?? 'Sin paciente',
                    'doctorNombre' => $consulta->doctor->usuario->nombre_completo ?? 'Sin doctor',
                ];
            });

            return response()->json($consultas);
        } catch (\Exception $e) {
            Log::error('Error al listar consultas: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al listar consultas'], 500);
        }
    }

    // Crear una nueva consulta
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'cita_id' => 'required|exists:citas,id_cita',
                'examen_id' => 'nullable|integer', // Permitir valores enteros (incluyendo 0)
                'diagnostico' => 'required|string|max:255',
                'enfermedad' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
                'tratamiento' => 'nullable|string',
            ]);

            // Verificar el examen_id y establecerlo como null si es 0 o no existe en la tabla de exámenes usando el modelo Examenes
            $validatedData['examen_id'] = ($validatedData['examen_id'] === 0 || !Examenes::where('id_examen', $validatedData['examen_id'])->exists()) 
                ? null 
                : $validatedData['examen_id'];

            $cita = Citas::findOrFail($validatedData['cita_id']);
            
            if (!$cita->pagada) {
                return response()->json([
                    'message' => 'No se puede pasar consulta ya que la cita aún no ha sido pagada.'
                ], 422);
            }

            $consulta = Consultas::create([
                'cita_id' => $validatedData['cita_id'],
                'paciente_id' => $cita->paciente_id,
                'doctor_id' => $cita->doctor_id,
                'examen_id' => $validatedData['examen_id'],
                'diagnostico' => $validatedData['diagnostico'],
                'enfermedad' => $validatedData['enfermedad'],
                'observaciones' => $validatedData['observaciones'],
                'tratamiento' => $validatedData['tratamiento'],
            ]);

            return response()->json(['message' => 'Consulta creada exitosamente', 'consulta' => $consulta], 201);
        } catch (ValidationException $e) {
            Log::error('Error de validación al crear consulta: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear consulta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear consulta'], 500);
        }
    }

    // Actualizar una consulta específica
    public function update(Request $request, $id)
    {
        $consulta = Consultas::find($id);
        if (!$consulta) {
            return response()->json(['message' => 'Consulta no encontrada'], 404);
        }

        try {
            $validatedData = $request->validate([
                'examen_id' => 'nullable|integer', // Permitir valores enteros (incluyendo 0)
                'diagnostico' => 'required|string|max:255',
                'enfermedad' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
                'tratamiento' => 'nullable|string',
            ]);

            // Verificar el examen_id y establecerlo como null si es 0 o no existe en la tabla de exámenes usando el modelo Examenes
            $validatedData['examen_id'] = ($validatedData['examen_id'] === 0 || !Examenes::where('id_examen', $validatedData['examen_id'])->exists()) 
                ? null 
                : $validatedData['examen_id'];

            // Verificar que la cita esté pagada
            $cita = Citas::findOrFail($consulta->cita_id);
            if (!$cita->pagada) {
                return response()->json([
                    'message' => 'No se puede actualizar la consulta ya que la cita aún no ha sido pagada.'
                ], 422);
            }

            $consulta->update([
                'examen_id' => $validatedData['examen_id'],
                'diagnostico' => $validatedData['diagnostico'],
                'enfermedad' => $validatedData['enfermedad'],
                'observaciones' => $validatedData['observaciones'],
                'tratamiento' => $validatedData['tratamiento'],
            ]);

            return response()->json(['message' => 'Consulta actualizada exitosamente', 'consulta' => $consulta]);

        } catch (ValidationException $e) {
            Log::error('Error de validación al actualizar consulta: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar consulta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar consulta'], 500);
        }
    }

    // Eliminar una consulta específica
    public function destroy($id)
    {
        try {
            $consulta = Consultas::find($id);
            if (!$consulta) {
                return response()->json(['message' => 'Consulta no encontrada'], 404);
            }

            $consulta->delete();
            return response()->json(['message' => 'Consulta eliminada con éxito']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar consulta: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al eliminar consulta'], 500);
        }
    }
}
