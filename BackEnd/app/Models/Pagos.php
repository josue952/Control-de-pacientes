<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'paciente_id',
        'monto',
        'fecha_pago',
    ];

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id', 'id_paciente');
    }
}
