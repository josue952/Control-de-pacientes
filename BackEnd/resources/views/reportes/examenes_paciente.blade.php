<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Exámenes de {{ $paciente->nombre_completo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Reporte de Exámenes</h2>
        <h4>Paciente: {{ $paciente->usuario->nombre_completo }}</h4>
    </div>

    <div class="section">
        <h3>Exámenes Realizados</h3>
        @if ($consultas->isEmpty())
            <p>No se encontraron exámenes en el rango de fechas seleccionado.</p>
        @else
            @foreach ($consultas as $consulta)
                <p><strong>Consulta N° :</strong> {{ $consulta->id_consulta }}</p>
                <p><strong>Fecha de la Consulta:</strong> {{ $consulta->cita->fecha_cita ?? 'Sin fecha' }}</p>
                <p><strong>Doctor:</strong> {{ $consulta->doctor->usuario->nombre_completo ?? 'Sin doctor' }}</p>
                @if ($consulta->examen)
                    <p><strong>Tipo de Examen:</strong> {{ $consulta->examen->tipo_examen }}</p>
                    <p><strong>Descripción:</strong> {{ $consulta->examen->descripcion }}</p>
                    <p><strong>Fecha del Examen:</strong> {{ $consulta->examen->fecha_examen }}</p>
                    <p><strong>Resultados:</strong> {{ is_array($consulta->examen->resultados) ? json_encode($consulta->examen->resultados) : $consulta->examen->resultados }}</p>
                    <p><strong>Observaciones:</strong> {{ $consulta->examen->observaciones ?? 'Sin observaciones' }}</p>
                @else
                    <p><em>Este paciente no tiene exámenes asociados a esta consulta.</em></p>
                @endif
                <hr>
            @endforeach
        @endif
    </div>
</body>

</html>
