<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Bandeja;
use App\Models\BaseDatos;
use App\Models\Complejidad;
use App\Models\Dependencia;
use App\Models\Entidad;
use App\Models\Esquema;
use App\Models\Impacto;
use App\Models\Motor;
use App\Models\Prioridad;
use App\Models\Sistema;
use App\Models\TipoSistema;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;

class ListarController extends Controller
{
    //FUNCION LISTAR Entidad
    public function listarEntidad()
    {
        $Entidad = Entidad::where('l_activo','S')->get();
        return json_encode($Entidad,200);
    }

    //listar Dependencias Activas
    public function listarDependencia($id)
    {
        $Dependencia = Dependencia::where('n_entidad',$id)
                        ->where('l_activo','S')
                        ->get();
        return json_encode($Dependencia,200);
    }

    //listar Unidad Activas
    public function listarUnidad($id)
    {
        $Unidad = Unidad::where('n_dependencia',$id)
                        ->where('l_activo','S')
                        ->get();
        return json_encode($Unidad,200);
    }

    //listar Área Activas
    public function listarArea($id)
    {
        $Area = Area::where('n_unidad',$id)
                        ->where('l_activo','S')
                        ->get();
        return json_encode($Area,200);
    }

    //listar Prioridad Activas
    public function listarPrioridad()
    {
        $Prioridad = Prioridad::where('l_activo','S')
                        ->get();
        return json_encode($Prioridad,200);
    }

    //listar Impacto Activas
    public function listarImpacto()
    {
        $Impacto = Impacto::where('l_activo','S')
                        ->get();
        return json_encode($Impacto,200);
    }

    //listar Complejidad Activas
    public function listarComplejidad()
    {
        $Complejidad = Complejidad::where('l_activo','S')
                        ->get();
        return json_encode($Complejidad,200);
    }

    //listar Complejidad Activas
    public function listarBandeja()
    {
        $bandeja = Bandeja::where('l_activo','S')
                        ->get();
        return json_encode($bandeja,200);
    }

    //listar Complejidad Activas
    public function listarUsuario()
    {
        $user = User::where('l_activo','S')
                        ->get();
        return json_encode($user,200);
    }


    //listar Sistemas Activas
    public function listarSistemas()
    {
        $sistema = Sistema::where('l_activo','S')
                        ->get();
        return json_encode($sistema,200);
    }


    //listar Sistemas Activas
    public function listarTipoSistemas()
    {
        $sistema = TipoSistema::where('l_activo','S')
                        ->get();
        return json_encode($sistema,200);
    }

    //listar Sistemas Activas
    public function listarMotor()
    {
        $motor = Motor::where('l_activo','S')
                        ->get();
        return json_encode($motor,200);
    }

    //listar Sistemas Activas
    public function listarBD($id)
    {
        $bd = BaseDatos::where('n_motor',$id)
                        ->where('l_activo','S')
                        ->get();
        return json_encode($bd,200);
    }


    //listar Sistemas Activas
    public function listarEsquema($id)
    {
        $bd = Esquema::where('n_bd',$id)
                        ->where('l_activo','S')
                        ->get();
        return json_encode($bd,200);
    }
}
