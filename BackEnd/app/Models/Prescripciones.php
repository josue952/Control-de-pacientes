<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescripciones extends Model
{
    use HasFactory;

    protected $table = 'prescripciones';

    protected $primaryKey = 'id_prescripcion';

    protected $fillable = [
        'historial_id',
        'medicamento_id',
        'dosis_prescrita',
        'duracion',
    ];

    public function historialMedico()
    {
        return $this->belongsTo(Historial_medico::class, 'historial_id', 'id_historial');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamentos::class, 'medicamento_id', 'id_medicamento');
    }
}
