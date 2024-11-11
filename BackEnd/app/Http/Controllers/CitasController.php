<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Citas;
use App\Models\Pacientes;
use App\Models\Doctores;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CitasController extends Controller
{
    // Listar todas las citas
    public function index()
    {
        try {
            $citas = Citas::with(['paciente.usuario', 'doctor.usuario'])->get();
            return response()->json($citas);
        } catch (\Exception $e) {
            Log::error('Error al listar citas: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al listar citas'], 500);
        }
    }

    // Crear una nueva cita
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'paciente_id' => 'nullable|exists:pacientes,id_paciente',
                'doctor_id' => 'nullable|exists:doctores,id_doctor',
                'fecha_cita' => 'required|date',
                'hora_cita' => 'required|date_format:H:i',
                'motivo_consulta' => 'nullable|string|max:255',
                'estado' => 'nullable|in:Pendiente,Completada,Cancelada',
                'monto_consulta' => 'nullable|numeric|min:0',
                'pagada' => 'nullable|boolean',
            ]);

            // Convertir monto_consulta a decimal si es necesario
            if (isset($validatedData['monto_consulta']) && is_string($validatedData['monto_consulta'])) {
                $validatedData['monto_consulta'] = (float) $validatedData['monto_consulta'];
            }

            // Verificar la fecha y hora de la cita
            $fechaCita = Carbon::parse($validatedData['fecha_cita'] . ' ' . $validatedData['hora_cita']);
            $now = Carbon::now();

            // Validar que la cita sea al menos 24 horas después de la fecha y hora actual
            if ($fechaCita->lessThanOrEqualTo($now->copy()->addHours(24))) {
                return response()->json([
                    'message' => 'La cita debe ser agendada con al menos un día de anticipación.'
                ], 422);
            }

            // Verificar si el paciente ya tiene una cita en la misma fecha
            $existingCita = Citas::where('paciente_id', $validatedData['paciente_id'])
                ->whereDate('fecha_cita', $validatedData['fecha_cita'])
                ->first();

            if ($existingCita) {
                return response()->json([
                    'message' => 'El paciente ya tiene una cita programada para esta fecha. Solo puede agendar una cita por día.'
                ], 422);
            }

            // Verificar si el doctor ya tiene una cita en la misma fecha y en el mismo intervalo de una hora
            $doctorCitaConflict = Citas::where('doctor_id', $validatedData['doctor_id'])
                ->whereDate('fecha_cita', $validatedData['fecha_cita'])
                ->whereTime('hora_cita', '>=', $fechaCita->subHour()->format('H:i'))
                ->whereTime('hora_cita', '<=', $fechaCita->addHour()->format('H:i'))
                ->exists();

            if ($doctorCitaConflict) {
                return response()->json([
                    'message' => 'Este doctor ya tiene agendada una cita en esta fecha y en un horario cercano. Seleccione otra fecha u hora.'
                ], 422);
            }

            // Restablecer la hora de la cita para guardarla correctamente
            $fechaCita->setTimeFromTimeString($validatedData['hora_cita']);
            $validatedData['pagada'] = $validatedData['pagada'] ?? false;
            $cita = Citas::create($validatedData);

            return response()->json(['message' => 'Cita creada exitosamente', 'cita' => $cita], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear cita: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear cita: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear cita'], 500);
        }
    }

    // Actualizar una cita
    public function update(Request $request, $id)
    {
        $cita = Citas::find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        try {
            $validatedData = $request->validate([
                'paciente_id' => 'nullable|exists:pacientes,id_paciente',
                'doctor_id' => 'nullable|exists:doctores,id_doctor',
                'fecha_cita' => 'required|date',
                'hora_cita' => 'required|date_format:H:i',
                'motivo_consulta' => 'nullable|string|max:255',
                'estado' => 'nullable|in:Pendiente,Completada,Cancelada',
                'monto_consulta' => 'nullable|numeric|min:0',
                'pagada' => 'nullable|boolean',
            ]);

            // Convertir monto_consulta a decimal si es necesario
            if (isset($validatedData['monto_consulta']) && is_string($validatedData['monto_consulta'])) {
                $validatedData['monto_consulta'] = (float) $validatedData['monto_consulta'];
            }

            // Verificar la fecha y hora de la cita
            $fechaCita = Carbon::parse($validatedData['fecha_cita'] . ' ' . $validatedData['hora_cita']);
            $now = Carbon::now();

            // Validar que la cita sea al menos 24 horas después de la fecha y hora actual
            if ($fechaCita->lessThanOrEqualTo($now->copy()->addHours(24))) {
                return response()->json([
                    'message' => 'La cita debe ser agendada con al menos un día de anticipación.'
                ], 422);
            }

            // Verificar si el paciente ya tiene otra cita en la misma fecha (excluyendo la cita actual)
            $existingCita = Citas::where('paciente_id', $validatedData['paciente_id'])
                ->whereDate('fecha_cita', $validatedData['fecha_cita'])
                ->where('id_cita', '!=', $id)
                ->first();

            if ($existingCita) {
                return response()->json([
                    'message' => 'El paciente ya tiene una cita programada para esta fecha. Solo puede agendar una cita por día.'
                ], 422);
            }

            // Verificar si el doctor ya tiene una cita en la misma fecha y en el mismo intervalo de una hora (excluyendo la cita actual)
            $doctorCitaConflict = Citas::where('doctor_id', $validatedData['doctor_id'])
                ->whereDate('fecha_cita', $validatedData['fecha_cita'])
                ->where('id_cita', '!=', $id)
                ->whereTime('hora_cita', '>=', $fechaCita->copy()->subHour()->format('H:i'))
                ->whereTime('hora_cita', '<=', $fechaCita->copy()->addHour()->format('H:i'))
                ->exists();

            if ($doctorCitaConflict) {
                return response()->json([
                    'message' => 'Este doctor ya tiene agendada una cita en esta fecha y en un horario cercano. Seleccione otra fecha u hora.'
                ], 422);
            }

            // Restablecer la hora de la cita para guardarla correctamente
            $fechaCita->setTimeFromTimeString($validatedData['hora_cita']);
            $validatedData['pagada'] = $validatedData['pagada'] ?? false;

            // Actualizar la cita en caso de validación exitosa
            $cita->update($validatedData);
            return response()->json(['message' => 'Cita actualizada exitosamente', 'cita' => $cita]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al actualizar cita: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cita: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar cita', 'error' => $e->getMessage()], 500);
        }
    }


    // Eliminar una cita
    public function destroy($id)
    {
        try {
            $cita = Citas::find($id);
            if (!$cita) {
                return response()->json(['message' => 'Cita no encontrada'], 404);
            }

            $cita->delete();
            return response()->json(['message' => 'Cita eliminada con éxito']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar cita: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al eliminar cita'], 500);
        }
    }
}
