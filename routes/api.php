<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puede registrar rutas API para su aplicación. Estas
| RouteServiceProvider carga las rutas dentro de un grupo que
| se le asigna el grupo de middleware "api". ¡Disfruta construyendo tu API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/reloj/nx7/recibir', function (Illuminate\Http\Request $request) {
    // Aquí procesas los datos enviados por el reloj
    \Log::info('Datos recibidos del NX7:', $request->all());

    return $request->all();
});
Route::match(['get', 'post'], '/reloj', 'ZKTecoController@handlePush');
// Route::get('/zkpush', 'ZKTecoController@handlePush');
Route::get('/categoria', 'CategoriaController@categoriaMenu');
Route::get('/categoriaList', 'CategoriaController@categorias');
Route::get('/articulo', 'ArticuloController@index');
Route::get('/articulos/populares', 'ArticuloController@getPopulares');
Route::get('/articulo/crearimg', 'ArticuloController@crearimagenes');
Route::get('/subarticulo', 'ArticuloController@subproducto');
Route::post('/costop/registrar', 'CostoisController@store');    
Route::post('/mensaje/formularioContacto', 'MensajesController@formularioContacto');   
Route::post('/tienda/cotizar', 'TiendaController@cotizar');
Route::get('/tienda/configuracion', 'TiendaController@getConfiguracion');
Route::get('/tienda/atributos', 'TiendaController@getAtributos');
Route::get('/tienda/tipos-producto', 'TiendaController@getTiposProducto');
Route::get('/tienda/min-precio/{id}', 'TiendaController@getMinPrecio');
Route::post('/tienda/atributos', 'TiendaController@storeAtributo');
Route::put('/tienda/atributos/actualizar', 'TiendaController@updateAtributo');
Route::put('/tienda/atributos/desactivar', 'TiendaController@desactivarAtributo');
Route::put('/tienda/atributos/activar', 'TiendaController@activarAtributo');


