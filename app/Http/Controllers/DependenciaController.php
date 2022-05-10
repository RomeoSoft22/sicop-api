<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DependenciaController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Dependencia['data'] = Dependencia::join('sicop.mae_entidad','mae_dependencia.n_entidad','=', 'mae_entidad.n_entidad')
                                ->whereIn('mae_dependencia.l_activo',array('S','N'))
                                ->get(['mae_dependencia.*','mae_entidad.x_entidad']);
        return json_encode($Dependencia,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->x_dependencia !=''){
            $existe = Dependencia::where('x_dependencia',$request->x_dependencia)->value('n_dependencia');
            if(!$existe){
                $query = "nextval('sicop.mae_dependencia_seq') as nxt";
                $next_id = Dependencia::selectRaw($query)->value('nxt');
        
                $dependencia = new Dependencia();
                $dependencia->n_dependencia = $next_id;
                $dependencia->x_dependencia = $request->x_dependencia;
                $dependencia->n_entidad = $request->n_entidad;
                $dependencia->l_activo = $request->l_activo;
                $dependencia->f_registro = now();
                $dependencia->save();
        
                $dependencia = Dependencia::where('n_dependencia',$next_id)->first();
            }else{
                $dependencia = array(
                    'error' => 'Dependencia duplicado'
                );
            }
        }else{
            $dependencia = array(
                'error' => 'Debe de ingresar la Dependencia'
            );
        }
        
        return json_encode($dependencia,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if($id !=''){
            $Dependencia = Dependencia::where('n_dependencia',$id)->first();
        }else{
            $Dependencia = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($Dependencia,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($request->edit_n_dependencia !=''){
            $dependencia = Dependencia::find($id);
            if($dependencia){        
                $dependencia->x_dependencia = $request->edit_x_dependencia;
                $dependencia->n_entidad = $request->edit_n_entidad;
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id !=''){
            $entidad = Dependencia::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mae_unidad 
                where n_unidad='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($entidad){        
                    $entidad->x_dependencia = $entidad->x_dependencia.$id;
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
