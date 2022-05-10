<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use App\Models\TipoSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoSistemaController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    
    public function index()
    {
        $data['data'] = TipoSistema::whereIn('l_activo',array('S','N'))->get();
        return json_encode($data,200);
    }

    public function store(Request $request)
    {
        if($request->x_tiposistema !=''){
            $existe = TipoSistema::where('x_tiposistema',$request->x_tiposistema)->value('n_tiposistema');
            if(!$existe){
                $query = "nextval('sicop.mae_tiposistema_seq') as nxt";
                $next_id = Perfil::selectRaw($query)->value('nxt');
        
                $data = new TipoSistema();
                $data->n_tiposistema = $next_id;
                $data->x_tiposistema = $request->x_tiposistema;
                $data->l_activo = $request->l_activo;
                $data->f_registro = now();
                $data->save();
        
                $data = TipoSistema::where('n_tiposistema',$next_id)->first();
            }else{
                $data = array(
                    'error' => 'Dato duplicado'
                );
            }
        }else{
            $data = array(
                'error' => 'Debe de ingresar todos los campos requeridos'
            );
        }
        
        return json_encode($data,200);
    }

    public function show($id)
    {
        if($id !=''){
            $data = TipoSistema::where('n_tiposistema',$id)->first();
        }else{
            $data = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($data,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_tiposistema !=''){
            $data = TipoSistema::find($id);
            if($data){        
                $data->x_tiposistema = $request->edit_x_tiposistema;
                $data->f_aud = now();
                $data->b_aud = 'U';
                $data->l_activo = $request->edit_l_activo;
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
            $update = TipoSistema::find($id);

            $menu_existe = DB::select("
                select count(ms.*) as total
                from sicop.mae_sistema ms
                where ms.n_tiposistema='".$id."' and ms.l_activo='S'	
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($update){      
                    $update->x_tiposistema = $update->x_tiposistema.$id;  
                    $update->f_aud = now();
                    $update->b_aud = 'D';
                    $update->l_activo = 'P';
                    $update->save();
            
                    $update = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $update = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $update = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') sistemas asociado'
                );
            }
        }else{
            $update = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($update,200);
    }
}
