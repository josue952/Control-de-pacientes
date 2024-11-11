<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultas;
use App\Models\Pacientes;
use App\Models\Doctores;
use App\Models\Recetas;
use PDF;

class ReportController extends Controller
{
    // Generar reporte de expediente del paciente
    public function generarExpedientePaciente($paciente_id)
    {
        // Buscar el paciente
        $paciente = Pacientes::find($paciente_id);

        // Verificar si el paciente existe
        if (!$paciente) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        // Obtener todas las consultas del paciente junto con sus relaciones
        $consultas = Consultas::with(['cita', 'doctor.usuario', 'examen'])
            ->where('paciente_id', $paciente_id)
            ->get();

        // Crear el PDF con Dompdf y enviar los datos de consultas y paciente
        $pdf = PDF::loadView('reportes.expediente_paciente', [
            'paciente' => $paciente,
            'consultas' => $consultas
        ]);

        // Devolver el PDF en línea en el navegador
        return $pdf->stream("expediente_paciente_{$paciente->id_paciente}.pdf");
    }

    // Generar reporte de recetas del paciente con opción de filtro por rango de fechas
    public function generarRecetasPaciente($paciente_id, Request $request)
    {
        // Validar las fechas desde y hasta
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        // Buscar el paciente
        $paciente = Pacientes::find($paciente_id);

        // Verificar si el paciente existe
        if (!$paciente) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        // Obtener las recetas en el rango de fechas, filtrando por paciente
        $recetas = Recetas::whereHas('consulta', function ($query) use ($paciente_id, $fechaDesde, $fechaHasta) {
            $query->where('paciente_id', $paciente_id)
                ->whereHas('cita', function ($query) use ($fechaDesde, $fechaHasta) {
                    $query->whereBetween('fecha_cita', [$fechaDesde, $fechaHasta]);
                });
        })
            ->with(['consulta.cita', 'consulta.doctor.usuario', 'medicamento']) // Traer datos de la consulta, cita y medicamento
            ->get();

        // Crear el PDF con Dompdf y enviar los datos de recetas y paciente
        $pdf = PDF::loadView('reportes.recetas_paciente', [
            'paciente' => $paciente,
            'recetas' => $recetas
        ]);

        // Devolver el PDF en línea en el navegador
        return $pdf->stream("recetas_paciente_{$paciente->id_paciente}.pdf");
    }

    // Generar reporte de exámenes del paciente con opción de filtro por rango de fechas
    public function generarExamenesPaciente($paciente_id, Request $request)
    {
        // Validar las fechas desde y hasta
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        // Buscar el paciente
        $paciente = Pacientes::find($paciente_id);

        // Verificar si el paciente existe
        if (!$paciente) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        // Obtener las consultas del paciente en el rango de fechas que tengan un examen asociado
        $consultas = Consultas::with(['doctor.usuario', 'examen'])
            ->where('paciente_id', $paciente_id)
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta]) // Ajustado para usar `created_at` en `consultas`
            ->whereNotNull('examen_id') // Filtrar solo las consultas con examen
            ->get();

        // Crear el PDF con Dompdf y enviar los datos de consultas con examen y paciente
        $pdf = PDF::loadView('reportes.examenes_paciente', [
            'paciente' => $paciente,
            'consultas' => $consultas
        ]);

        // Devolver el PDF en línea en el navegador
        return $pdf->stream("examenes_paciente_{$paciente->id_paciente}.pdf");
    }

    // Generar reporte de consultas realizadas por un médico específico
    public function generarConsultasPorDoctor($doctor_id)
    {
        // Buscar el doctor
        $doctor = Doctores::with('usuario')->find($doctor_id);

        // Verificar si el doctor existe
        if (!$doctor) {
            return response()->json(['message' => 'Doctor no encontrado'], 404);
        }

        // Obtener todas las consultas realizadas por el doctor junto con sus relaciones
        $consultas = Consultas::with(['paciente.usuario', 'examen'])
            ->where('doctor_id', $doctor_id)
            ->get();

        // Crear el PDF con Dompdf y enviar los datos de consultas y doctor
        $pdf = PDF::loadView('reportes.consultas_doctor', [
            'doctor' => $doctor,
            'consultas' => $consultas
        ]);

        // Devolver el PDF en línea en el navegador
        return $pdf->stream("consultas_doctor_{$doctor->id_doctor}.pdf");
    }
}
