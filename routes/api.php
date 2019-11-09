<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



// estas rutas se pueden acceder sin proveer de un token válido.
Route::post('/login', 'AuthController@login');
Route::post('/register', 'AuthController@register');
//empleados
Route::post('/empleados/usuarios', 'EmpleadosController@registrarEmpleadoYUsuario');
    
//validar estado del token
Route::get('/usuarios/validar-token', 'AuthController@esTokenValido');

// estas rutas requiren de un token válido para poder accederse.
Route::group(['middleware' => 'jwt'], function () {
    Route::post('/logout', 'AuthController@logout');
    Route::get('/me', 'AuthController@getUsuarioActual');
    Route::get('/usuarios', 'AuthController@getUsuarios');
    Route::get('/usuarios/refresh-token', 'AuthController@refreshToken');
    Route::get('/usuarios/{id}', 'AuthController@getUsuario');
    Route::post('/usuarios/{id}', 'AuthController@actualizarUsuario');
    

    //tipos opciones
    Route::get('/tipos-usuario', 'TiposOpcionesController@getTiposUsuarios');
    Route::get('/tipos-documento', 'TiposOpcionesController@getTiposDocumentos');
    Route::get('/tipos-empleado', 'TiposOpcionesController@getTiposEmpleados');
    Route::get('/tipos-estado', 'TiposOpcionesController@getTiposEstados');
    Route::get('/generos', 'TiposOpcionesController@getGeneros');
    Route::get('/grupos-sanguineos', 'TiposOpcionesController@getGruposSanguineos');
    Route::get('/clases-categoria', 'TiposOpcionesController@getClasesCategoria');
    
    //empleados
    Route::post('/empleados', 'EmpleadosController@registrarEmpleado');
    Route::post('/empleados/actualizar/{id}', 'EmpleadosController@actualizarEmpleado');
    Route::get('/empleados', 'EmpleadosController@obtenerEmpleados');
    Route::get('/empleados/usuarios','EmpleadosController@obtenerEmpleadosUsuarios');
    Route::get('/empleados/count','EmpleadosController@contarEmpleados');
    Route::get('/empleados/{id}', 'EmpleadosController@obtenerEmpleado');
    Route::post('/empleados/usuarios/{id}/actualizar', 'EmpleadosController@actualizarUsuarioEmpleado');
    Route::delete('/empleados/usuarios/{id}','EmpleadosController@eliminarUsuarioEmpleado');
    Route::post('/subir-imagen', 'EmpleadosController@subirImagen');
    

    //clientes 
    Route::post('/clientes', 'ClientesController@registrarCliente');
    Route::post('/clientes/actualizar/{id}', 'ClientesController@actualizarCliente');
    Route::get('/clientes', 'ClientesController@getClientes');
    Route::get('/clientes/count','ClientesController@contarClientes');
    Route::delete('/clientes/{id}','ClientesController@eliminarCliente');
    Route::get('/cliente/{id}', 'ClientesController@getCliente');
        //examen de reglas
        Route::post('/clientes/{id}/examenes-reglas', 'ClientesController@mantenerExamenReglas');
        Route::get('/clientes/{id}/examenes-reglas', 'ClientesController@obtenerExamenRegla');
        //ficha médica
        Route::post('/clientes/{id}/fichas-medicas', 'ClientesController@mantenerFichaMedica');
        Route::get('/clientes/{id}/fichas-medicas', 'ClientesController@obtenerFichaMedica');

    Route::get('/foo', function(){
        
		return  response()->json([
			'status' => 'ok',
			'data' => 'hola'
        ], 200);
        

    });
});


//api del mtc
Route::group(['prefix' => 'mtc'], function () {
    
    Route::post('/fichas-medicas/{id}', 'MTCController@registrarFichaMedica');
    Route::post('/examenes-reglas/{id}', 'MTCController@registrarExamenReglas');

    Route::get('/fichas-medicas', 'MTCController@obtenerFichasMedicas');
    Route::get('/examenes-reglas', 'MTCController@obtenerExamenesReglas');


    Route::get('/foo', function(){
		return  response()->json([
			'status' => 'ok',
			'data' => 'hola'
        ], 200); 
    });
});