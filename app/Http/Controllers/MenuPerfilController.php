<?php

namespace App\Http\Controllers;

use App\Models\MenuPerfil;
use Illuminate\Http\Request;

class MenuPerfilController extends Controller
{
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->x_menu !=''){
            $existe = MenuPerfil::where('x_menu',$request->x_menu)->value('n_menu');
            if(!$existe){
                /*$query = "nextval('sicop.mae_menu_seq') as nxt";
                $next_id = MenuPerfil::selectRaw($query)->value('nxt');
        
                $perfil = new MenuPerfil();
                $perfil->n_menu = $next_id;
                $perfil->x_menu = $request->x_menu;
                $perfil->x_url = $request->x_url;
                $perfil->x_url_page = $request->x_url_page;
                $perfil->x_icono = $request->x_icono;
                $perfil->n_orden = $request->n_orden;
                $perfil->n_nivel = $request->n_nivel;
                $perfil->l_activo = $request->l_activo;
                $perfil->f_registro = now();
                $perfil->save();*/
        
                $perfil = array(
                    'success' => 'actualizado'
                );
            }else{
                $perfil = array(
                    'error' => 'Menu duplicado'
                );
            }
        }else{
            $perfil = array(
                'error' => 'Debe de ingresar el Menu'
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
        //
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
        if($request->edit_n_menu !=''){
            for($i=1;$i<=$request->edit_total;$i++){
                $menu_opcion = $request->input('p'.$i);
                $menu_opcion = ($menu_opcion=="on")?'S':'N';

                //realizamos busqueda del item
                $menu = MenuPerfil::where('n_perfil',$id)->where('n_menu',$i)->value('n_perfil');
                //consultamos ID para UPDATE
                $menus = MenuPerfil::where('n_perfil',$id)->where('n_menu',$i)->first();

                //insertamos existencia del item
                if($menu=="" || $menu=="0"){
                    $query = "nextval('sicop.mov_menu_perfil_seq') as nxt";
                    $next_id = MenuPerfil::selectRaw($query)->value('nxt');

                    $Menuperfil = new MenuPerfil();
                    $Menuperfil->n_menu_perfil = $next_id;
                    $Menuperfil->n_menu = $i;
                    $Menuperfil->n_perfil = $id;
                    $Menuperfil->l_activo = $menu_opcion;
                    $Menuperfil->f_registro = now();
                    $Menuperfil->save();

                    $menu = array(
                        'success' => 'nuevo'
                    );
                    
                }
                //actualizamos la opcion
                else{
                    $menus->l_activo = $menu_opcion;
                    $menus->f_aud = now();
                    $menus->b_aud = 'U';
                    $menus->save();

                    $menu = array(
                        'success' => 'actualizado'
                    );
                }
    
            }
        }else{
            $menu = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($menu,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
