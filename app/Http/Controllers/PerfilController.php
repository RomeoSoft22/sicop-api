<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //$perfil['data'] = Perfil::all();
        $perfil['data'] = Perfil::whereIn('l_activo',array('S','N'))->get();
        return json_encode($perfil,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->x_perfil !=''){
            $existe = Perfil::where('x_perfil',$request->x_perfil)->value('n_perfil');
            if(!$existe){
                $query = "nextval('sicop.mae_perfil_seq') as nxt";
                $next_id = Perfil::selectRaw($query)->value('nxt');
        
                $perfil = new Perfil();
                $perfil->n_perfil = $next_id;
                $perfil->x_perfil = $request->x_perfil;
                $perfil->l_activo = $request->l_activo;
                $perfil->f_registro = now();
                $perfil->save();
        
                $perfil = Perfil::where('n_perfil',$next_id)->first();
            }else{
                $perfil = array(
                    'error' => 'Perfil duplicado'
                );
            }
        }else{
            $perfil = array(
                'error' => 'Debe de ingresar el Perfil'
            );
        }
        
        return json_encode($perfil,200);
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
            $perfil = Perfil::where('n_perfil',$id)->first();
        }else{
            $perfil = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($perfil,200);
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
        if($request->edit_n_perfil !=''){
            $perfil = Perfil::find($id);
            if($perfil){        
                $perfil->x_perfil = $request->edit_x_perfil;
                $perfil->f_aud = now();
                $perfil->b_aud = 'U';
                $perfil->l_activo = $request->edit_l_activo;
                $perfil->save();
        
                $perfil = array(
                    'success' => 'actualizado'
                );
            }else{
                $perfil = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $perfil = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($perfil,200);
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
            $perfil = Perfil::find($id);

            $menu_existe = DB::select("
                select count(mp.*) as total
                from sicop.mov_menu_perfil mp 
                inner join sicop.mae_menu as m ON (mp.n_menu=m.n_menu)
                where mp.n_perfil='".$id."' and m.l_activo='S' and mp.l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($perfil){        
                    $perfil->f_aud = now();
                    $perfil->b_aud = 'D';
                    $perfil->l_activo = 'P';
                    $perfil->save();
            
                    $perfil = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $perfil = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $perfil = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') opciones de menu asociado'
                );
            }
        }else{
            $perfil = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($perfil,200);
    }




    public function perfilActivo()
    {
        //muestra solo los perfiles activos
        $perfil = Perfil::where('l_activo','S')->get();
        return json_encode($perfil,200);
    }
}
