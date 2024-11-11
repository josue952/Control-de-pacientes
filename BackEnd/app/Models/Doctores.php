<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctores extends Model
{
    use HasFactory;

    protected $table = 'doctores';

    protected $primaryKey = 'id_doctor';

    protected $fillable = [
        'usuario_id',
        'especialidad',
        'telefono',
        'fecha_registro',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario')->withDefault([
            'nombre_completo' => 'Sin usuario asignado'
        ]);
    }
}
