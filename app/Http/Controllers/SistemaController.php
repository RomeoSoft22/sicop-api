<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SistemaController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function index()
    {
        $Sistema['data'] = Sistema::join('sicop.mae_tiposistema','mae_tiposistema.n_tiposistema','=', 'mae_sistema.n_tiposistema')
                                ->whereIn('mae_sistema.l_activo',array('S','N'))
                                ->get(['mae_sistema.*','mae_tiposistema.x_tiposistema']);
        return json_encode($Sistema,200);
    }

    public function store(Request $request)
    {
        if($request->x_sistema !=''){
            $existe = Sistema::where('x_sistema',$request->x_sistema)->value('n_sistema');
            if(!$existe){
                $query = "nextval('sicop.mae_sistema_seq') as nxt";
                $next_id = Perfil::selectRaw($query)->value('nxt');
        
                $data = new Sistema();
                $data->n_sistema = $next_id;
                $data->x_sistema = $request->x_sistema;
                $data->n_tiposistema = $request->n_tiposistema;
                $data->l_activo = $request->l_activo;
                $data->f_registro = now();
                $data->save();
        
                $data = Sistema::where('n_sistema',$next_id)->first();
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
            $Dependencia = Sistema::where('n_sistema',$id)->first();
        }else{
            $Dependencia = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($Dependencia,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_sistema !=''){
            $dependencia = Sistema::find($id);
            if($dependencia){        
                $dependencia->x_sistema = $request->edit_x_sistema;
                $dependencia->n_tiposistema = $request->edit_n_tiposistema;
                $dependencia->l_activo = $request->edit_l_activo;
                $dependencia->f_aud = now();
                $dependencia->b_aud = 'U';
                $dependencia->save();
        
                $dependencia = array(
                    'success' => 'actualizado'
                );
            }else{
                $dependencia = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $dependencia = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($dependencia,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $entidad = Sistema::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mov_asignar 
                where n_sistema='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($entidad){        
                    $entidad->x_sistema = $entidad->x_sistema.$id;
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
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') unidad asociado'
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
