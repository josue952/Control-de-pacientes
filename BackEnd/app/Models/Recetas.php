<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recetas extends Model
{
    use HasFactory;

    protected $table = 'recetas';

    protected $primaryKey = 'id_receta';

    protected $fillable = [
        'consulta_id',
        'medicamento_id',
        'dosis_prescrita',
        'duracion',
    ];

    // Relación con la tabla Consultas
    public function consulta()
    {
        return $this->belongsTo(Consultas::class, 'consulta_id', 'id_consulta');
    }

    // Relación con la tabla Medicamentos
    public function medicamento()
    {
        return $this->belongsTo(Medicamentos::class, 'medicamento_id', 'id_medicamento');
    }
}
