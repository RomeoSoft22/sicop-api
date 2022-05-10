<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impacto extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_impacto';
    public $timestamps = false;

    protected $fillable = [
        'n_impacto',
        'x_impacto',
        'l_activo',
    ];

    protected $primaryKey = 'n_impacto';
}
