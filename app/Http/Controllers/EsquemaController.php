<?php

namespace App\Http\Controllers;

use App\Models\Esquema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EsquemaController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    public function index()
    {
        $tabla['data'] = Esquema::join('sicop.mae_bd','mae_esquema.n_bd','=', 'mae_bd.n_bd')
                                ->join('sicop.mae_motor','mae_bd.n_motor','=', 'mae_motor.n_motor')
                                ->whereIn('mae_esquema.l_activo',array('S','N'))
                                ->get(['mae_esquema.*','mae_bd.x_bd','mae_motor.n_motor','mae_motor.x_motor']);
        return json_encode($tabla,200);
    }

    public function store(Request $request)
    {
        if($request->x_esquema !=''){
            $existe = Esquema::where('x_esquema',$request->x_esquema)->value('n_esquema');
            if(!$existe){
                $query = "nextval('sicop.mae_esquema_seq') as nxt";
                $next_id = Esquema::selectRaw($query)->value('nxt');
        
                $tabla= new Esquema();
                $tabla->n_esquema = $next_id;
                $tabla->x_esquema = $request->x_esquema;
                $tabla->n_bd = $request->n_bd;
                $tabla->l_activo = $request->l_activo;
                $tabla->f_registro = now();
                $tabla->save();
        
                $tabla= Esquema::where('n_esquema',$next_id)->first();
            }else{
                $tabla= array(
                    'error' => 'Registro duplicado'
                );
            }
        }else{
            $tabla= array(
                'error' => 'Debe de ingresar el registro'
            );
        }
        
        return json_encode($tabla,200);
    }

    public function show($id)
    {
        if($id !=''){
            $data = Esquema::where('n_esquema',$id)->first();
        }else{
            $data = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($data,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_esquema !=''){
            $tabla= Esquema::find($id);
            if($tabla){        
                $tabla->x_esquema = $request->edit_x_esquema;
                $tabla->n_bd = $request->edit_n_bd;
                $tabla->l_activo = $request->edit_l_activo;
                $tabla->f_aud = now();
                $tabla->b_aud = 'U';
                $tabla->save();
        
                $tabla= array(
                    'success' => 'actualizado'
                );
            }else{
                $tabla= array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $tabla= array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($tabla,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $dato = Esquema::find($id);

            if($dato){        
                $dato->x_esquema = $dato->x_esquema.$id;
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
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($dato,200);
    }
}
