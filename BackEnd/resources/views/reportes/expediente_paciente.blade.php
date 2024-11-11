<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Expediente de {{ $paciente->usuario?->nombre_completo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2, .header h4 {
            margin: 0;
        }

        .section {
            margin-bottom: 20px;
        }

        .consulta-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .consulta-item h4 {
            margin: 0;
            color: #007BFF;
        }

        .detalle {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Expediente Médico</h2>
        <h4>Paciente: {{ $paciente->usuario?->nombre_completo }}</h4>
    </div>

    <div class="section">
        <h3>Consultas Realizadas</h3>

        @if ($consultas->isEmpty())
            <p>Este paciente no ha pasado ninguna consulta para generar su expediente.</p>
        @else
            @foreach ($consultas as $consulta)
                <div class="consulta-item">
                    <h4>Consulta ID: {{ $consulta->id_consulta }}</h4>
                    <p class="detalle"><strong>Fecha de Consulta:</strong> {{ $consulta->cita->fecha_cita ?? 'No especificado' }}</p>
                    <p class="detalle"><strong>Doctor:</strong> {{ $consulta->doctor->usuario->nombre_completo ?? 'No especificado' }}</p>
                    <p class="detalle"><strong>Diagnóstico:</strong> {{ $consulta->diagnostico }}</p>
                    <p class="detalle"><strong>Enfermedad:</strong> {{ $consulta->enfermedad ?? 'No especificado' }}</p>
                    <p class="detalle"><strong>Tratamiento:</strong> {{ $consulta->tratamiento ?? 'No especificado' }}</p>

                    @if ($consulta->examen)
                        <h5>Detalles del Examen:</h5>
                        <p class="detalle"><strong>Tipo de Examen:</strong> {{ $consulta->examen->tipo_examen ?? 'No especificado' }}</p>
                        <p class="detalle"><strong>Descripción:</strong> {{ $consulta->examen->descripcion ?? 'No especificada' }}</p>
                        <p class="detalle"><strong>Resultados:</strong> 
                            {{ is_array($consulta->examen->resultados) ? implode(', ', $consulta->examen->resultados) : $consulta->examen->resultados ?? 'No especificados' }}
                        </p>
                        <p class="detalle"><strong>Observaciones:</strong> {{ $consulta->examen->observaciones ?? 'Sin observaciones' }}</p>
                    @else
                        <p class="detalle"><em>No se realizó examen en esta consulta.</em></p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</body>

</html>
