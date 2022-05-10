<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_area';
    public $timestamps = false;

    protected $fillable = [
        'n_area',
        'x_area',
        'l_activo',
    ];

    protected $primaryKey = 'n_area';
}
