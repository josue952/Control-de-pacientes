<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use Notifiable;
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios'; // Nombre de la tabla
    protected $primaryKey = 'id_usuario'; // Nombre de la clave primaria personalizada

    // Atributos que se pueden asignar en masa
    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'Rol',
        'Fecha_registro',
    ];

    // Ocultar campos al serializar el modelo
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Tipos de datos de los atributos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'Fecha_registro' => 'date',
    ];

    
}
