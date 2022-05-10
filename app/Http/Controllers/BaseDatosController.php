<?php

namespace App\Http\Controllers;

use App\Models\BaseDatos;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseDatosController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function index()
    {
        $Sistema['data'] = BaseDatos::join('sicop.mae_motor','mae_motor.n_motor','=', 'mae_bd.n_motor')
                                ->whereIn('mae_bd.l_activo',array('S','N'))
                                ->get(['mae_bd.*','mae_motor.x_motor']);
        return json_encode($Sistema,200);
    }

    public function store(Request $request)
    {
        if($request->x_bd !=''){
            $existe = BaseDatos::where('x_bd',$request->x_bd)->value('n_bd');
            if(!$existe){
                $query = "nextval('sicop.mae_bd_seq') as nxt";
                $next_id = Perfil::selectRaw($query)->value('nxt');
        
                $data = new BaseDatos();
                $data->n_bd = $next_id;
                $data->x_bd = $request->x_bd;
                $data->n_motor = $request->n_motor;
                $data->l_activo = $request->l_activo;
                $data->f_registro = now();
                $data->save();
        
                $data = BaseDatos::where('n_bd',$next_id)->first();
            }else{
                $data = array(
                    'error' => 'Dato duplicado'
                );
            }
        }else{
            $data = array(
                'error' => 'Debe de ingresar los datos requeridos'
            );
        }
        
        return json_encode($data,200);
    }

    public function show($id)
    {
        if($id !=''){
            $data = BaseDatos::where('n_bd',$id)->first();
        }else{
            $data = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($data,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_bd !=''){
            $data = BaseDatos::find($id);
            if($data){        
                $data->x_bd = $request->edit_x_bd;
                $data->n_motor = $request->edit_n_motor;
                $data->l_activo = $request->edit_l_activo;
                $data->f_aud = now();
                $data->b_aud = 'U';
                $data->save();
        
                $data = array(
                    'success' => 'actualizado'
                );
            }else{
                $data = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $data = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($data,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $data= BaseDatos::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mae_esquema 
                where n_bd='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $datos) {
                $conteo_total=$datos->total;
            }

            if($conteo_total==0){
                if($data){        
                    $data->x_bd = $data->x_bd.$id;
                    $data->f_aud = now();
                    $data->b_aud = 'D';
                    $data->l_activo = 'P';
                    $data->save();
            
                    $data= array(
                        'success' => 'actualizado'
                    );
                }else{
                    $data= array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $data= array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') unidad asociado'
                );
            }
        }else{
            $data= array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($data,200);
    }
}
