<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Expediente de {{ $paciente->usuario?->nombre_completo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h3 {
            margin-bottom: 10px;
        }

        .consulta-block {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .consulta-block p {
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
        <h3>Consultas</h3>
        
        @foreach ($consultas as $consulta)
        <div class="consulta-block">
            <p><strong>Consulta N° </strong>{{ $consulta->id_consulta }}</p>
            <p><strong>Fecha de Consulta:</strong> {{ $consulta->cita->fecha_cita ?? 'No especificado' }}</p>
            <p><strong>Doctor:</strong> {{ $consulta->doctor->usuario->nombre_completo ?? 'No especificado' }}</p>
            <p><strong>Diagnóstico:</strong> {{ $consulta->diagnostico }}</p>
            <p><strong>Enfermedad:</strong> {{ $consulta->enfermedad ?? 'No especificado' }}</p>
            <p><strong>Tratamiento:</strong> {{ $consulta->tratamiento ?? 'No especificado' }}</p>
            <p><strong>Examen:</strong> {{ $consulta->examen->nombre ?? 'No se realizó examen' }}</p>
        </div>
        @endforeach
    </div>
</body>

</html>
