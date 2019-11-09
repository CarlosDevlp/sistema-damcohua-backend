<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\Persona;
use App\GlobalConstants;
use App\ExamenReglas;
use App\FichaMedica;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    function getClientes(){
        $clientes=DB::table('clientes')
                    ->select('*',DB::raw('clientes.id as cliente_id, CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",personas.foto_adjunto) as foto_adjunto, '.
                            'CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",COALESCE(clientes.adjunto,"")) as adjunto'))
                    ->join('personas','personas.id','=','clientes.personas_id')
                    ->get();
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$clientes->toArray()
		]);
    }

    function contarClientes(){
        $clientes_count=DB::table('clientes')
                    ->select(DB::raw('COUNT(*) as cantidad'))
                    ->get();
                    
        return response()->json([
			'status' => 'ok',
			'data'=>$clientes_count->toArray()
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

    /**
     * obtener examen de reglas de un cliente
     */
    public function obtenerExamenRegla($id){
        $examen_reglas=ExamenReglas::where('clientes_personas_id','=',$id)
                                        ->select('*',DB::raw('CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",COALESCE(adjunto,"")) as adjunto'))
                                        ->first();
        if($examen_reglas==null){
            return response()->json([
                'status' => 'examenreglas_notfound',
                'message' => 'No se han encontrado ningun registro de examen de reglas'
            ]);
        }

        return response()->json([
			'status' => 'ok',
			'data'=>$examen_reglas->toArray()
		]);
    }

    /**
     * registrar o actualizar el examen de reglas de un cliente
     */
    public function mantenerExamenReglas($id, Request $request){
        $examen_reglas=ExamenReglas::where('clientes_personas_id','=',$id)->first();
        if($examen_reglas==null){
            $examen_reglas=new ExamenReglas();
            $examen_reglas->clientes_personas_id= $id;
        }
        if($request->has('servicio_solicitado')) $examen_reglas->servicio_solicitado= $request->servicio_solicitado;
        if($request->has('nro_recibo_operacion')) $examen_reglas->nro_recibo_operacion= $request->nro_recibo_operacion;
        $examen_reglas->clase_categoria_id= $request->clase_categoria_id;
        if($request->has('fecha_evaluacion')) $examen_reglas->fecha_evaluacion= $request->fecha_evaluacion;
        if($request->has('restricciones')) $examen_reglas->restricciones= $request->restricciones;
        if($request->has('observaciones')) $examen_reglas->observaciones= $request->observaciones;
        $examen_reglas->empleados_id= $request->empleados_id;
        if($request->hasFile('adjunto')){
            $path = $request->adjunto->store('adjunto');
            $examen_reglas->adjunto=$path;
        }
        $examen_reglas->save();
        
        return response()->json([
            'status' => 'ok',
            'message'=>'Los datos del examen de reglas se guardaron correctamente'
        ]);
    }

    /**
     * obtener la ficha médica de un cliente
     */
    public function obtenerFichaMedica($id){
        $ficha_medica=FichaMedica::where('clientes_personas_id','=',$id)
                                    ->select('*',DB::raw('CONCAT("'.GlobalConstants::$STORAGE_DIRECTORY_URL.'",COALESCE(adjunto,"")) as adjunto'))
                                    ->first();
        if($ficha_medica==null){
            return response()->json([
                'status' => 'fichamedica_notfound',
                'message' => 'No se han encontrado ningun registro de ficha médica'
            ]);
        }

        return response()->json([
			'status' => 'ok',
			'data'=>$ficha_medica->toArray()
		]);
    }

    /**
     * registrar o actualizar ficha médica de un cliente
     */
    public function mantenerFichaMedica($id, Request $request ){
        $ficha_medica=FichaMedica::where('clientes_personas_id','=',$id)->first();
        if($ficha_medica==null){
            $ficha_medica=new FichaMedica();
            $ficha_medica->clientes_personas_id= $id;
        }
        if($request->has('fecha_evaluacion')) $ficha_medica->fecha_evaluacion= $request->fecha_evaluacion;
        if($request->has('tipo_resultado_examen')) $ficha_medica->tipo_resultado_examen= $request->tipo_resultado_examen;
        if($request->hasFile('adjunto')){
            $path = $request->adjunto->store('adjunto');
            $ficha_medica->adjunto=$path;
        }
        if($request->has('tipo_examen')) $ficha_medica->tipo_examen= $request->tipo_examen;
        $ficha_medica->grupo_sanguineo_id= $request->grupo_sanguineo_id;
        if($request->has('observaciones')) $ficha_medica->observaciones= $request->observaciones;
        $ficha_medica->empleados_id= $request->empleados_id;
        $ficha_medica->save();
        return response()->json([
            'status' => 'ok',
            'message'=>'Los datos de la ficha médica se guardaron correctamente'
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


    public function eliminarCliente($id){
        try{
            $cliente=Cliente::find($id);
            
            DB::beginTransaction();

            $examenReglas=ExamenReglas::where('clientes_personas_id','=',$cliente->personas_id)->first();
            $fichaMedica= FichaMedica::where('clientes_personas_id','=',$cliente->personas_id)->first();
            if($examenReglas!=null) $examenReglas->delete();
            if($fichaMedica!=null) $fichaMedica->delete();
            
            $cliente->delete();
            
            DB::commit();
    
        }catch(QueryException $e){
            DB::rollback();
            return response()->json([
                'status' => 'cliente_notdeleted',
                'message'=>'No se pudo eliminar al cliente'
            ]);
        }
        return response()->json([
            'status' => 'ok',
            'message'=>'Se ha eliminado al cliente seleccionado'
        ]);    
    }
}
