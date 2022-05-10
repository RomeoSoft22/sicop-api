<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignar extends Model
{
    use HasFactory;
    protected $table = 'sicop.mov_asignar';
    public $timestamps = false;

    protected $fillable = [
        'n_asigna',
        'n_sistema',
        'n_tiposistema',
        'n_bandeja',
        'x_asigna',
        'n_correlativo',
        'n_anio',
        'n_usuario',
        'n_prioridad',
        'n_impacto',
        'n_complejidad',
        'x_objetivo',
        'x_alcancebd',
        'x_prerequisitodb',
        'l_conexionbd',
        'x_alcenceser',
        'x_plataformaser',
        'x_consideraser',
        'x_observaadi',
        'x_ordenadi',
        'f_registro',       
        'l_activo',
    ];

    protected $primaryKey = 'n_asigna';
}
