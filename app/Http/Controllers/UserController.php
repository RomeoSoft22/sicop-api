<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
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
        //$user['data'] = User::whereIn('l_activo',array('S','N'))->orderBy('n_usuario','ASC')->get();
        $user['data'] = User::join('sicop.mae_perfil', 'mae_usuario.n_perfil', '=', 'mae_perfil.n_perfil')
                        ->join('sicop.mae_area', 'mae_usuario.n_area', '=', 'mae_area.n_area')
                        ->whereIn('mae_usuario.l_activo',array('S','N'))
                        ->get(['mae_usuario.*','mae_perfil.x_perfil','mae_area.x_area','mae_area.n_unidad','mae_area.n_dependencia','mae_area.n_entidad']);
        return json_encode($user,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->c_usuario !=''){
            $existe = User::where('c_usuario',$request->c_usuario)->value('c_usuario');
            if(!$existe){
                $query = "nextval('sicop.mae_menu_seq') as nxt";
                $next_id = User::selectRaw($query)->value('nxt');
        
                $user = new User();
                $user->n_usuario = $next_id;
                $user->c_usuario = $request->c_usuario;
                $user->c_clave = bcrypt($request->c_clave);
                $user->x_dni = $request->x_dni;
                $user->x_nombres = $request->x_nombres;
                $user->x_ape_paterno = $request->x_ape_paterno;
                $user->x_ape_materno = $request->x_ape_materno;
                $user->n_perfil = $request->n_perfil;
                $user->n_area = $request->n_area;
                $user->l_activo = $request->l_activo;
                $user->f_registro = now();
                $user->save();
        
                $user = User::where('n_usuario',$next_id)->first();
            }else{
                $user = array(
                    'error' => 'Usuario duplicado'
                );
            }
        }else{
            $user = array(
                'error' => 'Debe de ingresar los campos requeridos'
            );
        }
        
        return json_encode($user,200);
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
            $user = User::where('n_usuario',$id)->first();
        }else{
            $user = array(
                'error' => 'No se puedo realizar la consulta'
            );
        }
        
        return json_encode($user,200);
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
        if($request->edit_n_usuario !=''){
            $user = User::find($id);
            if($user){        
                //$user->c_usuario = $request->edit_c_usuario;
                $user->x_dni = $request->edit_x_dni;
                $user->x_nombres = $request->edit_x_nombres;
                $user->x_ape_paterno = $request->edit_x_ape_paterno;
                $user->x_ape_materno = $request->edit_x_ape_materno;
                $user->n_perfil = $request->edit_n_perfil;
                $user->n_area = $request->edit_n_area;
                $user->f_aud = now();
                $user->b_aud = 'U';
                $user->l_activo = $request->edit_l_activo;
                $user->save();
        
                $user = array(
                    'success' => 'actualizado'
                );
            }else{
                $user = array(
                    'error' => 'No se encontro el ID : '.$id
                );
            }
        }else{
            $user = array(
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($user,200);
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
            $user = User::find($id);

            if($user){        
                $user->c_usuario = $user->c_usuario.$id;
                $user->f_aud = now();
                $user->b_aud = 'D';
                $user->l_activo = 'P';
                $user->save();
        
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
                'error' => 'Debe de ingresar todos los campos'
            );
        }
        
        return json_encode($menu,200);
    }
}
