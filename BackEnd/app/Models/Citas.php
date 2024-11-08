<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Citas extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $primaryKey = 'id_cita';

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha_cita',
        'hora_cita',
        'motivo_consulta',
        'estado',
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
