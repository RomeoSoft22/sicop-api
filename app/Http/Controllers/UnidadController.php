<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnidadController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    public function index()
    {
        $Unidad['data'] = Unidad::join('sicop.mae_entidad','mae_unidad.n_entidad','=', 'mae_entidad.n_entidad')
                                ->join('sicop.mae_dependencia','mae_unidad.n_dependencia','=', 'mae_dependencia.n_dependencia')
                                ->whereIn('mae_unidad.l_activo',array('S','N'))
                                ->get(['mae_unidad.*','mae_entidad.x_entidad','mae_dependencia.x_dependencia']);
        return json_encode($Unidad,200);
    }

    public function store(Request $request)
    {
        if($request->x_unidad !=''){
            $existe = Unidad::where('x_unidad',$request->x_unidad)->value('n_unidad');
            if(!$existe){
                $query = "nextval('sicop.mae_unidad_seq') as nxt";
                $next_id = Unidad::selectRaw($query)->value('nxt');
        
                $unidad = new Unidad();
                $unidad->n_unidad = $next_id;
                $unidad->x_unidad = $request->x_unidad;
                $unidad->n_dependencia = $request->n_dependencia;
                $unidad->n_entidad = $request->n_entidad;
                $unidad->l_activo = $request->l_activo;
                $unidad->f_registro = now();
                $unidad->save();
        
                $unidad = Unidad::where('n_unidad',$next_id)->first();
            }else{
                $unidad = array(
                    'error' => 'Registro duplicado'
                );
            }
        }else{
            $unidad = array(
                'error' => 'Debe de ingresar el registro'
            );
        }
        
        return json_encode($unidad,200);
    }

    public function show($id)
    {
        if($id !=''){
            $data = Unidad::where('n_unidad',$id)->first();
        }else{
            $data = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($data,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_unidad !=''){
            $unidad = Unidad::find($id);
            if($unidad){        
                $unidad->x_unidad = $request->edit_x_unidad;
                $unidad->n_dependencia = $request->edit_n_dependencia;
                $unidad->n_entidad = $request->edit_n_entidad;
                $unidad->l_activo = $request->edit_l_activo;
                $unidad->f_aud = now();
                $unidad->b_aud = 'U';
                $unidad->save();
        
                $unidad = array(
                    'success' => 'actualizado'
                );
            }else{
                $unidad = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $unidad = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($unidad,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $dato = Unidad::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mae_area 
                where n_unidad='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($dato){        
                    $dato->x_unidad = $dato->x_unidad.$id;
                    $dato->f_aud = now();
                    $dato->b_aud = 'D';
                    $dato->l_activo = 'P';
                    $dato->save();
            
                    $dato = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $dato = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $dato = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') área asociado'
                );
            }
        }else{
            $dato = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($dato,200);
    }
}
