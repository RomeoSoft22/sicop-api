<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prioridad extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_prioridad';
    public $timestamps = false;

    protected $fillable = [
        'n_prioridad',
        'x_prioridad',
        'l_activo',
    ];

    protected $primaryKey = 'n_prioridad';
}
