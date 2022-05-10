<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        //
        $perfil['data'] = Menu::whereIn('l_activo',array('S','N'))->orderBy('n_menu','ASC')->get();
        return json_encode($perfil,200);
    }

    public function store(Request $request)
    {
        if($request->x_menu !=''){
            $existe = Menu::where('x_menu',$request->x_menu)->value('n_menu');
            if(!$existe){
                $query = "nextval('sicop.mae_menu_seq') as nxt";
                $next_id = Menu::selectRaw($query)->value('nxt');
        
                $perfil = new Menu();
                $perfil->n_menu = $next_id;
                $perfil->x_menu = $request->x_menu;
                $perfil->x_url = $request->x_url;
                $perfil->x_url_page = $request->x_url_page;
                $perfil->x_icono = $request->x_icono;
                $perfil->n_orden = $request->n_orden;
                $perfil->n_nivel = $request->n_nivel;
                $perfil->l_activo = $request->l_activo;
                $perfil->f_registro = now();
                $perfil->save();
        
                $perfil = Menu::where('n_menu',$next_id)->first();
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

    public function show($id)
    {
        if($id !=''){
            $perfil = Menu::where('n_menu',$id)->first();
        }else{
            $perfil = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($perfil,200);
    }

    public function update(Request $request, $id)
    {
        if($request->edit_n_menu !=''){
            $menu = Menu::find($id);
            if($menu){        
                $menu->x_menu = $request->edit_x_menu;
                $menu->x_url = $request->edit_x_url;
                $menu->x_url_page = $request->edit_x_url_page;
                $menu->x_icono = $request->edit_x_icono;
                $menu->n_orden = $request->edit_n_orden;
                $menu->n_nivel = $request->edit_n_nivel;
                $menu->l_activo = $request->edit_l_activo;
                $menu->f_aud = now();
                $menu->b_aud = 'U';
                $menu->save();
        
                $menu = array(
                    'success' => 'actualizado'
                );
            }else{
                $menu = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $menu = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($menu,200);
    }

    public function destroy($id)
    {
        if($id !=''){
            $menu = Menu::find($id);

            $menu_existe = DB::select("
                select count(mp.*) as total
                from sicop.mov_menu_perfil mp 
                inner join sicop.mae_menu as m ON (mp.n_menu=m.n_menu)
                where mp.n_menu='".$id."' and m.l_activo='S' and mp.l_activo='S'
            ");
            $menu_existe = (json_encode($menu_existe));
            $total = json_decode($menu_existe);
            foreach ($total as $data) {
                $conteo_total=$data->total;
            }

            if($conteo_total==0){
                if($menu){        
                    $menu->f_aud = now();
                    $menu->b_aud = 'D';
                    $menu->l_activo = 'P';
                    $menu->save();
            
                    $menu = array(
                        'success' => 'actualizado'
                    );
                }else{
                    $menu = array(
                        'error' => 'No se elimino el ID : '.$id
                    );
                }
            }else{
                $menu = array(
                    'error' => 'No se elimino el ID : '.$id.', porque existen ('.$conteo_total.') opciones de menu asociado'
                );
            }
        }else{
            $menu = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($menu,200);
    }


    //lista las opciones del menu del index
    public function menuListar($id)
    {
        $list_menu = DB::select("
            select mp.n_menu_perfil, mp.n_menu, m.x_menu, m.x_url, m.x_url_page, m.x_icono, m.n_orden, m.n_nivel, 
            (select count(mm.n_nivel) from sicop.mae_menu as mm where mm.n_nivel=m.n_menu and mm.l_activo='S') as sub_menu, mp.l_activo
            from sicop.mov_menu_perfil mp 
            inner join sicop.mae_menu as m ON (mp.n_menu=m.n_menu)
            where mp.n_perfil='".$id."' and m.l_activo='S' and mp.l_activo='S'
            order by m.n_orden asc
        ");

        return $list_menu;
    }

}
