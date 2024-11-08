<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historial_medico extends Model
{
    use HasFactory;

    protected $table = 'historial_medico';

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha',
        'diagnostico',
        'tratamiento',
        'observaciones',
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
