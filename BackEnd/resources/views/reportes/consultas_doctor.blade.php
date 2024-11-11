<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Consultas del Doctor {{ $doctor->usuario->nombre_completo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2, .header h3 {
            margin: 0;
        }

        .consultas {
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
        <h2>Reporte de Consultas Realizadas</h2>
        <h3>Doctor: {{ $doctor->usuario->nombre_completo }}</h3>
    </div>

    @if ($consultas->isEmpty())
        <p>No se encontraron consultas realizadas por este doctor.</p>
    @else
        <div class="consultas">
            @foreach ($consultas as $consulta)
                <div class="consulta-item">
                    <h4>Consulta N° : {{ $consulta->id_consulta }}</h4>
                    <p class="detalle"><strong>Fecha:</strong> {{ $consulta->created_at->format('d-m-Y') }}</p>
                    <p class="detalle"><strong>Paciente:</strong> {{ $consulta->paciente->usuario->nombre_completo ?? 'No especificado' }}</p>
                    <p class="detalle"><strong>Diagnóstico:</strong> {{ $consulta->diagnostico }}</p>
                    <p class="detalle"><strong>Enfermedad:</strong> {{ $consulta->enfermedad ?? 'No especificada' }}</p>
                    <p class="detalle"><strong>Observaciones:</strong> {{ $consulta->observaciones ?? 'Sin Observaciones' }}</p>
                    <p class="detalle"><strong>Tratamiento:</strong> {{ $consulta->tratamiento ?? 'No especificado' }}</p>

                    @if ($consulta->examen)
                        <h5>Detalles del Examen:</h5>
                        <p class="detalle"><strong>Fecha del examen:</strong> {{ $consulta->examen->fecha_examen }}</p>
                        <p class="detalle"><strong>Tipo de Examen:</strong> {{ $consulta->examen->tipo_examen }}</p>
                        <p class="detalle"><strong>Descripción:</strong> {{ $consulta->examen->descripcion ?? 'No especificada' }}</p>
                        {{-- Verifica que resultados sea un array antes de usar implode --}}
                        <p class="detalle"><strong>Resultados:</strong> 
                            {{ $consulta->examen->resultados ?? 'Sin Resultados' }}
                        </p>
                        <p class="detalle"><strong>Observaciones:</strong> {{ $consulta->examen->observaciones ?? 'Sin observaciones' }}</p>
                    @else
                        <p class="detalle"><strong>Examen:</strong> No se realizó examen en esta consulta.</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
