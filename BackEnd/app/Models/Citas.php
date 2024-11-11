<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citas extends Model
{
    use HasFactory;

    protected $table = 'citas';
    protected $primaryKey = 'id_cita';

    // Incluye los nuevos campos en $fillable para que sean asignables en masa
    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha_cita',
        'hora_cita',
        'motivo_consulta',
        'estado',
        'monto_consulta',   // Nuevo campo para el costo de la consulta
        'pagada',           // Nuevo campo para indicar si está pagada
    ];

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id', 'id_paciente');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctores::class, 'doctor_id', 'id_doctor');
    }
}
