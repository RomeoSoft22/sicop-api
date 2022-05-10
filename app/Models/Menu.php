<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'sicop.mae_menu';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'n_menu',
        'x_menu',
        'x_url',
        'x_icono',
        'n_orden',
        'n_nivel',
        'l_activo',
    ];

    protected $primaryKey = 'n_menu';


}
