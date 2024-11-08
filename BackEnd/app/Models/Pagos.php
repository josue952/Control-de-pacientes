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
        'cita_id',       // Relación con la cita en lugar del paciente
        'monto',
        'fecha_pago',
    ];

    // Relación con el modelo Citas
    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id', 'id_cita');
    }
}
