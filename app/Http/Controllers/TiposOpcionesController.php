<?php

namespace App\Http\Controllers;

use App\Genero;
use App\TipoDocumento;
use App\TipoUsuario;
use App\TipoEmpleado;
use App\GrupoSanguineo;
use App\TipoEstado;
use App\ClaseCategoria;
use Illuminate\Http\Request;

class TiposOpcionesController extends Controller
{

	public function getGruposSanguineos(){
		$grupos_sanguineos=GrupoSanguineo::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$grupos_sanguineos->toArray()
		]);
	}


    public function getGeneros(){
		$generos=Genero::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$generos->toArray()
		]);
	}

    public function getTiposDocumentos(){
		$tipos_documentos=TipoDocumento::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$tipos_documentos->toArray()
		]);
	}
    
    public function getTiposEmpleados(){
		$tipos_empleados=TipoEmpleado::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$tipos_empleados->toArray()
		]);
	}

	public function getTiposUsuarios(){
		$tipos_usuarios=TipoUsuario::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$tipos_usuarios->toArray()
		]);
	}

	public function getTiposEstados(){
		$tipos_estados=TipoEstado::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$tipos_estados->toArray()
		]);
	}

	public function getClasesCategoria(){
		$clases_categorias=ClaseCategoria::all();
		return response()->json([
			'status' => 'ok',
			'data'=>$clases_categorias->toArray()
		]);
	}
}
