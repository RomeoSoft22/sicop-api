<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSistema extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_tiposistema';
    public $timestamps = false;

    protected $fillable = [
        'n_tiposistema',
        'x_tiposistema',
        'f_registro',
        'l_activo',
    ];

    protected $primaryKey = 'n_tiposistema';
}
