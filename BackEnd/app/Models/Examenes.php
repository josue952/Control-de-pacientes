<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examenes extends Model
{
    use HasFactory;

    protected $table = 'examenes';

    protected $primaryKey = 'id_examen';

    protected $fillable = [
        'tipo_examen',
        'descripcion',
        'fecha_examen',
        'resultados',
        'observaciones',
    ];

    // Atributo para manejar resultados como JSON
    protected $casts = [
        'resultados' => 'array',
    ];

    // Relación con Consultas (una consulta puede tener un examen)
    public function consulta()
    {
        return $this->hasOne(Consultas::class, 'examen_id', 'id_examen');
    }
}
