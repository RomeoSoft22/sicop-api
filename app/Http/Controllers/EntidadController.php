<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntidadController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    public function index()
    {
        $Entidad['data'] = Entidad::whereIn('l_activo',array('S','N'))->get();
        return json_encode($Entidad,200);
    }

    public function store(Request $request)
    {
        if($request->x_entidad !=''){
            $existe = Entidad::where('x_entidad',$request->x_entidad)->value('n_entidad');
            if(!$existe){
                $query = "nextval('sicop.mae_entidad_seq') as nxt";
                $next_id = Entidad::selectRaw($query)->value('nxt');
        
                $entidad = new Entidad();
                $entidad->n_entidad = $next_id;
                $entidad->x_entidad = $request->x_entidad;
                $entidad->l_activo = $request->l_activo;
                $entidad->f_registro = now();
                $entidad->save();
        
                $entidad = Entidad::where('n_entidad',$next_id)->first();
            }else{
                $entidad = array(
                    'error' => 'Entidad duplicado'
                );
            }
        }else{
            $entidad = array(
                'error' => 'Debe de ingresar la Entidad'
            );
        }
        
        return json_encode($entidad,200);
    }

    public function show($id)
    {
        if($id !=''){
            $entidad = Entidad::where('n_entidad',$id)->first();
        }else{
            $entidad = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($entidad,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_entidad !=''){
            $entidad = Entidad::find($id);
            if($entidad){        
                $entidad->x_entidad = $request->edit_x_entidad;
                $entidad->l_activo = $request->edit_l_activo;
                $entidad->f_aud = now();
                $entidad->b_aud = 'U';
                $entidad->save();
        
                $entidad = array(
                    'success' => 'actualizado'
                );
            }else{
                $entidad = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $entidad = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($entidad,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $entidad = Entidad::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mae_dependencia 
                where n_entidad='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($entidad){        
                    $entidad->x_entidad = $entidad->x_entidad.$id;
                    $entidad->f_aud = now();
                    $entidad->b_aud = 'D';
                    $entidad->l_activo = 'P';
                    $entidad->save();
            
                    $entidad = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $entidad = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $entidad = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') dependencias asociado'
                );
            }
        }else{
            $entidad = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($entidad,200);
    }


}
