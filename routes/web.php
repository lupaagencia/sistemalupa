<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware'=>['guest']],function(){
    
    Route::get('/','Auth\LoginController@showLoginForm');
    Route::post('/login', 'Auth\LoginController@login')->name('login');
   
});

Route::group(['middleware'=>['auth']],function(){
    
    Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
    
    Route::get('/main', function () {
        return view('contenido/contenido');
    })->name('main');

    Route::group(['middleware' => ['Almacenero']], function () {
        Route::get('/categoria', 'CategoriaController@index');
        Route::post('/categoria/registrar', 'CategoriaController@store');
        Route::put('/categoria/actualizar', 'CategoriaController@update');
        Route::put('/categoria/desactivar', 'CategoriaController@desactivar');
        Route::put('/categoria/activar', 'CategoriaController@activar');
        Route::get('/categoria/selectCategoria', 'CategoriaController@selectCategoria');

        Route::get('/articulo', 'ArticuloController@index');
        Route::post('/articulo/registrar', 'ArticuloController@store');
        Route::put('/articulo/actualizar', 'ArticuloController@update');
        Route::put('/articulo/desactivar', 'ArticuloController@desactivar');
        Route::put('/articulo/activar', 'ArticuloController@activar');
        Route::get('/articulo/buscarArticulo', 'ArticuloController@buscarArticulo');
        Route::get('/articulo/listarArticulo', 'ArticuloController@listarArticulo');

        Route::get('/proveedor', 'ProveedorController@index');
        Route::get('/proveedor/selectProveedor', 'ProveedorController@selectProveedor');
        Route::post('/proveedor/registrar', 'ProveedorController@store');
        Route::put('/proveedor/actualizar', 'ProveedorController@update');

        Route::get('/ingreso', 'IngresoController@index');
        Route::post('/ingreso/registrar', 'IngresoController@store');
        Route::put('/ingreso/desactivar', 'IngresoController@desactivar');

    });

    Route::group(['middleware' => ['Vendedor']], function () {
        Route::get('/cliente', 'ClienteController@index');
        Route::post('/cliente/registrar', 'ClienteController@store');
        Route::put('/cliente/actualizar', 'ClienteController@update');
    });

    Route::group(['middleware' => ['Administrador']], function () {
        
        Route::get('/categoria', 'CategoriaController@index');
        Route::post('/categoria/registrar', 'CategoriaController@store');
        Route::put('/categoria/actualizar', 'CategoriaController@update');
        Route::put('/categoria/desactivar', 'CategoriaController@desactivar');
        Route::put('/categoria/activar', 'CategoriaController@activar');
        Route::get('/categoria/selectCategoria', 'CategoriaController@selectCategoria');

        Route::get('/articulo', 'ArticuloController@index');
        Route::post('/articulo/registrar', 'ArticuloController@store');
        Route::put('/articulo/actualizar', 'ArticuloController@update');
        Route::put('/articulo/desactivar', 'ArticuloController@desactivar');
        Route::put('/articulo/activar', 'ArticuloController@activar');
        Route::get('/articulo/buscarArticulo', 'ArticuloController@buscarArticulo');
        Route::get('/articulo/selectArticulo', 'ArticuloController@selectArticulo');
        Route::get('/articulo/selectArticulobyid', 'ArticuloController@selectArticulobyid');
        Route::get('/articulo/listarArticulo', 'ArticuloController@listarArticulo');

        Route::get('/proveedor', 'ProveedorController@index');
        Route::get('/proveedor/selectProveedor', 'ProveedorController@selectProveedor');
        Route::post('/proveedor/registrar', 'ProveedorController@store');
        Route::put('/proveedor/actualizar', 'ProveedorController@update');

        Route::get('/ingreso', 'IngresoController@index');
        Route::post('/ingreso/registrar', 'IngresoController@store');
        Route::put('/ingreso/desactivar', 'IngresoController@desactivar');
        
        Route::get('/cliente', 'ClienteController@index');
        Route::get('/cliente/selectClientes', 'ClienteController@selectclientes');
        Route::get('/cliente/selectCliente', 'ClienteController@selectcliente');
        Route::post('/cliente/registrar', 'ClienteController@store');
        Route::put('/cliente/actualizar', 'ClienteController@update');

        Route::get('/rol', 'RolController@index');
        Route::get('/rol/selectRol', 'RolController@selectRol');
        
        Route::get('/user', 'UserController@index');
        Route::post('/user/registrar', 'UserController@store');
        Route::put('/user/actualizar', 'UserController@update');
        Route::put('/user/desactivar', 'UserController@desactivar');
        Route::put('/user/activar', 'UserController@activar');

        Route::get('/costop', 'CostoisController@index');
        Route::get('/costop/selectInsumos', 'CostoisController@selectInsumos');
        Route::post('/costop/registrar', 'CostoisController@store');
        Route::put('/costop/actualizar', 'CostoisController@update');
        Route::delete('/costop/borrar', 'CostoisController@delete');
        Route::put('/costop/activar', 'CostoisController@activar');
        
        Route::delete('/costo/borrar', 'CostoproduccionController@delete');
        Route::delete('/detalle/borrar', 'DetalletrabajoController@delete');

        Route::get('/orden', 'OrdentrabajoController@index');
        Route::get('/orden/filtrarFecha', 'OrdentrabajoController@filtrarFecha');
        Route::post('/orden/procesos', 'OrdentrabajoController@procesos');
        Route::post('/orden/registrar', 'OrdentrabajoController@store');
        Route::put('/orden/actualizar', 'OrdentrabajoController@update');
        Route::post('/orden/duplicar', 'OrdentrabajoController@duplicar');
        Route::delete('/orden/borrar', 'OrdentrabajoController@destroy');
        Route::put('/orden/activar', 'OrdentrabajoController@activar');
        Route::post('/orden/cambiarProceso', 'OrdentrabajoController@cambiarProceso');
    });

});
