<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_entidad';
    public $timestamps = false;

    protected $fillable = [
        'n_entidad',
        'x_entidad',
        'l_activo',
    ];

    protected $primaryKey = 'n_entidad';
}
