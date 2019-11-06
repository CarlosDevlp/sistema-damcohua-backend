<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterAuthRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\GlobalConstants;
use App\TipoUsuario;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public  $loginAfterSignUp = true;

	public  function  register(Request  $request) {
		
		//verificando que el usuario registrado no exista
		if(User::where('username',$request->surname)->count()==0){
			$user = new  User();
			$user->name = $request->name;
			$user->username = $request->username;
			$user->surname = $request->username;
			$user->email = $request->email;
			$user->tipo_usuario_id= GlobalConstants::$ROL_SOLO_LECTURA;
			$user->password = bcrypt($request->password);
			$user->save();
			
			//logearse después de registrarse
			if ($this->loginAfterSignUp) {
				return  $this->login($request);
			}
		}else{	//si existe sucede lo siguiente
			return  response()->json([
				'status' => 'username_duplicated',
				'message' => 'El nombre de usuario ingresado ya existe'
			], 200);	
		}
		

		return  response()->json([
			'status' => 'ok',
			'data' => $user
		], 200);
	}

	public  function  login(Request  $request) {
		$input = $request->only('surname', 'password');
		$jwt_token = null;
		if (!$jwt_token = JWTAuth::attempt($input)) { //email o password inválido
			return  response()->json([
				'status' => 'invalid_credentials',
				'message' => 'Correo o contraseña no válidos.',
			], 401);
		}
		$usuario_empleado=DB::table('empleados')
							->select('*',DB::raw('empleados.id as empleados_id, personas.id as personas_id, usuarios.id as usuarios_id , CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto'))
							->join('usuarios','usuarios.empleados_id','=','empleados.id')
							->join('personas','empleados.personas_id','=','personas.id')
							->where('usuarios.username','=',$request->surname)
							->get();
		return  response()->json([
			'status' => 'ok',
			'token' => $jwt_token,
			'data'=> $usuario_empleado->toArray(),
		]);
	}

	public  function  logout(Request  $request) {
		$this->validate($request, [
			'token' => 'required'
		]);

		try {
			JWTAuth::invalidate($request->token);
			return  response()->json([
				'status' => 'ok',
				'message' => 'Cierre de sesión exitoso.'
			]);
		} catch (JWTException  $exception) {
			return  response()->json([
				'status' => 'unknown_error',
				'message' => 'Al usuario no se le pudo cerrar la sesión.'
			], 500);
		}
	}

	public  function  getAuthUser(Request  $request) {
		$this->validate($request, [
			'token' => 'required'
		]);

		$user = JWTAuth::authenticate($request->token);
		return  response()->json(['user' => $user]);
	}

	/**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsuarioActual()
    {
        return response()->json([
			'status' => 'ok',
			'data'=>$this->guard()->user()
		]);
	}
	
	/**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
	}
	
	public function getUsuarios(){
		$usuarios=User::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$usuarios->toArray()
		]);
	}

	public function getUsuario($id){
		$usuario=User::find($id);
		if(!is_null($usuario)){
			return response()->json([
				'status' => 'ok',
				'data'=>$usuario->toArray()
			]);
		}

		return response()->json([
			'status' => 'user_notfound',
			'data'=>'No se ha encontrado el usuario con dichos parámetros de búsqueda'
		]);	
	}

	public function getTiposUsuarios(){
		$tipos_usuarios=TipoUsuario::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$tipos_usuarios->toArray()
		]);
	}

	/**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(){
		$token=$this->guard()->refresh();
		return response()->json([
			'status' => 'ok',
			'data'=> $token
		]);
    }

	/**Verificar si el token es válido */
	public function esTokenValido(Request  $request){	
		return response()->json([
			'status' => 'ok',
			'data'=> JWTAuth::parseToken()->authenticate()
		]);
	}

	/**actualizar datos del usuario */
	public function actualizarUsuario(Request $request, $id){
		if($request->has('actualizar')){
			$user=User::find($id);
			if($request->query('actualizar')=='password'){
				if($request->has('password')){
					$user->password = bcrypt($request->password);
				}else{
					return response()->json([
						'status' => 'user_updateerror',
						'message'=> 'Falta valor a reemplazar'
					]);
				}
			}
			$user->save();
			return response()->json([
				'status' => 'ok',
				'message'=> 'Los datos del usuario se actualizaron con éxito'
			]);
		}

		return response()->json([
			'status' => 'user_updateerror',
			'message'=> 'Falta parámetro actualizar que indique los campos a cambiar'
		]);
	}
}
