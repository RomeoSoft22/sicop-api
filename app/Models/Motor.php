<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motor extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_motor';
    public $timestamps = false;

    protected $fillable = [
        'n_motor',
        'x_motor',
        'l_activo',
    ];

    protected $primaryKey = 'n_motor';
}
