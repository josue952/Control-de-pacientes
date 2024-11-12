<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Exámenes de {{ $paciente->usuario->nombre_completo }}</title>
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

        .examenes {
            margin-bottom: 20px;
        }

        .examen-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .examen-item h4 {
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
        <h2>Reporte de Exámenes Realizados</h2>
        <h4>Paciente: {{ $paciente->usuario->nombre_completo }}</h4>
    </div>

    <div class="examenes">
        <h3>Examenes Realizados</h3>

        @if ($consultas->isEmpty())
            <p>No se encontraron exámenes en el rango de fechas seleccionado.</p>
        @else
            @foreach ($consultas as $consulta)
                <div class="examen-item">
                    <h4>Consulta ID: {{ $consulta->id_consulta }}</h4>
                    <p class="detalle"><strong>Fecha de la Consulta:</strong> {{ $consulta->created_at->format('d-m-Y') ?? 'Sin fecha' }}</p>
                    <p class="detalle"><strong>Doctor:</strong> {{ $consulta->doctor->usuario->nombre_completo ?? 'Sin doctor' }}</p>
                    
                    @if ($consulta->examen)
                        <p class="detalle"><strong>Tipo de Examen:</strong> {{ $consulta->examen->tipo_examen }}</p>
                        <p class="detalle"><strong>Descripción:</strong> {{ $consulta->examen->descripcion ?? 'No especificada' }}</p>
                        <p class="detalle"><strong>Fecha del Examen:</strong> {{ $consulta->examen->fecha_examen ?? 'Sin fecha' }}</p>
                        <p class="detalle"><strong>Resultados:</strong> 
                            {{ is_array($consulta->examen->resultados) ? implode(', ', $consulta->examen->resultados) : $consulta->examen->resultados ?? 'No especificados' }}
                        </p>
                        <p class="detalle"><strong>Observaciones:</strong> {{ $consulta->examen->observaciones ?? 'Sin observaciones' }}</p>
                    @else
                        <p class="detalle"><em>Este paciente no tiene exámenes asociados a esta consulta.</em></p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</body>

</html>
