<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complejidad extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_complejidad';
    public $timestamps = false;

    protected $fillable = [
        'n_complejidad',
        'x_complejidad',
        'l_activo',
    ];

    protected $primaryKey = 'n_complejidad';
}
