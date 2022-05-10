<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Esquema extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_esquema';
    public $timestamps = false;

    protected $fillable = [
        'n_esquema',
        'x_esquema',
        'n_bd',
        'l_activo',
    ];

    protected $primaryKey = 'n_esquema';
}
