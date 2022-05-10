<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_sistema';
    public $timestamps = false;

    protected $fillable = [
        'n_sistema',
        'n_tiposistema',
        'x_sistema',
        'f_registro',
        'l_activo',
    ];

    protected $primaryKey = 'n_sistema';
}
