<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamentos extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';
    protected $primaryKey = 'id_medicamento';

    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad',
        'dosis',
        'fecha_registro'
    ];

    public $timestamps = true;
}
