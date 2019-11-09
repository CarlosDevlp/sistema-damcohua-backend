<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MTCExamenReglas;
use App\MTCFichaMedica;

class MTCController extends Controller
{
    function registrarFichaMedica($id, Request $request){
        $MTCFichaMedica=MTCFichaMedica::where('cliente_id','=',$id)->first();
        if($MTCFichaMedica==null){
            $MTCFichaMedica=new MTCFichaMedica();
            $MTCFichaMedica->cliente_id= $id;
        }
        if($request->has('data')) $MTCFichaMedica->data= $request->data;
        $MTCFichaMedica->save();

        return response()->json([
            'status' => 'ok',
            'message'=>'Se ha enviado la ficha médica al MTC satisfactoriamente'
        ]);  
    }

    function registrarExamenReglas($id, Request $request){
        $MTCExamenReglas=MTCExamenReglas::where('cliente_id','=',$id)->first();
        if($MTCExamenReglas==null){
            $MTCExamenReglas=new MTCExamenReglas();
            $MTCExamenReglas->cliente_id= $id;
        }
        if($request->has('data')) $MTCExamenReglas->data= $request->data;
        $MTCExamenReglas->save();

        return response()->json([
            'status' => 'ok',
            'message'=>'Se ha enviado el examen de reglas al MTC satisfactoriamente'
        ]);  
    }

    public function obtenerFichasMedicas(){
        $MTCFichaMedica=MTCFichaMedica::all();
        return response()->json([
            'status' => 'ok',
            'data'=>$MTCFichaMedica->toArray()
        ]);  
        
    }

    public function obtenerExamenesReglas(){
        $MTCExamenReglas=MTCExamenReglas::all();
        return response()->json([
            'status' => 'ok',
            'data'=>$MTCExamenReglas->toArray()
        ]);  
    }
}
