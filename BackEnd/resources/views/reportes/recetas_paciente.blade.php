<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recetas de {{ $paciente->nombre_completo }}</title>
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
        <h2>Reporte de Recetas</h2>
        <h4>Paciente: {{ $paciente->usuario->nombre_completo }}</h4>
    </div>

    <div class="section">
        <h3>Recetas</h3>
        @foreach ($recetas as $receta)
            <p><strong>Consulta N° </strong>{{ $receta->consulta_id }}</p>
            <p><strong>Fecha:</strong> {{ $receta->consulta->cita->fecha_cita ?? 'Sin fecha' }}</p>
            <p><strong>Doctor:</strong> {{ $receta->consulta->doctor->usuario->nombre_completo ?? 'Sin doctor' }}</p>
            <p><strong>Medicamento:</strong> {{ $receta->medicamento->nombre ?? 'Sin medicamento' }}</p>
            <p><strong>Cantidad:</strong> {{ $receta->cantidad }}</p>
            <p><strong>Dosis Prescrita:</strong> {{ $receta->dosis_prescrita }}</p>
            <p><strong>Duración:</strong> {{ $receta->duracion }}</p>
            <hr>
        @endforeach
    </div>
</body>

</html>
