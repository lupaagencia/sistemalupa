<?php

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- | | Here is where you can register web routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | contains the "web" middleware group. Now create something great! | */
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // ✅ Importa la clase correcta
use maliklibs\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\File;

Route::get('/calculador/{any?}', function () {
    $path = public_path('calculador/index.php');

    if (!File::exists($path)) {
        abort(404);
    }

    // Esto carga el archivo index.php directamente
    require $path;
})->where('any', '.*');

// ── Herramienta temporal: Recalcular Caja Menor sin Terminal ──────────────────
Route::get('/recalcular-caja-menor-una-vez-lupa', function () {
    \App\CajaMenorMovimiento::recalculateBalances();
    return "Saldos de Caja Menor recalculados y corregidos exitosamente en el servidor.";
});

// ── Herramienta de migración: Abonos → Recibos de Pago ───────────────────────
Route::match(['get', 'post'], '/migrar-abonos', function (Request $request) {
    $path = public_path('migrar_abonos.php');
    if (!File::exists($path)) {
        abort(404, 'Archivo de migración no encontrado.');
    }
    // Pasar parámetros GET y POST al entorno del script
    $_GET = array_merge($_GET, $request->query->all());
    $_POST = array_merge($_POST, $request->request->all());
    ob_start();
    require $path;
    $output = ob_get_clean();
    return response($output)->header('Content-Type', 'text/html; charset=UTF-8');
});
Route::get('/orden/scan-status/{id}/{proceso}', 'OrdentrabajoController@actualizarEstadoScan');
Route::get('/scan/estado/{id}/{proceso}', 'OrdentrabajoController@actualizarEstadoScan');

// Public Attendance Routes for Kiosk Display and Mobile Phone Scanning
Route::get('/marcar-asistencia', 'AsistenciaController@pantallaMarcarMovil');
Route::get('/kiosco-asistencia', 'AsistenciaController@pantallaKiosco');
Route::post('/asistencia/registrar-qr', 'AsistenciaController@registrarMarcacionQR');
Route::post('/asistencia/reconocer-rostro', 'AsistenciaController@reconocerRostro');
Route::post('/asistencia/reconocer-huella', 'AsistenciaController@reconocerHuella');
Route::post('/asistencia/enrolar-huella', 'AsistenciaController@enrolarHuella');
Route::post('/asistencia/eliminar-huella', 'AsistenciaController@eliminarHuella');
Route::get('/borrarImagen', 'ArticuloController@borrarImagen');
Route::get('/slider/publicos', 'SliderController@publicos');

// Colors Route (Public Access with DB and JSON Fallback)
Route::get('/colores/{paleta}', function ($paleta) {
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('colores')) {
            $colores = \Illuminate\Support\Facades\DB::table('colores')
                ->where('paleta', $paleta)
                ->orderBy('id', 'asc')
                ->get(['pantone', 'hex']);

            if ($colores->count() > 0) {
                return response()->json($colores)
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->header('Pragma', 'no-cache');
            }
        }
    } catch (\Exception $e) {
        // Fallback si hay error
    }

    $file = public_path("colors{$paleta}.json");
    if (file_exists($file)) {
        return response()->file($file, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'
        ]);
    }

    return response()->json([]);
});

// ── Módulo Standalone Gastos Casa / Hogar (Acceso público directo por URL /gastos) ──
Route::get('/gastos', 'GastosCasaController@index');
Route::get('/gastos/data', 'GastosCasaController@getData');
Route::post('/gastos/persona/store', 'GastosCasaController@storePersona');
Route::delete('/gastos/persona/delete/{id}', 'GastosCasaController@deletePersona');
Route::post('/gastos/gasto/store', 'GastosCasaController@storeGasto');
Route::post('/gastos/gasto/update/{id}', 'GastosCasaController@updateGasto');
Route::delete('/gastos/gasto/delete/{id}', 'GastosCasaController@deleteGasto');
Route::post('/gastos/gasto/delete-masivo', 'GastosCasaController@deleteGastosMasivo');
Route::post('/gastos/ingreso/store', 'GastosCasaController@storeIngreso');
Route::post('/gastos/reserva/movimiento', 'GastosCasaController@storeReservaMovimiento');
Route::get('/gastos/importar/plantilla', 'GastosCasaController@descargarPlantilla');
Route::post('/gastos/importar', 'GastosCasaController@importarGastos');

Route::group(['middleware' => ['guest']], function () {


    // Route::get('/', function () {
    //     return Redirect::intended('http://localhost/sistema/web/public');
    // })->name('Home');
    // Route::get('/','Auth\LoginController@tienda');
    Route::get('/', 'Auth\LoginController@showLoginForm');

    Route::get('/login', 'Auth\LoginController@showLoginForm');
    Route::get('/iniciarSeccion', 'Auth\LoginController@showLoginForm');
    Route::post('/login', 'Auth\LoginController@login')->name('login');

    // Rutas de recuperación de contraseña
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');


});




