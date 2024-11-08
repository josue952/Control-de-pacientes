<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultas extends Model
{
    use HasFactory;

    protected $table = 'consultas';
    protected $primaryKey = 'id_consulta';

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'doctor_id',
        'diagnostico',
        'enfermedad',
        'observaciones',
        'tratamiento',
    ];

    // Relación con la tabla Citas
    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id', 'id_cita');
    }

    // Relación con la tabla Pacientes
    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id', 'id_paciente');
    }

    // Relación con la tabla Doctores
    public function doctor()
    {
        return $this->belongsTo(Doctores::class, 'doctor_id', 'id_doctor');
    }
}
