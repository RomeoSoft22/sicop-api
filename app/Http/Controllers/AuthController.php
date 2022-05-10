<?php

namespace App\Http\Controllers;

use App\Models\User as ModelsUser;
use App\Models\Menu;
use App\Models\MenuPerfil;
use App\Models\Perfil;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }



    public function username()
    {
        return 'c_usuario';
    }
    public function userpass()
    {
        return 'c_clave';
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        //declaramos las variables 
        $usuario = $request->c_usuario;
        $clave = $request->c_clave;

        $credentials = $this->validate(request(),         
        [
            $this->username() => 'required|string', 
            $this->userpass() => 'required|string'
        ]);
        

        //consulta si viene desde el SIJ
        /*if($request->tipo=="SIJ"){
            
            //validar Acceso al SIJ
            $valida = $this->validaAccesoSIJ($usuario,$clave);
            
            //conexion correcta
            if($valida=="OK"){
                
                //se consultan datos del usuario
                $user_data = ConsutaSIJController::ConsultaUsuario($usuario);
                $user_data = json_decode($user_data);
                $x_nombres="";
                $x_ape_paterno="";
                $x_ape_materno="";
                $x_dni="";        

                foreach ($user_data as $data) {
                    $x_nombres=$data->c_nombres;
                    $x_ape_paterno=$data->c_ape_paterno;
                    $x_ape_materno=$data->c_ape_materno;
                    $x_dni=$data->c_dni;
                }

                //validar si existe usuario
                $consulta = DB::table('reportes.mae_usuario')->where('c_usuario',$usuario)->first();
                $user = ModelsUser::where('c_usuario',$usuario)->first();

                //validacion del usuario ACTIVO
                if($user->l_activo!='S'){
                    return response([
                        'status' => 'error',
                        'error' => 'invalid.credentials',
                        'msg' => 'Invalid Credentials.'
                    ], 401);
                }
                
                
                if($consulta != null){
                    $clave='123456';
                    //actualiza fecha de ingreso
                    $user->f_ultimo_ing = now();
                    $user->f_aud = now();
                    $user->c_aud_uidred = $usuario;
                    $user->b_aud = 'U';
                    $user->save();
                }else{

                    $query = "nextval('reportes.mae_usuario_seq') as nxt";
                    $next_rid = ModelsUser::selectRaw($query)->value('nxt');

                    //$next_rid = DB::select("select nextval('reportes.mae_usuario_seq') as nxt");
                    
                    $user = new ModelsUser();
                    $user->n_usuario = $next_rid;
                    $user->n_perfil = 2;
                    $user->c_usuario = $usuario;
                    $user->c_clave = bcrypt('123456');
                    $user->x_nombres = $x_nombres;
                    $user->x_ape_paterno = $x_ape_paterno;
                    $user->x_ape_materno = $x_ape_materno;
                    $user->x_dni = $x_dni;
                    $user->l_activo = 'S';

                    //dd($user->getAttributes());
                    
                    $user->save();

                }
            }else{
                return $valida;
            }
        }*/
       
        
        if (!$token = JWTAuth::attempt(['c_usuario' => $usuario, 'password' => $clave])) {
            return response([
                'status' => 'error',
                'error' => 'invalid.credentials',
                'msg' => 'Invalid Credentials.'
            ], 401);
        }

        return $this->respondWithToken($token);



    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => Auth::user(),
            'perfil' => Perfil::where('n_perfil',Auth::user()->n_perfil)->first(),
            'menu' => MenuController::show(Auth::user()->n_perfil),
        ]);
    }





     /**
     * Encriptación de CLAVE DEL SIJ 
     */
    protected function encriptarClaveSIJ($as_clave ){

        $li_len=0;
        $i=0;
        $li_caracter=0;
        $li_resta=0;
		$ls_nueva_clave='';
        $ls_caracter='';

		$ls_nueva_clave ="";
		$li_len = strlen($as_clave);
		
		for ($i=0; $i<$li_len;$i++){
			$ls_caracter = substr(substr($as_clave, $i, $li_len),0,1);//ls_caracter = left(mid(as_clave,i,li_len),1)
						
			$character = ord($ls_caracter); //Asc
			$li_caracter = $character;
			 
			if (is_int($ls_caracter)){
				if (($li_caracter > 47) && ($li_caracter < 58)){ 
                    $li_resta = $li_caracter - 47;
                    $li_resta = 64 + $li_resta;
				}
			}
			else{
				if (($li_caracter >= 65) && ($li_caracter <= 75)){
					$li_resta = $li_caracter - 64;
					$li_resta = 47 + $li_resta;
				}
				else if (($li_caracter >= 76) && ($li_caracter <= 86)){
					$li_resta = $li_caracter - 75;
					$li_resta = 47 + $li_resta;
				}
				else if (($li_caracter >= 87) && ($li_caracter <= 90)){
					$li_resta = $li_caracter - 86;
					$li_resta = 47 + $li_resta;
				}
				else{
					//si es un simbolo 
                    $li_resta = $li_caracter + 17;
				}
			}

            
			$ls_nueva_clave .= chr($li_resta);//char
		} 
		
		return $ls_nueva_clave;

    }





    /****
     * VALIDAR CONEXION DE USUARIO AL SIJ
     */
    public function validaAccesoSIJ($db_user,$db_pass){

        
        try{

            $conexion = new PDO('odbc:Driver={SQL Anywhere 11};DatabaseName='.env('DB_ODBC_DATABASE').';ServerName='.env('DB_ODBC_SERVICE').';CommLinks=tcpip(host='.env('DB_ODBC_HOST').':'.env('DB_ODBC_PORT').');uid='.$db_user.';pwd='.$this->encriptarClaveSIJ($db_pass).'');
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $mensaje = 'OK';
            return $mensaje;

        }catch(PDOException $error){

            $mensaje = substr($error->getMessage(),9,5);

            if($mensaje == "28000"){
                $mensaje = "Invalido Usuario o Clave";
            }
            if($mensaje == "08001"){
                $detalle = substr($error->getMessage(),34,5);
                
                if($detalle == -83){
                    $mensaje = "No se encontró la base de datos especificada";
                }

                if($detalle == -100){
                    $mensaje = "No se encontró el servidor de la base de datos";
                }
            }
            
            return response()->json([
                'access_token' => '',
                'error' => $mensaje,
            ]);

        }
        
        
    }


}