Route::group(['middleware' => ['auth']], function () {
    Route::post('/verificar-clave', 'Auth\AuthController@verificarClave');
    // Colors Routes (Database backed with auto-seed from JSON fallback)
    Route::get('/colores/{paleta}', function ($paleta) {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('colores')) {
                $colores = \Illuminate\Support\Facades\DB::table('colores')
                    ->where('paleta', $paleta)
                    ->orderBy('id', 'asc')
                    ->get(['pantone', 'hex']);
                
                if ($colores->count() > 0) {
                    return response()->json($colores)
                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache');
                }

                // If DB is empty for this palette, populate DB from JSON file
                $file = public_path("colors{$paleta}.json");
                if (file_exists($file)) {
                    $jsonText = file_get_contents($file);
                    $jsonData = json_decode($jsonText, true);
                    if (is_array($jsonData) && count($jsonData) > 0) {
                        $insertData = [];
                        foreach ($jsonData as $color) {
                            if (isset($color['pantone']) && isset($color['hex'])) {
                                $insertData[] = [
                                    'pantone'    => $color['pantone'],
                                    'hex'        => $color['hex'],
                                    'paleta'     => $paleta,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                        if (!empty($insertData)) {
                            foreach (array_chunk($insertData, 500) as $chunk) {
                                \Illuminate\Support\Facades\DB::table('colores')->insert($chunk);
                            }
                        }
                        return response()->json($jsonData)
                            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                    }
                }
            }
        } catch (\Exception $e) {}

        $file = public_path("colors{$paleta}.json");
        if (file_exists($file)) {
            return response()->file($file, [
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'
            ]);
        }

        return response()->json([]);
    });

    // Chat Routes
    Route::get('/mensaje/listar', 'MensajeController@index');
    Route::post('/mensaje/enviar', 'MensajeController@store');
    Route::post('/mensaje/marcar', 'MensajeController@marcarLeidos');
    Route::get('/ajustes/codigos', 'AjustesController@codigos');
    Route::get('/ajustes/titulosDetalle', 'AjustesController@titulosDetalle');
    Route::get('/ajustes/listar', 'AjustesController@listAjustes');
    Route::post('/ajustes/registrar', 'AjustesController@store_ajuste');
    Route::put('/ajustes/actualizar', 'AjustesController@update_ajuste');
    Route::delete('/ajustes/eliminar', 'AjustesController@delete_ajuste');
    Route::get('/ajustes/nomina-config', 'AjustesController@getNominaConfig');
    Route::post('/ajustes/nomina-config', 'AjustesController@saveNominaConfig');
    Route::get('/estadisticas-ventas/resumen', 'EstadisticasVentasController@getResumen');

    // ── Superadministrador Web IDE & AI Agent Routes ─────────────────────────
    Route::group(['middleware' => ['Superadministrador']], function () {
        Route::get('/superadmin/ide/tree', 'SuperadminIdeController@getTree');
        Route::get('/superadmin/ide/file', 'SuperadminIdeController@getFile');
        Route::post('/superadmin/ide/save', 'SuperadminIdeController@saveFile');
        Route::post('/superadmin/ide/ai-prompt', 'SuperadminIdeController@aiPrompt');
        Route::post('/superadmin/ide/deploy-ftp', 'SuperadminIdeController@deployFtp');
    });


    // Sliders Web Routes
    Route::get('/slider', 'SliderController@index');
    Route::post('/slider/registrar', 'SliderController@store');
    Route::post('/slider/actualizar', 'SliderController@update');
    Route::put('/slider/desactivar', 'SliderController@desactivar');
    Route::put('/slider/activar', 'SliderController@activar');
    Route::post('/slider/eliminar', 'SliderController@destroy');

    Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
    Route::post('/user/cambiar-password', 'UserController@cambiarPassword');

    // ── CRM & Cotizaciones Routes ─────────────────────────────────────────────
    Route::get('/crm/equipo', 'CrmDashboardController@getEquipo');
    Route::get('/crm/kpis', 'CrmDashboardController@getKpis');
    Route::get('/crm/empresa-config', 'CrmDashboardController@getEmpresaConfig');
    Route::post('/crm/empresa-config', 'CrmDashboardController@saveEmpresaConfig');

    // ── CRM Base de Datos & Contactos Outbound Routes ─────────────────────────
    Route::get('/crm/base-datos', 'CrmBaseDatosController@index');
    Route::get('/crm/base-datos/select', 'CrmBaseDatosController@selectBases');
    Route::post('/crm/base-datos/registrar', 'CrmBaseDatosController@store');
    Route::put('/crm/base-datos/actualizar', 'CrmBaseDatosController@update');
    Route::delete('/crm/base-datos/eliminar', 'CrmBaseDatosController@destroy');

    Route::get('/crm/base-datos/contactos', 'CrmBaseDatosController@getContactos');
    Route::post('/crm/base-datos/contacto/registrar', 'CrmBaseDatosController@storeContacto');
    Route::put('/crm/base-datos/contacto/actualizar', 'CrmBaseDatosController@updateContacto');
    Route::post('/crm/base-datos/registrar-portafolio', 'CrmBaseDatosController@registrarPortafolio');
    Route::post('/crm/base-datos/importar', 'CrmBaseDatosController@importarContactos');
    Route::post('/crm/base-datos/convertir-prospecto', 'CrmBaseDatosController@convertirAProspecto');
    Route::get('/crm/base-datos/estadisticas', 'CrmBaseDatosController@getEstadisticas');
    Route::get('/crm/base-datos/catalogos', 'CrmBaseDatosController@getCatalogos');
    Route::post('/crm/base-datos/catalogos/registrar', 'CrmBaseDatosController@storeCatalogItem');
    Route::put('/crm/base-datos/catalogos/actualizar', 'CrmBaseDatosController@updateCatalogItem');
    Route::delete('/crm/base-datos/catalogos/eliminar', 'CrmBaseDatosController@deleteCatalogItem');

    Route::get('/crm/prospecto', 'CrmProspectoController@index');
    Route::get('/crm/prospecto/select', 'CrmProspectoController@selectProspectos');
    Route::post('/crm/prospecto/registrar', 'CrmProspectoController@store');
    Route::put('/crm/prospecto/actualizar', 'CrmProspectoController@update');
    Route::delete('/crm/prospecto/eliminar', 'CrmProspectoController@destroy');
    Route::post('/crm/prospecto/convertir-cliente', 'CrmProspectoController@convertirACliente');

    Route::get('/crm/oportunidad', 'CrmOportunidadController@index');
    Route::get('/crm/oportunidad/kanban', 'CrmOportunidadController@kanban');
    Route::get('/crm/oportunidad/select', 'CrmOportunidadController@selectOportunidades');
    Route::post('/crm/oportunidad/registrar', 'CrmOportunidadController@store');
    Route::put('/crm/oportunidad/actualizar', 'CrmOportunidadController@update');
    Route::put('/crm/oportunidad/cambiar-etapa', 'CrmOportunidadController@cambiarEtapa');
    Route::delete('/crm/oportunidad/eliminar', 'CrmOportunidadController@destroy');

    Route::get('/crm/cotizacion', 'CrmCotizacionController@index');
    Route::get('/crm/cotizacion/siguiente-numero', 'CrmCotizacionController@siguienteNumero');
    Route::get('/crm/cotizacion/{id}', 'CrmCotizacionController@show');
    Route::post('/crm/cotizacion/registrar', 'CrmCotizacionController@store');
    Route::put('/crm/cotizacion/actualizar', 'CrmCotizacionController@update');
    Route::put('/crm/cotizacion/cambiar-estado', 'CrmCotizacionController@cambiarEstado');
    Route::delete('/crm/cotizacion/eliminar', 'CrmCotizacionController@destroy');
    Route::get('/crm/cotizacion/pdf/{id}', 'CrmCotizacionController@pdf');
    Route::post('/crm/cotizacion/convertir-pedido', 'CrmCotizacionController@convertirAPedido');
    Route::post('/crm/cotizacion/revertir-conversion', 'CrmCotizacionController@revertirConversion');

    Route::get('/crm/vendedores/select', 'CrmDashboardController@selectVendedores');

    Route::get('/crm/actividad', 'CrmActividadController@index');
    Route::post('/crm/actividad/registrar', 'CrmActividadController@store');
    Route::put('/crm/actividad/completar', 'CrmActividadController@marcarCompletada');
    Route::delete('/crm/actividad/eliminar', 'CrmActividadController@destroy');

    Route::get(
        '/main',
        function () {
            return view('backend/contenido');
        }
    )->name('main');

    // Rutas de Configuración Web - Sliders
    Route::get('/slider', 'SliderController@index');
    Route::post('/slider/registrar', 'SliderController@store');
    Route::post('/slider/actualizar', 'SliderController@update');
    Route::put('/slider/desactivar', 'SliderController@desactivar');
    Route::put('/slider/activar', 'SliderController@activar');
    Route::post('/slider/eliminar', 'SliderController@destroy');

    // Status Pro Common Routes (Accessible by all roles for syncing)
    Route::get('/statuspro', 'StatusProduccionController@index');
    Route::get('/statuspro/imprimir-status/{estado}', 'StatusProduccionController@imprimirStatus');
    Route::get('/statuspro/procesos', 'StatusProduccionController@procesos');
    Route::get('/statuspro/checkUpdates', 'StatusProduccionController@checkUpdates');
    Route::put('/statuspro/cambiarEstado', 'StatusProduccionController@cambiarEstado');
    Route::put('/statuspro/cambiarPrioridad', 'StatusProduccionController@cambiarPrioridad');
    Route::put('/statuspro/cambiarPlancha', 'StatusProduccionController@cambiarPlancha');
    Route::put('/statuspro/cambiarDatosEstado', 'StatusProduccionController@cambiarDatosEstado');
    Route::get('/statuspro/estadosdestatus', 'StatusProduccionController@procesos');
    Route::get('/statuspro/asignarCostos', 'StatusProduccionController@asignarCostos');
    Route::get('/statuspro/verificarEnvio', 'StatusProduccionController@verificarEnvio');
    Route::post('/statuspro/actualizarValorTerminado', 'StatusProduccionController@actualizarValorTerminado');
    Route::post('/statuspro/actualizarCantidadEntregada', 'StatusProduccionController@actualizarCantidadEntregada');
    Route::post('/statuspro/guardarOpciones', 'StatusProduccionController@guardarOpciones');
    Route::post('/statuspro/guardarMaterial', 'StatusProduccionController@guardarMaterial');

    // Cuentas por Pagar Routes
    Route::get('/cuentas-pagar', 'CuentasPorPagarController@index');
    Route::post('/cuentas-pagar/registrar', 'CuentasPorPagarController@store')->middleware('periodo.abierto');
    Route::put('/cuentas-pagar/actualizar/{id}', 'CuentasPorPagarController@update')->middleware('periodo.abierto');
    Route::delete('/cuentas-pagar/eliminar/{id}', 'CuentasPorPagarController@destroy')->middleware('periodo.abierto');
    Route::post('/cuentas-pagar/abono', 'CuentasPorPagarController@registrarAbono')->middleware('periodo.abierto');
    Route::post('/cuentas-pagar/abono/actualizar/{id}', 'CuentasPorPagarController@actualizarAbono')->middleware('periodo.abierto');
    Route::post('/cuentas-pagar/abono-general', 'CuentasPorPagarController@registrarAbonoGeneral')->middleware('periodo.abierto');
    Route::delete('/cuentas-pagar/abono/{id}', 'CuentasPorPagarController@eliminarAbono')->middleware('periodo.abierto');
    Route::get('/cuentas-pagar/estado-cuenta', 'CuentasPorPagarController@obtenerEstadoCuentaIndividual');
    Route::get('/cuentas-pagar/abono/pdf/{id}', 'CuentasPorPagarController@descargarPDFAbono');

    // Pagos Programados Routes
    Route::get('/pagos-programados', 'PagoProgramadoController@index');
    Route::post('/pagos-programados/registrar', 'PagoProgramadoController@store');
    Route::put('/pagos-programados/actualizar/{id}', 'PagoProgramadoController@update');
    Route::delete('/pagos-programados/eliminar/{id}', 'PagoProgramadoController@destroy');
    Route::put('/pagos-programados/cambiar-estado/{id}', 'PagoProgramadoController@cambiarEstado');
    Route::post('/pagos-programados/generar-cuenta/{id}', 'PagoProgramadoController@generarCuentaPorPagar')->middleware('periodo.abierto');

    // Control de Asistencia y Turnos QR Routes
    Route::get('/kiosco-asistencia', 'AsistenciaController@pantallaKiosco');
    Route::get('/asistencia', 'AsistenciaController@index');
    Route::post('/asistencia/registrar-qr', 'AsistenciaController@registrarMarcacionQR');
    Route::get('/asistencia/reporte-llegadas-tarde', 'AsistenciaController@reporteLlegadasTarde');
    Route::get('/asistencia/reporte-horas-extras', 'AsistenciaController@reporteHorasExtras');
    Route::post('/asistencia/generar-qr-empleado/{id}', 'AsistenciaController@generarQREmpleado');
    Route::get('/asistencia/turnos', 'AsistenciaController@turnosIndex');
    Route::post('/asistencia/turnos/registrar', 'AsistenciaController@turnoStore');
    Route::put('/asistencia/turnos/actualizar/{id}', 'AsistenciaController@turnoUpdate');
    Route::post('/asistencia/asignar-turno', 'AsistenciaController@asignarTurnoEmpleado');
    Route::get('/asistencia/configuracion-seguridad', 'AsistenciaController@getConfiguracionSeguridad');
    Route::post('/asistencia/configuracion-seguridad', 'AsistenciaController@guardarConfiguracionSeguridad');

    // Egresos and Caja Menor Routes
    Route::get('/egresos/consolidado-resultados', 'EgresosController@getConsolidadoResultados');
    Route::get('/egresos', 'EgresosController@index');
    Route::post('/egresos/registrar', 'EgresosController@store')->middleware('periodo.abierto');
    Route::delete('/egresos/eliminar/{id}', 'EgresosController@destroy')->middleware('periodo.abierto');
    Route::get('/egresos/pdf/{id}', 'EgresosController@descargarPDFEgreso');
    Route::get('/caja-menor/status', 'EgresosController@getCajaMenorStatus');
    Route::get('/caja-menor/movimientos', 'EgresosController@listCajaMenorMovimientos');
    Route::post('/caja-menor/recargar', 'EgresosController@registrarRecargaCajaMenor')->middleware('periodo.abierto');
    Route::post('/caja-menor/recargar/actualizar/{id}', 'EgresosController@actualizarRecargaCajaMenor')->middleware('periodo.abierto');
    Route::delete('/caja-menor/recargar/eliminar/{id}', 'EgresosController@eliminarRecargaCajaMenor')->middleware('periodo.abierto');
    Route::get('/clasificaciones-egresos', 'ClasificacionEgresoController@index');
    Route::post('/clasificaciones-egresos/registrar', 'ClasificacionEgresoController@store')->middleware('periodo.abierto');
    Route::delete('/clasificaciones-egresos/eliminar/{id}', 'ClasificacionEgresoController@destroy')->middleware('periodo.abierto');

    // Periodos Contables Routes
    Route::get('/periodos-contables', 'PeriodoContableController@index');
    Route::post('/periodos-contables/toggle', 'PeriodoContableController@toggle');

    // Liquidación Quincenas (Payroll) Routes
    Route::get('/nomina/quincenas', 'LiquidacionQuincenaController@index');
    Route::post('/nomina/quincenas/registrar', 'LiquidacionQuincenaController@store');
    Route::post('/nomina/quincenas/pagar/{id}', 'LiquidacionQuincenaController@pagar');
    Route::put('/nomina/quincenas/actualizar/{id}', 'LiquidacionQuincenaController@update');
    Route::delete('/nomina/quincenas/eliminar/{id}', 'LiquidacionQuincenaController@destroy');
    Route::get('/nomina/quincenas/pdf/{id}', 'LiquidacionQuincenaController@descargarPDFQuincena');
    Route::get('/nomina/promedio-extras', 'LiquidacionQuincenaController@getPromedioExtras');
    Route::get('/nomina/seguridad-social', 'LiquidacionQuincenaController@getReporteSeguridadSocial');

    // Liquidación Primas Routes
    Route::get('/nomina/primas/calcular', 'LiquidacionPrimaController@calcularPrimas');
    Route::post('/nomina/primas/registrar', 'LiquidacionPrimaController@store');
    Route::get('/nomina/primas/historial', 'LiquidacionPrimaController@index');
    Route::delete('/nomina/primas/eliminar/{id}', 'LiquidacionPrimaController@destroy');
    Route::get('/nomina/primas/pdf/{id}', 'LiquidacionPrimaController@descargarPDFPrima');

    // Despiece Muebles de Cocina Routes
    Route::get('/despiece/proyectos', 'DespieceController@indexProyectos');
    Route::post('/despiece/proyectos/registrar', 'DespieceController@storeProyecto');
    Route::delete('/despiece/proyectos/eliminar/{id}', 'DespieceController@destroyProyecto');
    Route::get('/despiece/muebles/{proyecto_id}', 'DespieceController@getMuebles');
    Route::post('/despiece/muebles/registrar', 'DespieceController@storeMueble');
    Route::delete('/despiece/muebles/eliminar/{id}', 'DespieceController@destroyMueble');
    Route::get('/despiece/pdf/{proyecto_id}', 'DespieceController@descargarPDF');
    Route::get('/despiece/proyectos/{proyecto_id}/piezas-globales', 'DespieceController@getPiezasGlobales');

    // PUC / Cuentas Contables Routes
    Route::get('/cuentas-contables', 'CuentaController@index');
    Route::post('/cuentas-contables/registrar', 'CuentaController@store');
    Route::put('/cuentas-contables/actualizar/{id}', 'CuentaController@update');
    Route::delete('/cuentas-contables/eliminar/{id}', 'CuentaController@destroy');

    // Reportes Contables Routes
    Route::get('/reportes-contables/balance-general', 'ReporteContableController@balanceGeneral');
    Route::get('/reportes-contables/estado-resultados', 'ReporteContableController@estadoResultados');
    Route::get('/reportes-contables/balanza-comprobacion', 'ReporteContableController@balanzaComprobacion');
    Route::get('/reportes-contables/auxiliar-cuenta', 'ReporteContableController@auxiliarCuenta');
    Route::get('/reportes-contables/auxiliar-terceros', 'ReporteContableController@auxiliarTerceros');

    // Conciliación Bancaria Routes
    Route::get('/conciliacion-bancaria', 'ConciliacionBancariaController@index');
    Route::post('/conciliacion-bancaria/importar', 'ConciliacionBancariaController@importar');
    Route::post('/conciliacion-bancaria/conciliar-automatica', 'ConciliacionBancariaController@conciliarAutomatica');
    Route::post('/conciliacion-bancaria/conciliar-manual', 'ConciliacionBancariaController@conciliarManual');
    Route::post('/conciliacion-bancaria/desconciliar', 'ConciliacionBancariaController@desconciliar');
    Route::get('/conciliacion-bancaria/comprobantes-pendientes', 'ConciliacionBancariaController@buscarComprobantesPendientes');

    // Movimientos Contables Routes
    Route::get('/movimientos-contables', 'MovimientoContableController@index');

    // Ingresos (Compras) Routes
    Route::get('/ingreso', 'IngresoController@index');
    Route::post('/ingreso/registrar', 'IngresoController@store');
    Route::put('/ingreso/desactivar', 'IngresoController@desactivar');
    Route::get('/ingreso/obtenerCabecera', 'IngresoController@obtenerCabecera');
    Route::get('/ingreso/obtenerDetalles', 'IngresoController@obtenerDetalles');
    Route::get('/proveedor/obtenerEstadoCredito', 'ProveedorController@obtenerEstadoCredito');

    // Seguimiento y Optimización de Producción (Additive Routes)
    Route::get('/seguimiento-produccion/delivery-stats', 'ProduccionSeguimientoController@getDeliveryStats');
    Route::get('/seguimiento-produccion/active-progress', 'ProduccionSeguimientoController@getActiveProgress');
    Route::get('/seguimiento-produccion/print-optimization', 'ProduccionSeguimientoController@getPrintOptimization');

    // Entregas Parciales
    Route::get('/entrega', 'EntregasController@index');
    Route::post('/statuspro/registrarEntrega', 'EntregasController@store');
    Route::post('/entrega/registrarLote', 'EntregasController@storeBatch');
    Route::post('/entrega/registrarMasiva', 'EntregasController@storeMassive');
    Route::put('/entrega/{id}', 'EntregasController@update');
    Route::delete('/entrega/{id}', 'EntregasController@destroy');
    Route::delete('/entrega/comprobante/{id}', 'EntregasController@destroyComprobante');
    Route::get('/statuspro/entregaPdf/{id}', 'EntregasController@pdf');
    Route::get('/entrega/pdfLote/{comprobante_id}', 'EntregasController@pdfBatch');
    Route::get('/entrega/pedido/{id}', 'EntregasController@getByPedido');

    Route::group(
        ['middleware' => ['Almacenero']],
        function () {
            Route::get('/borrarImagen', 'UploadController@borrar');

            Route::get('/categoria', 'CategoriaController@index');
            Route::get('/categoria/articulosCategoria', 'CategoriaController@articulosCategoria');
            Route::post('/categoria/registrar', 'CategoriaController@store');
            Route::post('/categoria/actualizar', 'CategoriaController@update');
            Route::put('/categoria/actualizar', 'CategoriaController@update');
            Route::delete('/categoria/eliminar', 'CategoriaController@eliminar');
            Route::delete('/categoria/eliminarC', 'CategoriaController@eliminarC');
            Route::put('/categoria/activar', 'CategoriaController@activar');
            Route::get('/categoria/selectCategoria', 'CategoriaController@selectCategoria');
            Route::post('/categoria/importarWord', 'CategoriaController@importarWord');

            Route::get('/inventariosMateriasPrimas', 'InventarioController@index');
            Route::get('/inventarios/registar', 'InventarioController@store');

            Route::get('/articulo', 'ArticuloController@index');
            Route::post('/articulo/registrar', 'ArticuloController@store');
            Route::put('/articulo/actualizar', 'ArticuloController@update');
            Route::put('/articulo/cambioximportacion', 'ArticuloController@importar');
            Route::delete('/articulo/eliminar', 'ArticuloController@eliminar');
            Route::put('/articulo/activar', 'ArticuloController@activar');
            Route::get('/articulo/buscarArticulo', 'ArticuloController@buscarArticulo');
            Route::get('/articulo/selectArticulo', 'ArticuloController@selectArticulo');
            Route::get('/articulo/selectArticulobyid', 'ArticuloController@selectArticulobyid');
            Route::get('/articulo/listarArticulo', 'ArticuloController@listarArticulo');
            Route::get('/articulo/crearimagenes', 'ArticuloController@crearimagenes');
            Route::post('/articulo/importarImagenesMasivas', 'ArticuloController@importarImagenesMasivas');
            
            // Rutas Troqueles por Cabida
            Route::get('/articulo/troquel/listar', 'ArticuloController@listarTroqueles');
            Route::get('/articulo/troquel/listarTodos', 'ArticuloController@listarTodosLosTroqueles');
            Route::post('/articulo/troquel/guardar', 'ArticuloController@guardarTroquel');
            Route::delete('/articulo/troquel/eliminar', 'ArticuloController@eliminarTroquel');



            Route::get('/tipoproducto', 'TipoProductoController@index');
            Route::post('/tipoproducto/registrar', 'TipoProductoController@store');
            Route::put('/tipoproducto/actualizar', 'TipoProductoController@update');
            Route::get('/api/tienda/atributos-todos', 'TiendaController@listAll');
            Route::get('/api/tipo-producto/{id}/atributos', 'TipoProductoController@getAtributosTienda');
            Route::put('/user/actualizar', 'UserController@update');
            Route::put('/user/desactivar', 'UserController@desactivar');
            Route::put('/user/activar', 'UserController@activar');

            Route::get('/proveedor', 'ProveedorController@index');
            Route::get('/proveedor/selectProveedor', 'ProveedorController@selectProveedor');
            Route::post('/proveedor/registrar', 'ProveedorController@store');
            Route::put('/proveedor/actualizar', 'ProveedorController@update');
            Route::post('/proveedor/eliminar', 'ProveedorController@eliminar');
            Route::delete('/proveedor/eliminar', 'ProveedorController@eliminar');

            Route::get('/comprobante/facturas', 'ComprobanteController@facturas');
            Route::get('/comprobante/resumen-proformas', 'ComprobanteController@resumenProformas');
            Route::get('/comprobante/cotizaciones', 'ComprobanteController@cotizaciones');
            Route::put('/comprobante/convertirAPedido', 'ComprobanteController@convertirAPedido');
            Route::get('/comprobante', 'ComprobanteController@index');
            Route::get('/comprobante/entregar', 'ComprobanteController@pedidosEntregar');
            Route::get('/comprobante/remisiones', 'ComprobanteController@remisiones');
            Route::post('/comprobante/crearCuentaCobroDesdeRemisiones', 'ComprobanteController@crearCuentaCobroDesdeRemisiones');
            Route::get('/comprobante/cuentasCobro', 'ComprobanteController@cuentasCobro');
            Route::post('/comprobante/registrarAbonoCuentaCobro', 'ComprobanteController@registrarAbonoCuentaCobro')->middleware('periodo.abierto');
            Route::post('/comprobante/cruzarAbonosCuentaCobro', 'ComprobanteController@cruzarAbonosCuentaCobro')->middleware('periodo.abierto');
            Route::post('/comprobante/cambiarEstadoCuentaCobro', 'ComprobanteController@cambiarEstadoCuentaCobro');
            Route::delete('/comprobante/borrar', 'ComprobanteController@eliminarComprobante')->middleware('periodo.abierto');
            Route::get('/comprobante/completado', 'ComprobanteController@pedidosCompletados');
            Route::put('/comprobante/cambiarEstado', 'ComprobanteController@cambiarEstado');
            Route::post('/comprobante/registrar', 'ComprobanteController@store');
            Route::post('/comprobante/actualizar', 'ComprobanteController@actualizarComprobante');
            Route::put('/comprobante/desactivar', 'ComprobanteController@desactivar');
            Route::get('/imprimirPedido', 'ComprobanteController@imprimirPedido');
            Route::get('/imprimirfactura', 'ComprobanteController@imprimirFactura');
            Route::get('/imprimirProforma', 'ComprobanteController@imprimirProforma');
            Route::post('/comprobante/crearProformaDesdePedido', 'ComprobanteController@crearProformaDesdePedido');
            Route::get('/comprobante/proformas', 'ComprobanteController@proformas');
            Route::post('/proforma/cambiar-estado', 'ComprobanteController@cambiarEstadoProforma');
            Route::get('/proforma/consecutivo', 'ComprobanteController@getConsecutivoProforma');
            Route::post('/proforma/consecutivo', 'ComprobanteController@setConsecutivoProforma');
            Route::get('/imprimirCuentaCobro', 'ComprobanteController@imprimirCuentaCobro');
            Route::get('/cuentas-cobro/pdf/{id}', 'ComprobanteController@imprimirCuentaCobro');

            Route::get('/cliente', 'ClienteController@index');
            Route::get('/cliente/selectClientes', 'ClienteController@selectclientes');
            Route::get('/cliente/selectCliente', 'ClienteController@selectcliente');
            Route::get('/cliente/selectClientebyId', 'ClienteController@selectclientebyId');
            Route::post('/cliente/registrar', 'ClienteController@store');
            Route::put('/cliente/actualizar', 'ClienteController@update');
            Route::get('/cliente/verificar', 'ClienteController@verificarPersona');
            Route::delete('/datosenvio/borrar', 'ClienteController@eliminarDatose');
            Route::get('/guias', 'ClienteController@guias');
            Route::get('/generarGuia', 'ClienteController@generarguia');

            Route::get('/rol', 'RolController@index');
            Route::get('/rol/selectRol', 'RolController@selectRol');
            Route::post('/rol/registrar', 'RolController@store');
            Route::put('/rol/actualizar', 'RolController@update');
            Route::put('/rol/desactivar', 'RolController@desactivar');
            Route::put('/rol/activar', 'RolController@activar');

            Route::get('/user', 'UserController@index');
            Route::get('/user/actividad', 'UserController@actividadUser');
            Route::delete('/user/borrarActividad', 'ActividadController@delete');
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

            Route::delete('/costo/borrar', 'OrdentrabajoController@delete');
            Route::delete('/detalle/borrar', 'DetalletrabajoController@delete');

            Route::delete('/costoArticulo/borrar', 'CostoArticuloController@delete');

            Route::delete('/opcionAtributo/borrar', 'ArticuloController@deleteOpcion');

            Route::delete('/atributo/borrar', 'ArticuloController@deleteAtributo');

            Route::get('/orden', 'OrdentrabajoController@index');
            Route::post('/orden/reasignarPedido', 'OrdentrabajoController@reasignarPedido');
            Route::get('/generarOrden', 'OrdentrabajoController@generarOrden');
            Route::get('/orden/hoja-ruta/{id?}', 'OrdentrabajoController@imprimirHojaRuta');
            Route::get('/orden/scan-status/{id}/{proceso}', 'OrdentrabajoController@actualizarEstadoScan');
            Route::get('/imprimirHojaRuta', 'OrdentrabajoController@imprimirHojaRuta');
            Route::get('/orden/hoja-ruta-config', 'OrdentrabajoController@getHojaRutaConfig');
            Route::post('/orden/hoja-ruta-config', 'OrdentrabajoController@saveHojaRutaConfig');
            Route::get('/orden/listaReportesOp', 'OrdentrabajoController@listaReportesOp');
            Route::get('/orden/listaReportesPapel', 'OrdentrabajoController@listaReportesPapel');
            Route::get('/orden/listaReportesTiraje', 'OrdentrabajoController@listaReportesTirajes');
            Route::get('/orden/listaReportesTroquelado', 'OrdentrabajoController@listaReportesTroquelados');
            Route::get('/orden/ordenesProducccion', 'OrdentrabajoController@ordenesProduccion');
            Route::delete('/orden/borrarReportes', 'ReportesController@delete');
            Route::get('/orden/filtrarFecha', 'OrdentrabajoController@filtrarFecha');
            Route::get('/orden/filtrarOrdenes', 'OrdentrabajoController@filtrarOrdenes');
            Route::get('/orden/filtrarEstadoc', 'OrdentrabajoController@filtrarEstadoc');
            Route::post('/orden/procesos', 'OrdentrabajoController@procesos');
            Route::post('/orden/reporteProcesos', 'OrdentrabajoController@reporteProcesos');
            Route::post('/orden/generarReporteProcesos', 'OrdentrabajoController@reporteExcelProcesos');
            Route::post('/orden/generarReporteOrdenes', 'OrdentrabajoController@reporteExcelOrdenes');
            Route::post('/orden/registrar', 'OrdentrabajoController@store');
            Route::put('/orden/actualizar', 'OrdentrabajoController@update');
            Route::post('/orden/duplicar', 'OrdentrabajoController@duplicar');
            Route::delete('/orden/borrar', 'OrdentrabajoController@destroy');
            Route::put('/orden/activar', 'OrdentrabajoController@activar');
            Route::put('/orden/cambiarFecha', 'OrdentrabajoController@cambiarFecha');
            Route::put('/orden/cambiarEstado', 'OrdentrabajoController@cambiarEstado');
            Route::put('/orden/cambiarImpresa', 'OrdentrabajoController@cambiarImpresa');
            Route::put('/orden/actualizarRecibo', 'OrdentrabajoController@actualizarRecibo')->middleware('periodo.abierto');
            Route::delete('/orden/eliminarRecibo', 'OrdentrabajoController@eliminarRecibo')->middleware('periodo.abierto');
            Route::put('/orden/cambiarAbono', 'OrdentrabajoController@cambiarAbono')->middleware('periodo.abierto');
            Route::post('/orden/cambiarProceso', 'OrdentrabajoController@cambiarProceso');
            Route::get('/orden/cartera', 'OrdentrabajoController@cartera');
            Route::get('/orden/estadoCuenta', 'OrdentrabajoController@estadoCuenta');
            Route::get('/orden/alertaCartera', 'OrdentrabajoController@alertaCartera');
            Route::get('/orden/carteraRemisiones', 'OrdentrabajoController@carteraRemisiones');
            Route::get('/orden/carteraRespaldo', 'OrdentrabajoController@carteraRespaldo');
            Route::get('/orden/carteraProyectada', 'OrdentrabajoController@carteraProyectada');
            Route::get('/orden/historialPagos', 'OrdentrabajoController@historialPagos');
            Route::get('/orden/resumenPagos', 'OrdentrabajoController@resumenPagos');
            Route::post('/orden/registrarPago', 'OrdentrabajoController@registrarPago')->middleware('periodo.abierto');
            Route::post('/orden/registrarPagoMasivo', 'OrdentrabajoController@registrarPagoMasivo')->middleware('periodo.abierto');
            Route::post('/orden/convertirAbonoEnRecibo', 'OrdentrabajoController@convertirAbonoEnRecibo')->middleware('periodo.abierto');
            Route::post('/orden/registrarCruce', 'OrdentrabajoController@registrarCruce')->middleware('periodo.abierto');
            Route::post('/orden/cruzarCarteraCliente', 'OrdentrabajoController@cruzarCarteraCliente');
            Route::get('/orden/listarPagos/{id}', 'OrdentrabajoController@listarPagos');
            Route::get('/orden/reciboPdf/{id}', 'OrdentrabajoController@reciboPdf');
            Route::get('/orden/comprobantesClientePendientes', 'OrdentrabajoController@comprobantesClientePendientes');
            Route::get('/orden/ventas', 'OrdentrabajoController@ventas');
            Route::get('/orden/filtrarFechaVentas', 'OrdentrabajoController@filtrarFechaVentas');
            Route::get('/orden/filtrarVentas', 'OrdentrabajoController@filtrarVentas');

            Route::get('/statuspro', 'StatusProduccionController@index');
            Route::get('/statuspro/procesos', 'StatusProduccionController@procesos');
            Route::put('/statuspro/cambiarEstado', 'StatusProduccionController@cambiarEstado');
            Route::put('/statuspro/cambiarPlancha', 'StatusProduccionController@cambiarPlancha');
            Route::post('/statuspro/guardarOpciones', 'StatusProduccionController@guardarOpciones');
            Route::post('/statuspro/guardarMaterial', 'StatusProduccionController@guardarMaterial');
            Route::get('/statuspro/asignarCostos', 'StatusProduccionController@asignarCostos');
            Route::put('/statuspro/cambiarDatosEstado', 'StatusProduccionController@cambiarDatosEstado');
            Route::get('/statuspro/crear', 'StatusProduccionController@crearStatus');

            Route::get('/flujo', 'FlujoProduccionController@index');
            Route::get('/flujo/stats', 'FlujoProduccionController@stats');


            Route::get('/tipos', 'InventarioController@tipos');
            Route::get('/invetario', 'InventarioController@index');

        }
    );
    Route::group(
        ['middleware' => ['Disenador']],
        function () {
            Route::get('/borrarImagen', 'UploadController@borrar');

            Route::get('/categoria', 'CategoriaController@index');
            Route::get('/categoria/articulosCategoria', 'CategoriaController@articulosCategoria');
            Route::post('/categoria/registrar', 'CategoriaController@store');
            Route::post('/categoria/actualizar', 'CategoriaController@update');
            Route::put('/categoria/actualizar', 'CategoriaController@update');
            Route::delete('/categoria/eliminar', 'CategoriaController@eliminar');
            Route::delete('/categoria/eliminarC', 'CategoriaController@eliminarC');
            Route::put('/categoria/activar', 'CategoriaController@activar');
            Route::get('/categoria/selectCategoria', 'CategoriaController@selectCategoria');
            Route::post('/categoria/importarWord', 'CategoriaController@importarWord');

            Route::get('/articulo', 'ArticuloController@index');
            Route::post('/articulo/registrar', 'ArticuloController@store');
            Route::put('/articulo/actualizar', 'ArticuloController@update');
            Route::put('/articulo/cambioximportacion', 'ArticuloController@importar');
            Route::delete('/articulo/eliminar', 'ArticuloController@eliminar');
            Route::put('/articulo/activar', 'ArticuloController@activar');
            Route::get('/articulo/buscarArticulo', 'ArticuloController@buscarArticulo');
            Route::get('/articulo/selectArticulo', 'ArticuloController@selectArticulo');
            Route::get('/articulo/selectArticulobyid', 'ArticuloController@selectArticulobyid');
            Route::get('/articulo/listarArticulo', 'ArticuloController@listarArticulo');
            Route::get('/articulo/crearimagenes', 'ArticuloController@crearimagenes');
            Route::post('/articulo/importarImagenesMasivas', 'ArticuloController@importarImagenesMasivas');
            Route::post('/articulo/asignarCategoriasMasivas', 'ArticuloController@asignarCategoriasMasivas');

            Route::get('/proveedor', 'ProveedorController@index');
            Route::get('/proveedor/selectProveedor', 'ProveedorController@selectProveedor');
            Route::post('/proveedor/registrar', 'ProveedorController@store');
            Route::put('/proveedor/actualizar', 'ProveedorController@update');
            Route::post('/proveedor/eliminar', 'ProveedorController@eliminar');
            Route::delete('/proveedor/eliminar', 'ProveedorController@eliminar');

            Route::get('/comprobante', 'ComprobanteController@index');
            Route::post('/comprobante/registrar', 'ComprobanteController@store');
            Route::put('/comprobante/desactivar', 'ComprobanteController@desactivar');

            Route::get('/cliente', 'ClienteController@index');
            Route::get('/clientes', 'ClienteController@clientes');
            Route::get('/contacto', 'ClienteController@contacto');
            Route::get('/cliente/selectClientes', 'ClienteController@selectclientes');
            Route::get('/cliente/selectCliente', 'ClienteController@selectcliente');
            Route::post('/cliente/registrar', 'ClienteController@store');
            Route::post('/cliente/crearCuenta', 'ClienteController@crearCuenta');
            Route::post('/cliente/crearContacto', 'ClienteController@crearContacto');
            Route::put('/cliente/actualizar', 'ClienteController@update');
            Route::get('/cliente/verificar', 'ClienteController@verificarPersona');
            Route::delete('/datosenvio/borrar', 'ClienteController@eliminarDatose');
            Route::get('/guias', 'ClienteController@guias');
            Route::get('/generarGuia', 'ClienteController@generarguia');

            Route::get('/rol', 'RolController@index');
            Route::get('/rol/selectRol', 'RolController@selectRol');
            Route::post('/rol/registrar', 'RolController@store');
            Route::put('/rol/actualizar', 'RolController@update');
            Route::put('/rol/desactivar', 'RolController@desactivar');
            Route::put('/rol/activar', 'RolController@activar');

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

            Route::delete('/costo/borrar', 'OrdentrabajoController@delete');
            Route::delete('/detalle/borrar', 'DetalletrabajoController@delete');

            Route::delete('/costoArticulo/borrar', 'CostoArticuloController@delete');

            Route::delete('/opcionAtributo/borrar', 'ArticuloController@deleteOpcion');

            Route::delete('/atributo/borrar', 'ArticuloController@deleteAtributo');

            Route::get('/orden', 'OrdentrabajoController@index');
            Route::get('/orden/listaReportesOp', 'OrdentrabajoController@listaReportesOp');
            Route::get('/orden/listaReportesPapel', 'OrdentrabajoController@listaReportesPapel');
            Route::get('/orden/listaReportesTiraje', 'OrdentrabajoController@listaReportesTirajes');
            Route::get('/orden/listaReportesTroquelado', 'OrdentrabajoController@listaReportesTroquelados');
            Route::get('/orden/ordenesProducccion', 'OrdentrabajoController@ordenesProduccion');
            Route::delete('/orden/borrarReportes', 'ReportesController@delete');
            Route::get('/orden/filtrarFecha', 'OrdentrabajoController@filtrarFecha');
            Route::get('/orden/filtrarOrdenes', 'OrdentrabajoController@filtrarOrdenes');
            Route::get('/orden/filtrarEstadoc', 'OrdentrabajoController@filtrarEstadoc');
            Route::post('/orden/procesos', 'OrdentrabajoController@procesos');
            Route::post('/orden/reporteProcesos', 'OrdentrabajoController@reporteProcesos');
            Route::post('/orden/generarReporteProcesos', 'OrdentrabajoController@reporteExcelProcesos');
            Route::post('/orden/generarReporteOrdenes', 'OrdentrabajoController@reporteExcelOrdenes');
            Route::post('/orden/registrar', 'OrdentrabajoController@store');
            Route::put('/orden/actualizar', 'OrdentrabajoController@update');
            Route::post('/orden/duplicar', 'OrdentrabajoController@duplicar');
            Route::delete('/orden/borrar', 'OrdentrabajoController@destroy');
            Route::put('/orden/activar', 'OrdentrabajoController@activar');
            Route::put('/orden/cambiarFecha', 'OrdentrabajoController@cambiarFecha');
            Route::put('/orden/cambiarEstado', 'OrdentrabajoController@cambiarEstado');
            Route::put('/orden/cambiarImpresa', 'OrdentrabajoController@cambiarImpresa');
            Route::put('/orden/cambiarAbono', 'OrdentrabajoController@cambiarAbono');
            Route::post('/orden/cambiarProceso', 'OrdentrabajoController@cambiarProceso');
            Route::get('/orden/cartera', 'OrdentrabajoController@cartera');
            Route::get('/orden/estadoCuenta', 'OrdentrabajoController@estadoCuenta');
            Route::get('/orden/alertaCartera', 'OrdentrabajoController@alertaCartera');
            Route::get('/orden/carteraRespaldo', 'OrdentrabajoController@carteraRespaldo');
            Route::get('/orden/ventas', 'OrdentrabajoController@ventas');
            Route::get('/orden/filtrarFechaVentas', 'OrdentrabajoController@filtrarFechaVentas');
            Route::get('/orden/filtrarVentas', 'OrdentrabajoController@filtrarVentas');

            Route::get('/statuspro', 'StatusProduccionController@index');
            Route::put('/statuspro/cambiarEstado', 'StatusProduccionController@cambiarEstado');
            Route::put('/statuspro/cambiarDatosEstado', 'StatusProduccionController@cambiarDatosEstado');
            Route::get('/statuspro/crear', 'StatusProduccionController@crearStatus');

            Route::get('/flujo', 'FlujoProduccionController@index');
            Route::get('/flujo/stats', 'FlujoProduccionController@stats');
            Route::get('/flujo/procesos', 'FlujoProduccionController@getProcesos');



        }
    );

    Route::group(
        ['middleware' => ['Vendedor']],
        function () {
            Route::get('/cliente', 'ClienteController@index');
            Route::post('/cliente/registrar', 'ClienteController@store');
            Route::put('/cliente/actualizar', 'ClienteController@update');
        }
    );

    Route::group(
        ['middleware' => ['Administrador']],
        function () {
            Route::post(
                '/guardar-colores',
                function (\Illuminate\Http\Request $request) {
                    $data = $request->all();
                    if (empty($data) && $request->getContent()) {
                        $data = json_decode($request->getContent(), true);
                    }
                    $paleta = isset($data['paleta']) ? $data['paleta'] : 'c';
                    $accion = isset($data['accion']) ? $data['accion'] : 'guardar_todo';
                    $colorSingle = isset($data['color']) ? $data['color'] : null;
                    $colores = isset($data['colores']) ? $data['colores'] : [];

                    $file = public_path("colors{$paleta}.json");

                    if ($accion === 'agregar' && $colorSingle && isset($colorSingle['pantone']) && isset($colorSingle['hex'])) {
                        // 1. Insert/update in DB
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('colores')) {
                                \Illuminate\Support\Facades\DB::table('colores')->updateOrInsert(
                                    ['paleta' => $paleta, 'pantone' => $colorSingle['pantone']],
                                    ['hex' => $colorSingle['hex'], 'updated_at' => now(), 'created_at' => now()]
                                );
                            }
                        } catch (\Exception $e) {}

                        // 2. Append/Unshift to JSON file
                        $jsonColores = [];
                        if (file_exists($file)) {
                            $jsonColores = json_decode(file_get_contents($file), true) ?: [];
                        }
                        $jsonColores = array_values(array_filter($jsonColores, function($c) use ($colorSingle) {
                            return isset($c['pantone']) && strtolower(trim($c['pantone'])) !== strtolower(trim($colorSingle['pantone']));
                        }));
                        array_unshift($jsonColores, [
                            'pantone' => $colorSingle['pantone'],
                            'hex'     => $colorSingle['hex']
                        ]);
                        @file_put_contents($file, json_encode($jsonColores, JSON_PRETTY_PRINT));

                        return response()->json(['message' => 'Color agregado correctamente']);
                    }

                    if ($accion === 'eliminar' && $colorSingle && isset($colorSingle['pantone'])) {
                        // 1. Delete from DB
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('colores')) {
                                \Illuminate\Support\Facades\DB::table('colores')
                                    ->where('paleta', $paleta)
                                    ->where('pantone', $colorSingle['pantone'])
                                    ->delete();
                            }
                        } catch (\Exception $e) {}

                        // 2. Delete from JSON file
                        if (file_exists($file)) {
                            $jsonColores = json_decode(file_get_contents($file), true) ?: [];
                            $jsonColores = array_values(array_filter($jsonColores, function($c) use ($colorSingle) {
                                return isset($c['pantone']) && strtolower(trim($c['pantone'])) !== strtolower(trim($colorSingle['pantone']));
                            }));
                            @file_put_contents($file, json_encode($jsonColores, JSON_PRETTY_PRINT));
                        }

                        return response()->json(['message' => 'Color eliminado correctamente']);
                    }

                    // Full replacement (only if colores array is present and not empty)
                    if (!empty($colores)) {
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('colores')) {
                                \Illuminate\Support\Facades\DB::transaction(function () use ($paleta, $colores) {
                                    \Illuminate\Support\Facades\DB::table('colores')->where('paleta', $paleta)->delete();
                                    $insertData = [];
                                    foreach ($colores as $color) {
                                        if (isset($color['pantone']) && isset($color['hex'])) {
                                            $insertData[] = [
                                                'pantone'    => $color['pantone'],
                                                'hex'        => $color['hex'],
                                                'paleta'     => $paleta,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ];
                                        }
                                    }
                                    if (!empty($insertData)) {
                                        foreach (array_chunk($insertData, 500) as $chunk) {
                                            \Illuminate\Support\Facades\DB::table('colores')->insert($chunk);
                                        }
                                    }
                                });
                            }
                        } catch (\Exception $e) {}

                        @file_put_contents($file, json_encode($colores, JSON_PRETTY_PRINT));
                    }

                    return response()->json(['message' => 'Colores procesados correctamente']);
                }
            );

            Route::delete('/mensaje/borrarTodo', 'MensajeController@destroyAll');

            Route::get('/flujo/dia', 'FlujoProduccionController@flujoDelDia');
            Route::post('/flujo/flujoProduccion', 'FlujoProduccionController@flujoProduccion');


            Route::get('/borrarImagen', 'UploadController@borrar');

            Route::get('/empleado', 'EmpleadoController@index');
            Route::post('/empleado/registrar', 'EmpleadoController@crearEmpleado');
            Route::delete('/empleado/borrar', 'EmpleadoController@borrar');
            Route::get('/empleado/getEmpleados', 'EmpleadoController@getEmpleados');
            Route::get('/empleado/selectEmpleado', 'EmpleadoController@selectEmpleado');
            Route::get('/empleado/selectEmpleados', 'EmpleadoController@selectEmpleados');

            Route::get('/categoria', 'CategoriaController@index');
            Route::get('/categoria/articulosCategoria', 'CategoriaController@articulosCategoria');
            Route::post('/categoria/registrar', 'CategoriaController@store');
            Route::post('/categoria/actualizar', 'CategoriaController@update');
            Route::put('/categoria/actualizar', 'CategoriaController@update');
            Route::delete('/categoria/eliminar', 'CategoriaController@eliminar');
            Route::delete('/categoria/eliminarC', 'CategoriaController@eliminarC');
            Route::put('/categoria/activar', 'CategoriaController@activar');
            Route::get('/categoria/selectCategoria', 'CategoriaController@selectCategoria');
            Route::post('/categoria/importarWord', 'CategoriaController@importarWord');

            Route::get('/inventariosMateriasPrimas', 'InventarioController@index');
            Route::get('/inventariosPlanchas', 'InventarioController@planchas');
            Route::delete('/inventarios/eliminar', 'InventarioController@eliminar');
            Route::post('/inventarios/registrar', 'InventarioController@store');
            Route::post('/inventarios/registrarPlancha', 'InventarioController@nuevaPlancha');
            Route::get('/inventarios/valoracion', 'InventarioController@getValoracionInventario');
            Route::get('/inventarios/kardex', 'InventarioController@getMovimientosKardex');
            Route::get('/inventarios/dashboard-costos', 'InventarioController@getDashboardCostos');
            Route::post('/factura-electronica/transmitir', 'FacturaElectronicaController@transmitir');
            Route::get('/factura-electronica/estado/{comprobante_id}', 'FacturaElectronicaController@getEstado');
            Route::get('/factura-electronica/descargar/{tipo}/{id}', 'FacturaElectronicaController@descargar');

            Route::get('/activo', 'ActivosController@index');
            Route::get('/activo/portipo', 'ActivosController@portipo');
            Route::get('/activo/activos', 'ActivosController@activos');
            Route::post('/activo/registrarActivo', 'ActivosController@store');

            Route::post('/movimientos/registrar', 'MovimientoMateriaPrimaController@store');

            Route::get('/articulo', 'ArticuloController@index');
            Route::post('/articulo/registrar', 'ArticuloController@store');
            Route::put('/articulo/actualizar', 'ArticuloController@update');
            Route::put('/articulo/cambioximportacion', 'ArticuloController@importar');
            Route::delete('/articulo/eliminar', 'ArticuloController@eliminar');
            Route::put('/articulo/activar', 'ArticuloController@activar');
            Route::get('/articulo/buscarArticulo', 'ArticuloController@buscarArticulo');
            Route::get('/articulo/selectArticulo', 'ArticuloController@selectArticulo');
            Route::get('/articulo/selectArticulobyid', 'ArticuloController@selectArticulobyid');
            Route::get('/articulo/listarArticulo', 'ArticuloController@listarArticulo');
            Route::get('/articulo/crearimagenes', 'ArticuloController@crearimagenes');
            Route::post('/articulo/importarImagenesMasivas', 'ArticuloController@importarImagenesMasivas');

            // Rutas Troqueles por Cabida
            Route::get('/articulo/troquel/listar', 'ArticuloController@listarTroqueles');
            Route::get('/articulo/troquel/listarTodos', 'ArticuloController@listarTodosLosTroqueles');
            Route::post('/articulo/troquel/guardar', 'ArticuloController@guardarTroquel');
            Route::delete('/articulo/troquel/eliminar', 'ArticuloController@eliminarTroquel');

            Route::post('/atributos/registrar', 'TipoProductoController@crearAtributos');

            Route::get('/proveedor', 'ProveedorController@index');
            Route::get('/proveedor/selectProveedor', 'ProveedorController@selectProveedor');
            Route::post('/proveedor/registrar', 'ProveedorController@store');
            Route::put('/proveedor/actualizar', 'ProveedorController@update');
            Route::post('/proveedor/eliminar', 'ProveedorController@eliminar');
            Route::delete('/proveedor/eliminar', 'ProveedorController@eliminar');

            Route::get('/comprobante', 'ComprobanteController@index');
            Route::post('/comprobante/registrar', 'ComprobanteController@store');
            Route::put('/comprobante/desactivar', 'ComprobanteController@desactivar');
            Route::put('/comprobante/cambiarEstadoFactura', 'ComprobanteController@cambiarEstadoFactura');

            // Route::get('/cliente', 'ClienteController@index');
            Route::get('/cliente', 'ClienteController@clientes');
            Route::get('/percli', 'ClienteController@actualizarClientes');
            Route::get('/contacto', 'ClienteController@contactos');
            Route::get('/envio', 'ClienteController@envios');
            Route::get('/asignarFavorito', 'ClienteController@asignarFavorito');
            Route::get('/facturacion', 'ClienteController@empresas');
            Route::delete('/cuenta/desvincular', 'ClienteController@devincularCuenta');
            Route::delete('/contacto/desvincular', 'ClienteController@devincularContacto');
            Route::delete('/empresa/desvincular', 'ClienteController@devincularEmpresa');
            Route::delete('/envio/desvincular', 'ClienteController@devincularEnvio');
            Route::get('/cliente/rasonsocial', 'ClienteController@llenarsocial');
            Route::get('/cliente/selectOrdenesCliente', 'ClienteController@selectOrdenesCliente');
            Route::post('/cliente/registrar', 'ClienteController@crearCuenta');
            Route::post('/contacto/registrar', 'ClienteController@crearContacto');
            Route::post('/envio/registrar', 'ClienteController@crearEnvio');
            Route::get('/envio/conectarenvios', 'ClienteController@crearEnvios');
            Route::post('/facturacion/registrar', 'ClienteController@crearFacturacion');
            // Route::post('/cliente/registrar', 'ClienteController@store');
            Route::put('/cliente/actualizar', 'ClienteController@update');
            Route::get('/cliente/verificar', 'ClienteController@verificarPersona');
            Route::delete('/datosenvio/borrar', 'ClienteController@eliminarDatose');
            Route::get('/guias', 'ClienteController@guias');
            Route::get('/generarGuia', 'ClienteController@generarguia');

            Route::get('/cliente/selectClientes', 'ClienteController@selectClientes');
            Route::get('/orden/selectOrdenesCliente', 'ClienteController@selectOrdenesCliente');
            Route::get('/contacto/selectContactos', 'ContactoController@selectContactos');
            Route::get('/empresa/selectEmpresas', 'EmpresaController@selectEmpresas');
            Route::get('/envio/selectEnvios', 'EnvioController@selectEnvios');

            Route::delete('/cliente/eliminar', 'ClienteController@eliminarCliente');
            Route::delete('/contacto/eliminar', 'ClienteController@eliminarContacto');
            Route::delete('/facturacion/eliminar', 'ClienteController@eliminarFacturacion');
            Route::delete('/envio/eliminar', 'ClienteController@eliminarEnvio');
            Route::post('/cliente/reasignar', 'ClienteController@reasignarCliente');
            Route::post('/facturacion/reasignar', 'ClienteController@reasignarFacturacion');
            Route::get('/facturacion/selectFacturacion', 'ClienteController@selectFacturacion');

            Route::get('/rol', 'RolController@index');
            Route::get('/rol/selectRol', 'RolController@selectRol');
            Route::post('/rol/registrar', 'RolController@store');
            Route::put('/rol/actualizar', 'RolController@update');
            Route::put('/rol/desactivar', 'RolController@desactivar');
            Route::put('/rol/activar', 'RolController@activar');

            Route::get('/user', 'UserController@index');
            Route::post('/user/registrar', 'UserController@store');
            Route::put('/user/actualizar', 'UserController@update');
            Route::put('/user/desactivar', 'UserController@desactivar');
            Route::put('/user/activar', 'UserController@activar');

            Route::get('/costop', 'CostoisController@index');
            Route::get('/costop/selectInsumos', 'CostoisController@selectInsumos');
            Route::get('/costop/agruparTipos', 'CostoisController@agruparTipos');
            Route::post('/costop/registrar', 'CostoisController@store');
            Route::put('/costop/actualizar', 'CostoisController@update');
            Route::delete('/costop/borrar', 'CostoisController@delete');
            Route::put('/costop/activar', 'CostoisController@activar');

            Route::delete('/costo/borrar', 'OrdentrabajoController@delete');
            Route::delete('/detalle/borrar', 'DetalletrabajoController@delete');

            Route::delete('/costoArticulo/borrar', 'CostoArticuloController@delete');

            Route::delete('/opcionAtributo/borrar', 'ArticuloController@deleteOpcion');

            Route::delete('/atributo/borrar', 'ArticuloController@deleteAtributo');

            Route::post('/factura/registrar', 'ComprobanteController@generarFactura')->middleware('periodo.abierto');

            Route::post('/pedido/registrar', 'ComprobanteController@crearPedido')->middleware('periodo.abierto');
            Route::post('/pedido/generar', 'ComprobanteController@crearPedidos')->middleware('periodo.abierto');
            Route::delete('/pedido/borrar', 'ComprobanteController@eliminar')->middleware('periodo.abierto');
            Route::delete('/factura/borrar', 'ComprobanteController@eliminarFactura')->middleware('periodo.abierto');
            Route::delete('/pedido/eliminarLinea', 'ComprobanteController@eliminarLinea')->middleware('periodo.abierto');
            Route::get('/imprimirPedido', 'ComprobanteController@imprimirPedido');

            Route::get('/orden', 'OrdentrabajoController@index');
            Route::get('/asignarCosto', 'OrdentrabajoController@asignarCosto');
            Route::get('/orden/buscarorden', 'OrdentrabajoController@buscarorden');
            Route::get('/orden/filtroOrden', 'OrdentrabajoController@filtroOrden');
            Route::get('/orden/listaReportesOp', 'OrdentrabajoController@listaReportesOp');
            Route::get('/orden/listaReportesPapel', 'OrdentrabajoController@listaReportesPapel');
            Route::get('/orden/listaReportesTiraje', 'OrdentrabajoController@listaReportesTirajes');
            Route::get('/orden/listaReportesTroquelado', 'OrdentrabajoController@listaReportesTroquelados');
            Route::get('/orden/ordenesProducccion', 'OrdentrabajoController@ordenesProduccion');
            Route::delete('/orden/borrarReportes', 'ReportesController@delete');
            Route::get('/orden/filtrarFecha', 'OrdentrabajoController@filtrarFecha');
            Route::get('/orden/filtrarOrdenes', 'OrdentrabajoController@filtrarOrdenes');
            Route::get('/orden/filtrarEstadoc', 'OrdentrabajoController@filtrarEstadoc');
            Route::post('/orden/procesos', 'OrdentrabajoController@procesos');
            Route::post('/orden/reporteProcesos', 'OrdentrabajoController@reporteProcesos');
            Route::post('/orden/generarReporteProcesos', 'OrdentrabajoController@reporteExcelProcesos');
            Route::post('/orden/generarReporteOrdenes', 'OrdentrabajoController@reporteExcelOrdenes');
            Route::post('/orden/registrar', 'OrdentrabajoController@store');
            Route::put('/orden/actualizar', 'OrdentrabajoController@update');
            Route::post('/orden/duplicar', 'OrdentrabajoController@duplicar');
            Route::post('/orden/actualizarPapel', 'OrdentrabajoController@actualizarPapel');
            Route::delete('/orden/borrar', 'OrdentrabajoController@destroy');
            Route::put('/orden/activar', 'OrdentrabajoController@activar');
            Route::put('/orden/cambiarFecha', 'OrdentrabajoController@cambiarFecha');
            Route::put('/orden/cambiarEstado', 'OrdentrabajoController@cambiarEstado');

            Route::post('/registros/registrar', 'RegistroProduccionController@crearRegistro');
            Route::get('/registros/resumenxempleado', 'RegistroProduccionController@resumenPorEmpleado');
            Route::delete('/registros/borrar', 'RegistroProduccionController@borrar');
            Route::get('/registros/registroEmpleado', 'RegistroProduccionController@registroEmpleado');
            Route::get('/registros/estadisticas', 'RegistroProduccionController@getEstadisticas');

            Route::put('/orden/cambiarImpresa', 'OrdentrabajoController@cambiarImpresa');
            Route::put('/orden/cambiarAbono', 'OrdentrabajoController@cambiarAbono');
            Route::post('/orden/cambiarProceso', 'OrdentrabajoController@cambiarProceso');
            Route::get('/orden/cartera', 'OrdentrabajoController@cartera');
            Route::get('/orden/estadoCuenta', 'OrdentrabajoController@estadoCuenta');
            Route::get('/orden/alertaCartera', 'OrdentrabajoController@alertaCartera');
            Route::get('/orden/carteraRespaldo', 'OrdentrabajoController@carteraRespaldo');
            Route::get('/orden/ventas', 'OrdentrabajoController@ventas');
            Route::get('/orden/filtrarFechaVentas', 'OrdentrabajoController@filtrarFechaVentas');
            Route::get('/orden/filtrarVentas', 'OrdentrabajoController@filtrarVentas');
            Route::post('/orden/convertirAbonoEnRecibo', 'OrdentrabajoController@convertirAbonoEnRecibo');
            Route::get('/orden/reporteProyeccionPapel', 'OrdentrabajoController@reporteProyeccionPapel');


            Route::get('/statuspro', 'StatusProduccionController@index');
            Route::get('/statuspro/procesos', 'StatusProduccionController@procesos');
            Route::get('/statuspro/verificarEnvio', 'StatusProduccionController@verificarEnvio');
            Route::put('/statuspro/cambiarEstado', 'StatusProduccionController@cambiarEstado');
            Route::put('/statuspro/cambiarDatosEstado', 'StatusProduccionController@cambiarDatosEstado');
            Route::put('/statuspro/cambiarPrioridad', 'StatusProduccionController@cambiarPrioridad');
            Route::get('/statuspro/crear', 'StatusProduccionController@crearStatus');
            Route::get('/statuspro/checkUpdates', 'StatusProduccionController@checkUpdates');
            Route::get('/statuspro/llenarprocesos', 'StatusProduccionController@llenarProcesos');
            Route::get('/statuspro/estadosdestatus', 'StatusProduccionController@procesos');
            Route::post('/statuspro/guardaractivo', 'StatusProduccionController@asignarActivo');
            Route::post('/statuspro/asignarOperariaTerminado', 'StatusProduccionController@asignarOperariaTerminado');
            Route::post('/statuspro/actualizarOperariaTerminado', 'StatusProduccionController@actualizarOperariaTerminado');
            Route::post('/statuspro/eliminarOperariaTerminado', 'StatusProduccionController@eliminarOperariaTerminado');
            Route::post('/statuspro/CrearFlujoInicial', 'StatusProduccionController@flujo');
        }
    );




















    Route::post('/orden/subir-hoja-ruta-escaneada', 'OrdentrabajoController@subirHojaRutaEscaneada');
    Route::post('/orden/eliminar-hoja-ruta-escaneada', 'OrdentrabajoController@eliminarHojaRutaEscaneada');
    Route::delete('/orden/eliminar-hoja-ruta-escaneada/{id}', 'OrdentrabajoController@eliminarHojaRutaEscaneada');
    Route::post('/orden/guardar-hoja-ruta-procesos-data', 'OrdentrabajoController@guardarHojaRutaProcesosData');
    Route::get('/orden/obtener-hoja-ruta-procesos-data/{id}', 'OrdentrabajoController@obtenerHojaRutaProcesosData');
    Route::get('/orden/estadisticas-produccion-globales', 'OrdentrabajoController@obtenerEstadisticasProduccionGlobales');
});
