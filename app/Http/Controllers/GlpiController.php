<?php

namespace App\Http\Controllers;

use App\Models\Glpi;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlpiController extends Controller
{
    //Controlar por JWT
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    
    public function index()
    {
        $Glpi['data'] = Glpi::join('sicop.mae_prioridad','mov_glpi.n_prioridad','=','mae_prioridad.n_prioridad')
                        ->join('sicop.mae_impacto','mov_glpi.n_impacto','=','mae_impacto.n_impacto')
                        ->join('sicop.mae_complejidad','mov_glpi.n_complejidad','=','mae_complejidad.n_complejidad')
                        ->whereIn('mov_glpi.l_activo',array('S','N'))
                        ->get(['mov_glpi.*','mae_prioridad.x_prioridad','mae_impacto.x_impacto','mae_complejidad.x_complejidad']);
        return json_encode($Glpi,200);
    }

    public function store(Request $request)
    {
        if($request->c_glpi !=''){
            $existe = Glpi::where('c_glpi',$request->c_glpi)->value('n_glpi');
            if(!$existe){
                $query = "nextval('sicop.mov_glpi_seq') as nxt";
                $next_id = Perfil::selectRaw($query)->value('nxt');
        
                $glpi = new Glpi();
                $glpi->n_glpi = $next_id;
                $glpi->c_glpi = $request->c_glpi;
                $glpi->x_glpi = $request->x_glpi;
                $glpi->n_prioridad = $request->n_prioridad;
                $glpi->n_impacto = $request->n_impacto;
                $glpi->n_complejidad = $request->n_complejidad;
                $glpi->l_activo = $request->l_activo;
                $glpi->f_registro = now();
                $glpi->save();
        
                $glpi = Glpi::where('n_glpi',$next_id)->first();
            }else{
                $glpi = array(
                    'error' => 'Dato duplicado'
                );
            }
        }else{
            $glpi = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($glpi,200);
    }

    public function show($id)
    {
        if($id !=''){
            $perfil = Glpi::where('c_glpi',$id)->first();
            if($perfil==''){
                $perfil = array(
                    'error' => 'No se encontraron datos'
                );
            }
        }else{
            $perfil = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($perfil,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_glpi !=''){
            $glpi = Glpi::find($id);
            if($glpi){        
                $glpi->c_glpi = $request->edit_c_glpi;
                $glpi->x_glpi = $request->edit_x_glpi;
                $glpi->n_prioridad = $request->edit_n_prioridad;
                $glpi->n_impacto = $request->edit_n_impacto;
                $glpi->n_complejidad = $request->edit_n_complejidad;
                $glpi->f_aud = now();
                $glpi->b_aud = 'U';
                $glpi->l_activo = $request->edit_l_activo;
                $glpi->save();
        
                $glpi = array(
                    'success' => 'actualizado'
                );
            }else{
                $glpi = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $glpi = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($glpi,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $glpi = Glpi::find($id);

            $menu_existe = DB::select("
            select count(ag.*) as total
            from sicop.mov_asigna_glpi ag 
            where ag.n_glpi='".$id."' 
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($glpi){        
                    $glpi->f_aud = now();
                    $glpi->b_aud = 'D';
                    $glpi->l_activo = 'P';
                    $glpi->save();
            
                    $glpi = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $glpi = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $glpi = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') GLPI asociado'
                );
            }
        }else{
            $glpi = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($glpi,200);
    }
}
