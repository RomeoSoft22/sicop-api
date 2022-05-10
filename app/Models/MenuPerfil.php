<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPerfil extends Model
{
    use HasFactory;
    protected $table = 'sicop.mov_menu_perfil';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'n_menu_perfil',
        'n_menu',
        'n_perfil',
        'l_activo',
    ];

    protected $primaryKey = 'n_menu_perfil';


}
