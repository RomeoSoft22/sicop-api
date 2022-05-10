<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MotorController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    public function index()
    {
        $motor['data'] = Motor::whereIn('l_activo',array('S','N'))->get();
        return json_encode($motor,200);
    }

    public function store(Request $request)
    {
        if($request->x_motor !=''){
            $existe = Motor::where('x_motor',$request->x_motor)->value('n_motor');
            if(!$existe){
                $query = "nextval('sicop.mae_motor_seq') as nxt";
                $next_id = Motor::selectRaw($query)->value('nxt');
        
                $motor = new Motor();
                $motor->n_motor = $next_id;
                $motor->x_motor = $request->x_motor;
                $motor->l_activo = $request->l_activo;
                $motor->f_registro = now();
                $motor->save();
        
                $motor = Motor::where('n_motor',$next_id)->first();
            }else{
                $motor = array(
                    'error' => 'Registro duplicado'
                );
            }
        }else{
            $motor = array(
                'error' => 'Debe de ingresar todos los datos'
            );
        }
        
        return json_encode($motor,200);
    }

    public function show($id)
    {
        if($id !=''){
            $motor = Motor::where('n_motor',$id)->first();
        }else{
            $motor = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($motor,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_motor !=''){
            $motor = Motor::find($id);
            if($motor){        
                $motor->x_motor = $request->edit_x_motor;
                $motor->l_activo = $request->edit_l_activo;
                $motor->f_aud = now();
                $motor->b_aud = 'U';
                $motor->save();
        
                $motor = array(
                    'success' => 'actualizado'
                );
            }else{
                $motor = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $motor = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($motor,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $motor = Motor::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mae_bd 
                where n_motor='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($motor){        
                    $motor->x_motor = $motor->x_motor.$id;
                    $motor->f_aud = now();
                    $motor->b_aud = 'D';
                    $motor->l_activo = 'P';
                    $motor->save();
            
                    $motor = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $motor = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $motor = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') dependencias asociado'
                );
            }
        }else{
            $motor = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($motor,200);
    }
}
