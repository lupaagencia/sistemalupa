<?php

/**
 * Script de verificación para el Módulo de Costos e Inventarios (Fase 5)
 * Ejecuta transaccionalmente pruebas sobre valoración de inventario,
 * Kárdex y agregación de consumos (COGS), revirtiendo todos los cambios.
 */

// 1. Inicializar Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use App\Costois;
use App\InventariosMateriaPrima;
use App\MovimientoMateriaPrima;
use App\Http\Controllers\InventarioController;
use Carbon\Carbon;

echo "=========================================================\n";
echo " INICIANDO VERIFICACIÓN DE VALORACIÓN Y COSTOS DE INVENTARIO \n";
echo "=========================================================\n\n";

DB::beginTransaction();

try {
    // 2. Buscar proveedor para idproveedor / idpersona
    $prov = DB::table('proveedores')->first();
    $idProv = $prov ? $prov->id : 1;

    // Crear insumo base (Costois)
    $insumo = Costois::create([
        'idproveedor' => $idProv,
        'idpersona' => $idProv,
        'nombre' => 'Papel Test Verificacion 250g',
        'descripcion' => 'Insumo de prueba para verificación de costos',
        'unidad_medida' => 'Pliegos',
        'valor' => 500.00, // Costo unitario
        'total' => 500.00,
        'estado' => 'Activo',
        'tipo_costo' => 'Directo'
    ]);
    
    echo "✔ Insumo de prueba creado. ID: {$insumo->id}, Valor Unitario: $500.00 COP\n";

    // 3. Crear registro de Inventario
    $inventario = InventariosMateriaPrima::create([
        'costois_id' => $insumo->id,
        'referencia' => $insumo->nombre,
        'tipo' => 'Materia Prima',
        'cantidad' => 100, // Stock inicial
        'estado' => 'Disponible',
        'ubicacion' => 'Bodega Central'
    ]);

    echo "✔ Registro de inventario creado. ID: {$inventario->id}, Stock inicial: 100 pliegos\n";

    // 4. Registrar movimientos históricos en la base de datos
    // Entrada de 100 pliegos
    $movEntrada = MovimientoMateriaPrima::create([
        'inventarios_materia_prima_id' => $inventario->id,
        'proveedores_id' => 0,
        'tipo' => 'entrada',
        'cantidad' => 100,
        'costo_unitario' => 500.00,
        'costo_total' => 50000.00
    ]);
    
    // Salida (Consumo) de 30 pliegos
    $movSalida = MovimientoMateriaPrima::create([
        'inventarios_materia_prima_id' => $inventario->id,
        'proveedores_id' => 0,
        'tipo' => 'salida',
        'cantidad' => 30,
        'costo_unitario' => 500.00,
        'costo_total' => 15000.00
    ]);

    echo "✔ Movimientos de Kárdex registrados (Entrada: +100 unidades, Salida: -30 unidades)\n";

    // Ajustar stock final después del consumo en el modelo (100 - 30 = 70)
    $inventario->cantidad = 70;
    $inventario->save();

    // 5. Invocar lógica del controlador de Inventario
    $controller = new InventarioController();

    // Prueba 5.1: Valoración de Inventario
    $requestVal = new \Illuminate\Http\Request([
        'buscar' => 'Papel Test Verificacion 250g',
        'criterio' => 'referencia'
    ]);
    $responseVal = $controller->getValoracionInventario($requestVal);
    $dataVal = $responseVal->getData(true);

    echo "\n--- Evaluando getValoracionInventario ---\n";
    echo "Valoración Total en Respuesta: $" . number_format($dataVal['valoracion_total'], 2) . "\n";
    echo "Cantidad de items filtrados: " . $dataVal['items_count'] . "\n";
    
    // Assert de valoración (70 pliegos * 500.00 unitario = 35,000.00 valuation)
    $itemFiltrado = $dataVal['detalles'][0];
    echo "Nombre: {$itemFiltrado['referencia']}, Stock: {$itemFiltrado['cantidad']}, Val: $" . number_format($itemFiltrado['valoracion'], 2) . "\n";
    
    if ($itemFiltrado['valoracion'] == 35000.00) {
        echo "   PASSED: Valoración unitaria y total calculada correctamente.\n";
    } else {
        throw new \Exception("   FAILED: Valoración incorrecta. Esperaba 35000, obtenido " . $itemFiltrado['valoracion']);
    }

    // Prueba 5.2: Historial Kárdex
    $requestKardex = new \Illuminate\Http\Request([
        'buscar' => 'Papel Test Verificacion 250g',
        'tipo' => ''
    ]);
    $responseKardex = $controller->getMovimientosKardex($requestKardex);
    $dataKardex = $responseKardex->getData(true);

    echo "\n--- Evaluando getMovimientosKardex ---\n";
    echo "Registros encontrados en Kárdex: " . count($dataKardex['movimientos']) . "\n";
    
    if (count($dataKardex['movimientos']) >= 2) {
        echo "   PASSED: Historial de Kárdex recupera correctamente los movimientos.\n";
    } else {
        throw new \Exception("   FAILED: El Kárdex no reportó los movimientos creados.");
    }

    // Prueba 5.3: Dashboard de Costos y COGS
    $requestDash = new \Illuminate\Http\Request();
    $responseDash = $controller->getDashboardCostos($requestDash);
    $dataDash = $responseDash->getData(true);

    echo "\n--- Evaluando getDashboardCostos ---\n";
    echo "Valoración Inventario Global: $" . number_format($dataDash['valoracion_inventario'], 2) . "\n";
    echo "Costo de Ventas (COGS) Mes Actual: $" . number_format($dataDash['cogs_mes_actual'], 2) . "\n";
    echo "Entradas del Mes: $" . number_format($dataDash['entradas_mes'], 2) . "\n";
    echo "Salidas del Mes: $" . number_format($dataDash['salidas_mes'], 2) . "\n";

    if ($dataDash['cogs_mes_actual'] >= 15000.00 && $dataDash['entradas_mes'] >= 50000.00) {
        echo "   PASSED: Agregaciones mensuales del Dashboard de costos correctas.\n";
    } else {
        throw new \Exception("   FAILED: Las métricas del Dashboard no sumaron correctamente los movimientos de prueba.");
    }

    echo "\n=========================================================\n";
    echo " VERIFICACIÓN EXITOSA - TODO OPERA SEGÚN ESPECIFICACIONES \n";
    echo "=========================================================\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR EN LA VERIFICACIÓN: " . $e->getMessage() . "\n";
} finally {
    // Revertir todos los cambios de prueba
    DB::rollBack();
    echo "\n✔ Transacción revertida. Base de datos limpia.\n";
}
