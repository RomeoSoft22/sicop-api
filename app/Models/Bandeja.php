<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bandeja extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_bandeja';
    public $timestamps = false;

    protected $fillable = [
        'n_bandeja',
        'x_bandeja',
        'x_icono',
        'l_activo',
    ];

    protected $primaryKey = 'n_bandeja';
}
