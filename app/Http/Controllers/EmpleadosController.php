<?php

namespace App\Http\Controllers;
use App\Empleado;
use App\Persona;
use App\User;
use App\GlobalConstants;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class EmpleadosController extends Controller
{
    

    public function obtenerEmpleados(Request $request){
        if($request->has('rol')){
            $empleado_rol_id=$request->query('rol');
            $empleados=DB::table('empleados')
                    ->select('*',DB::raw('empleados.id as empleados_id, personas.id as personas_id , CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto'))
                    ->join('personas','personas.id','=','empleados.personas_id')
                    ->where('tipo_empleado_id','=',$empleado_rol_id)
                    ->get();
        }else{
            $empleados=DB::table('empleados')
                    ->select('*',DB::raw('empleados.id as empleados_id, personas.id as personas_id , CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto'))
                    ->join('personas','personas.id','=','empleados.personas_id')
                    ->get();
        }
        
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$empleados->toArray()
		]);
    }

    function contarEmpleados(){
        $empleados_count=DB::table('empleados')
                    ->select(DB::raw('COUNT(*) as cantidad'))
                    ->get();
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$empleados_count->toArray()
		]);
    }

    public function obtenerEmpleadosUsuarios(){
        $empleados=DB::table('empleados')
                    ->select('*',DB::raw('empleados.id as empleados_id, personas.id as personas_id, usuarios.id as usuarios_id , CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto'))
                    ->join('personas','personas.id','=','empleados.personas_id')
                    ->join('usuarios','usuarios.empleados_id','=','empleados.id')
                    ->get();
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$empleados->toArray()
		]);
    }

    public function obtenerEmpleado($id){
        $empleado=DB::table('empleados')
                    ->select('*',DB::raw('empleados.id as empleados_id, personas.id as personas_id , CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto'))
                    ->join('personas','personas.id','=','empleados.personas_id')
                    ->where('empleados.id','=',$id)
                    ->get();
        
        if(!is_null($empleado) && !empty($empleado) && count($empleado)>0){
            return response()->json([
                'status' => 'ok',
                'data'=>$empleado->toArray()
            ]);
        }

        return response()->json([
			'status' => 'empleado_notfound',
			'data'=>'No se ha encontrado el empleado con dichos parámetros de búsqueda'
		]);	

    }


    public function registrarEmpleadoYUsuario(Request $request){
         //verificando que el usuario registrado no exista
		if(User::where('username',$request->username)->count()==0){
            DB::beginTransaction();

            $user = new  User();
			$user->name = $request->nombres;
			$user->username = $request->username;
			$user->surname = $request->username;
			$user->email = $request->email;
			$user->password = bcrypt($request->password);
			
			//verificando que el empleado registrado no exista
			if(Persona::where('nro_identificacion',$request->nro_identificacion)->count()==0){
                $empleado=new Empleado();
                $persona=new Persona();
    
                $persona->nombres= $request->nombres;
                $persona->apellido_paterno= $request->apellido_paterno;
                $persona->apellido_materno= $request->apellido_materno;
                $persona->tipo_documento_id= $request->tipo_documento_id;
                $persona->nro_identificacion= $request->nro_identificacion;
                $persona->genero_id= $request->genero_id;
                $persona->direccion= $request->direccion;
                $persona->provincia= $request->provincia;
                $persona->departamento= $request->departamento;
                $persona->pais= $request->pais;
                $persona->fecha_nacimiento= $request->fecha_nacimiento;
                $persona->email= $request->email;
                $persona->telefonos= $request->telefonos;
                
                $persona->foto_adjunto=GlobalConstants::$IMAGE_DEFAULT_URL;
                if($request->hasFile('foto_adjunto')){
                    $path = $request->foto_adjunto->store('imagenes');
                    $persona->foto_adjunto=$path;
                }
    
                $persona->save();
    
                $empleado->personas_id=$persona->id;
                $empleado->tipo_empleado_id=$request->tipo_empleado_id;
                
                $empleado->save();

                $user->empleados_id=$empleado->id;
                
                
            } else{
                DB::rollback();
                return response()->json([
                    'status' => 'empleado_dni_redundante',
                    'message'=>'El dni del empleado no puede ser repetido'
                ]);
    
            }

            $user->save();
            DB::commit();
		}else{	//si existe sucede lo siguiente
			return  response()->json([
                'status' => 'username_duplicated',
                'message' => 'El nombre de usuario ingresado ya existe'
            ], 200);	

		}
        
        
        return response()->json([
            'status' => 'ok',
            'message'=>'El empleado se ha registrado satisfactoriamente'
        ]);

		
    }

    public function actualizarEmpleado(Request $request, $id){
        
        $empleado= Empleado::find($id);
        $persona= Persona::find($empleado->personas_id);
        
        if(!is_null($persona)){
            if($request->has('nombres')){$persona->nombres= $request->nombres;}
            if($request->has('apellido_paterno'))$persona->apellido_paterno= $request->apellido_paterno;
            if($request->has('apellido_materno'))$persona->apellido_materno= $request->apellido_materno;
            if($request->has('tipo_documento_id'))$persona->tipo_documento_id= $request->tipo_documento_id;
            if($request->has('nro_identificacion'))$persona->nro_identificacion= $request->nro_identificacion;
            if($request->has('genero_id'))$persona->genero_id= $request->genero_id;
            if($request->has('direccion'))$persona->direccion= $request->direccion;
            if($request->has('provincia'))$persona->provincia= $request->provincia;
            if($request->has('departamento'))$persona->departamento= $request->departamento;
            if($request->has('pais'))$persona->pais= $request->pais;
            if($request->has('fecha_nacimiento'))$persona->fecha_nacimiento= $request->fecha_nacimiento;
            if($request->has('email'))$persona->email= $request->email;
            if($request->has('telefonos'))$persona->telefonos= $request->telefonos;
            if($request->has('tipo_empleado_id')) $empleado->tipo_empleado_id=$request->tipo_empleado_id;
            
            if($request->hasFile('foto_adjunto')){
                $path = $request->foto_adjunto->store('imagenes');
                $persona->foto_adjunto=$path;
            }

    
            $persona->save();
            $empleado->save();
            
            return response()->json([
                'status' => 'ok',
                'message'=>'Los datos del empleado se han actualizado satisfactoriamente'
            ]);
        }

        return response()->json([
            'status' => 'persona_notfound',
            'message'=>'Los datos personales del empleado no han sido registrados'
        ]);        

    
        return response()->json([
            'status' => 'empleado_dni_redundante',
            'message'=>'El dni del empleado no puede ser repetido'
        ]);
    
   }


   /**
    * Eliminar datos de empelado y usuario
    * @param $id de usuario
    */
   public function eliminarUsuarioEmpleado($id){
        try{
            $user=User::find($id);
            $empleado=Empleado::find($user->empleados_id);
            $persona= Persona::find($empleado->personas_id);
            
            DB::beginTransaction();

            $user->delete();
            $empleado->delete();
            $persona->delete();
            
            DB::commit();
    
        }catch(QueryException $e){
            DB::rollback();
            return response()->json([
                'status' => 'empleado_notdeleted',
                'message'=>'No se pudo eliminar al empleado'
            ]);
        }
        return response()->json([
            'status' => 'ok',
            'message'=>'Se ha eliminado el empleado seleccionado'
        ]);        
        
    }

    public function registrarEmpleado(Request $request){
        //verificando que el empleado registrado no exista
		if(Persona::where('nro_identificacion',$request->nro_identificacion)->count()==0){
            DB::beginTransaction();

            $empleado=new Empleado();
            $persona=new Persona();

            if(!$request->has('nombres')){
                return response()->json([
                    'status' => 'empleado_nodata_post',
                    'message'=>'Debe ingresar las parametros requeridos'
                ]);
            }
            $persona->nombres= $request->nombres;
            $persona->apellido_paterno= $request->apellido_paterno;
            $persona->apellido_materno= $request->apellido_materno;
            $persona->tipo_documento_id= $request->tipo_documento_id;
            $persona->nro_identificacion= $request->nro_identificacion;
            $persona->genero_id= $request->genero_id;
            $persona->direccion= $request->direccion;
            $persona->provincia= $request->provincia;
            $persona->departamento= $request->departamento;
            $persona->pais= $request->pais;
            $persona->fecha_nacimiento= $request->fecha_nacimiento;
            $persona->email= $request->email;
            $persona->telefonos= $request->telefonos;
            
            $persona->foto_adjunto=GlobalConstants::$IMAGE_DEFAULT_URL;
            if($request->hasFile('foto_adjunto')){
                $path = $request->foto_adjunto->store('imagenes');
                $persona->foto_adjunto=$path;
            }

            $persona->save();

            $empleado->personas_id=$persona->id;
            $empleado->tipo_empleado_id=$request->tipo_empleado_id;
            $empleado->save();

            DB::commit();

            return response()->json([
                'status' => 'ok',
                'message'=>'El empleado se ha registrado satisfactoriamente'
            ]);

        } 

		return response()->json([
			'status' => 'empleado_dni_redundante',
			'message'=>'El dni del empleado no puede ser repetido'
		]);
    }
    
    /**actualizar datos de empleado y usuario */
    public function actualizarUsuarioEmpleado(Request $request, $id){
        $no_hay_valor_de_parametro=true;
        
        $user=User::find($id);
        $empleado=Empleado::find($user->empleados_id);
        $persona= Persona::find($empleado->personas_id);
        $data=[];

        if($request->has('modificar_password')){
            if($request->has('password')){
                $user->password = bcrypt($request->password);
                $no_hay_valor_de_parametro=false;
            }
        }

        if($request->has('modificar_username')){
            if($request->has('username')){
                $user->username = $request->username;
			    $user->surname = $request->username;
                $no_hay_valor_de_parametro=false;
            }
        }

        if($request->has('modificar_foto')){
            if($request->hasFile('foto_adjunto')){
                $path = $request->foto_adjunto->store('imagenes');
                $persona->foto_adjunto=$path;
                $no_hay_valor_de_parametro=false;
                $data['foto_adjunto']=GlobalConstants::$STORAGE_DIRECTORY_URL.$path;
            }
        }

        if($request->has('modificar_datos_personales')){
            if($request->has('nombres')){$persona->nombres= $request->nombres;}
            if($request->has('apellido_paterno'))$persona->apellido_paterno= $request->apellido_paterno;
            if($request->has('apellido_materno'))$persona->apellido_materno= $request->apellido_materno;
            if($request->has('tipo_documento_id'))$persona->tipo_documento_id= $request->tipo_documento_id;
            if($request->has('nro_identificacion'))$persona->nro_identificacion= $request->nro_identificacion;
            if($request->has('genero_id'))$persona->genero_id= $request->genero_id;
            if($request->has('direccion'))$persona->direccion= $request->direccion;
            if($request->has('provincia'))$persona->provincia= $request->provincia;
            if($request->has('departamento'))$persona->departamento= $request->departamento;
            if($request->has('pais'))$persona->pais= $request->pais;
            if($request->has('fecha_nacimiento'))$persona->fecha_nacimiento= $request->fecha_nacimiento;
            if($request->has('email')){
                $persona->email= $request->email;
                $user->email = $request->email;
            }
            if($request->has('telefonos'))$persona->telefonos= $request->telefonos;
            if($request->has('tipo_empleado_id')) $empleado->tipo_empleado_id=$request->tipo_empleado_id;
            $no_hay_valor_de_parametro=false;
        }

        if($no_hay_valor_de_parametro){
            return response()->json([
                'status' => 'empleado_updateerror',
                'message'=> 'Falta valor a reemplazar',
                'data'=> $request->password
            ]);
        }

        $persona->save();
        $empleado->save();
        $user->save();

        return response()->json([
            'status' => 'ok',
            'message'=> 'Los datos del empleado se actualizaron con éxito',
            'data'=>$data
        ]);
		
    }


    public function subirImagen(Request $request){
        if($request->hasFile('foto_adjunto')){
            $path = $request->foto_adjunto->store('imagenes');
            return response()->json([
                'status' => 'ok',
                'message'=> $path
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message'=> 'No se ha podido subir imagen'
        ]);
    }



}
