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
Route::get('categoria', 'CategoriaController@index');
Route::post('/costop/registrar', 'CostoisController@store');
