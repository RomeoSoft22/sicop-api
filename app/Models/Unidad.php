<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_unidad';
    public $timestamps = false;

    protected $fillable = [
        'n_unidad',
        'x_unidad',
        'l_activo',
    ];

    protected $primaryKey = 'n_unidad';
}
