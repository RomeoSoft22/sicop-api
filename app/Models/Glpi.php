<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Glpi extends Model
{
    use HasFactory;
    protected $table = 'sicop.mov_glpi';
    public $timestamps = false;

    protected $fillable = [
        'n_glpi',
        'c_glpi',
        'x_glpi',
        'n_prioridad',
        'n_impacto',
        'n_complejidad',
        'f_registro',
        'l_activo',
    ];

    protected $primaryKey = 'n_glpi';
}
