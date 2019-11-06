<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\Persona;
use App\GlobalConstants;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    function getClientes(){
        $clientes=DB::table('clientes')
                    ->select('*',DB::raw('CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto, '.
                            'CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",COALESCE(clientes.adjunto,"")) as adjunto'))
                    ->join('personas','personas.id','=','clientes.personas_id')
                    ->get();
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$clientes->toArray()
		]);
    }


    function getCliente($id){
        $clientes=DB::table('clientes')
                    ->select('*',DB::raw('cliente.id as cliente_id, CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto, '.
                            'CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",COALESCE(clientes.adjunto,"")) as adjunto'))
                    ->join('personas','personas.id','=','clientes.personas_id')
                    ->where('cliente.id','=',$id)
                    ->get();
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$clientes->toArray()
		]);
    }

    public function actualizarCliente(Request $request,$id){
        
        

        $clientes=Cliente::where('personas_id','=',$id)->get();
        if(count($clientes)>0){
            $cliente=$clientes[0];
        
            $persona=Persona::find($cliente->personas_id);
            if(!is_null($persona)){
                DB::beginTransaction();
                if($request->has('nombres')) $persona->nombres= $request->nombres;
                if($request->has('apellido_paterno')) $persona->apellido_paterno= $request->apellido_paterno;
                if($request->has('apellido_materno')) $persona->apellido_materno= $request->apellido_materno;
                if($request->has('tipo_documento_id')) $persona->tipo_documento_id= $request->tipo_documento_id;
                if($request->has('nro_identificacion')) $persona->nro_identificacion= $request->nro_identificacion;
                if($request->has('genero_id')) $persona->genero_id= $request->genero_id;
                if($request->has('direccion')) $persona->direccion= $request->direccion;
                if($request->has('provincia')) $persona->provincia= $request->provincia;
                if($request->has('departamento')) $persona->departamento= $request->departamento;
                if($request->has('pais')) $persona->pais= $request->pais;
                if($request->has('fecha_nacimiento')) $persona->fecha_nacimiento= $request->fecha_nacimiento;
                if($request->has('email')) $persona->email= $request->email;
                if($request->has('telefonos')) $persona->telefonos= $request->telefonos;
                if($request->has('estado_civil')) $persona->estado_civil= $request->estado_civil;

                $persona->foto_adjunto=GlobalConstants::$IMAGE_DEFAULT_URL;
                if($request->hasFile('foto_adjunto')){
                    $path = $request->foto_adjunto->store('imagenes');
                    $persona->foto_adjunto=$path;
                }

                if($request->hasFile('adjunto')){
                    $path = $request->adjunto->store('adjunto');
                    $cliente->adjunto=$path;
                }

                $persona->save();
                
                if($request->has('nro_licencia_conducir'))$cliente->nro_licencia_conducir=$request->nro_licencia_conducir;
                if($request->has('donacion_organos'))$cliente->donacion_organos=$request->donacion_organos;
                if($request->has('tipo_estado_id'))$cliente->tipo_estado_id=$request->tipo_estado_id;
                $cliente->save();

                DB::commit();

                return response()->json([
                    'status' => 'ok',
                    'message'=>'Los datos del cliente se han actualizado satisfactoriamente'
                ]);
            } 
        }
		return response()->json([
            'status' => 'persona_notfound',
            'message'=>'Los datos personales del empleado no han sido registrados'
        ]);
    }

    public function registrarCliente(Request $request){
        //verificando que el cliente registrado no exista
		if(Persona::where('nro_identificacion',$request->nro_identificacion)->count()==0){
            DB::beginTransaction();

            $cliente=new Cliente();
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
            $persona->estado_civil= $request->estado_civil;

            $persona->foto_adjunto=GlobalConstants::$IMAGE_DEFAULT_URL;
            if($request->hasFile('foto_adjunto')){
                $path = $request->foto_adjunto->store('imagenes');
                $persona->foto_adjunto=$path;
            }

            if($request->hasFile('adjunto')){
                $path = $request->adjunto->store('adjunto');
                $persona->adjunto=$path;
            }

            $persona->save();
            
            $cliente->personas_id=$persona->id;
            $cliente->nro_licencia_conducir=$request->nro_licencia_conducir;
            $cliente->donacion_organos=$request->donacion_organos;
            $cliente->tipo_estado_id=$request->tipo_estado_id;
            $cliente->save();

            DB::commit();

            return response()->json([
                'status' => 'ok',
                'message'=>'El cliente se ha registrado satisfactoriamente'
            ]);

        } 

		return response()->json([
			'status' => 'cliente_dni_redundante',
			'message'=>'El dni del cliente no puede ser repetido'
		]);
    }
}
