<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_dependencia';
    public $timestamps = false;

    protected $fillable = [
        'n_dependencia',
        'n_entidad',
        'x_dependencia',
        'l_activo',
    ];

    protected $primaryKey = 'n_dependencia';
}
