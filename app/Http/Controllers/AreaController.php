<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
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
        $area['data'] = Area::join('sicop.mae_entidad','mae_area.n_entidad','=', 'mae_entidad.n_entidad')
                                ->join('sicop.mae_dependencia','mae_area.n_dependencia','=', 'mae_dependencia.n_dependencia')
                                ->join('sicop.mae_unidad','mae_area.n_unidad','=', 'mae_unidad.n_unidad')
                                ->whereIn('mae_area.l_activo',array('S','N'))
                                ->get(['mae_area.*','mae_entidad.x_entidad','mae_dependencia.x_dependencia','mae_unidad.x_unidad']);
        return json_encode($area,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->x_area !=''){
            $existe = Area::where('x_area',$request->x_area)->value('n_area');
            if(!$existe){
                $query = "nextval('sicop.mae_area_seq') as nxt";
                $next_id = Area::selectRaw($query)->value('nxt');
        
                $data = new Area();
                $data->n_area = $next_id;
                $data->x_area = $request->x_area;
                $data->n_unidad = $request->n_unidad;
                $data->n_dependencia = $request->n_dependencia;
                $data->n_entidad = $request->n_entidad;
                $data->l_activo = $request->l_activo;
                $data->f_registro = now();
                $data->save();
        
                $data = Area::where('n_area',$next_id)->first();
            }else{
                $data = array(
                    'error' => 'Registro duplicado'
                );
            }
        }else{
            $data = array(
                'error' => 'Debe de ingresar el registro'
            );
        }
        
        return json_encode($data,200);
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
            $data = Area::where('n_area',$id)->first();
        }else{
            $data = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($data,200);
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
        if($request->edit_n_area !=''){
            $data = Area::find($id);
            if($data){        
                $data->x_area = $request->edit_x_area;
                $data->n_unidad = $request->edit_n_unidad;
                $data->n_dependencia = $request->edit_n_dependencia;
                $data->n_entidad = $request->edit_n_entidad;
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id !=''){
            $dato = Area::find($id);

            $menu_existe = DB::select("
                select count(*) as total
                from sicop.mae_usuario 
                where n_area='".$id."' and l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($dato){        
                    $dato->x_area = $dato->x_area.$id;
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
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') usuarios asociado'
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
