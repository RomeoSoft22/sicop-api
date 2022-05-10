<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseDatos extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_bd';
    public $timestamps = false;

    protected $fillable = [
        'n_bd',
        'n_motor',
        'x_bd',
        'f_registro',
        'l_activo',
    ];

    protected $primaryKey = 'n_bd';
}
