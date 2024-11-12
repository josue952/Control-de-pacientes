<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recetas de {{ $paciente->usuario->nombre_completo }}</title>
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

        .receta-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .receta-item h4 {
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
        <h2>Reporte de Recetas</h2>
        <h4>Paciente: {{ $paciente->usuario->nombre_completo }}</h4>
    </div>

    <div class="section">
        <h3>Recetas</h3>
        @if ($recetas->isEmpty())
            <p>No se encontraron recetas para este paciente en el rango de fechas seleccionado.</p>
        @else
            @foreach ($recetas as $receta)
                <div class="receta-item">
                    <h4>Consulta N° {{ $receta->consulta_id }}</h4>
                    <p class="detalle"><strong>Fecha:</strong> {{ $receta->consulta->cita->fecha_cita ?? 'Sin fecha' }}</p>
                    <p class="detalle"><strong>Doctor:</strong> {{ $receta->consulta->doctor->usuario->nombre_completo ?? 'Sin doctor' }}</p>
                    <p class="detalle"><strong>Medicamento:</strong> {{ $receta->medicamento->nombre ?? 'Sin medicamento' }}</p>
                    <p class="detalle"><strong>Cantidad:</strong> {{ $receta->cantidad }}</p>
                    <p class="detalle"><strong>Dosis Prescrita:</strong> {{ $receta->dosis_prescrita }}</p>
                    <p class="detalle"><strong>Duración:</strong> {{ $receta->duracion }}</p>
                </div>
            @endforeach
        @endif
    </div>
</body>

</html>
