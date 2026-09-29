<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ordentrabajo;
use App\CostoProduccion;
use App\CuentaPorPagar;
use App\Cliente;
use App\Articulo;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // 1. Find or create active client and article
    $cliente = Cliente::first();
    $articulo = Articulo::first();

    if (!$cliente || !$articulo) {
        throw new Exception("Please run migrations and seeds first; need at least one client and one article to test.");
    }

    echo "Testing quantity sync for Ordentrabajo...\n";

    // 2. Create test Ordentrabajo
    $orden = new Ordentrabajo();
    $orden->cliente_id = $cliente->id;
    $orden->articulo_id = $articulo->id;
    $orden->cantidad = 1000;
    $orden->produccion = 'ENP';
    $orden->estado = 'OSP';
    $orden->total = 10000;
    $orden->save();
    echo "Created Ordentrabajo #{$orden->id} with quantity = 1000\n";

    // 3. Create finishing CostoProduccion
    $costo = new CostoProduccion();
    $costo->ordentrabajo_id = $orden->id;
    $costo->costois_id = 1; // dummy active ID
    $costo->titulo = 'Terminado';
    $costo->cantidad = 1000;
    $costo->valor = 5.50;
    $costo->total = 5500;
    $costo->save();
    echo "Created CostoProduccion #{$costo->id} ('Terminado') with quantity = 1000, valor = 5.50, total = 5500\n";

    // 4. Create CuentaPorPagar
    $cuenta = new CuentaPorPagar();
    $cuenta->activo_id = $costo->costois_id;
    $cuenta->ordentrabajo_id = $orden->id;
    $cuenta->costo_id = $costo->id;
    $cuenta->descripcion = "Terminado test description";
    $cuenta->cantidad = 1000;
    $cuenta->valor_unitario = 5.50;
    $cuenta->monto = 5500;
    $cuenta->saldo = 5500;
    $cuenta->estado = 'Pendiente';
    $cuenta->fecha = date('Y-m-d');
    $cuenta->save();
    echo "Created CuentaPorPagar #{$cuenta->id} with quantity = 1000, monto = 5500, saldo = 5500\n";

    // 5. Update Ordentrabajo quantity
    echo "Updating Ordentrabajo quantity to 1500...\n";
    $orden->cantidad = 1500;
    $orden->save();

    // 6. Refresh Costo and Cuenta from DB and assert
    $costoRefreshed = CostoProduccion::find($costo->id);
    $cuentaRefreshed = CuentaPorPagar::find($cuenta->id);

    echo "Checking assertions...\n";
    
    // Check Costo quantity
    if ($costoRefreshed->cantidad != 1500) {
        throw new Exception("Assertion failed: CostoProduccion quantity is {$costoRefreshed->cantidad}, expected 1500");
    }
    // Check Costo total
    $expectedTotal = 1500 * 5.50;
    if ($costoRefreshed->total != $expectedTotal) {
        throw new Exception("Assertion failed: CostoProduccion total is {$costoRefreshed->total}, expected {$expectedTotal}");
    }
    echo "✓ CostoProduccion synchronized correctly!\n";

    // Check Cuenta quantity
    if ($cuentaRefreshed->cantidad != 1500) {
        throw new Exception("Assertion failed: CuentaPorPagar quantity is {$cuentaRefreshed->cantidad}, expected 1500");
    }
    // Check Cuenta monto
    if ($cuentaRefreshed->monto != $expectedTotal) {
        throw new Exception("Assertion failed: CuentaPorPagar monto is {$cuentaRefreshed->monto}, expected {$expectedTotal}");
    }
    // Check Cuenta saldo
    if ($cuentaRefreshed->saldo != $expectedTotal) {
        throw new Exception("Assertion failed: CuentaPorPagar saldo is {$cuentaRefreshed->saldo}, expected {$expectedTotal}");
    }
    echo "✓ CuentaPorPagar synchronized correctly!\n";

    echo "All assertions passed successfully! DB transaction will now be rolled back to keep DB clean.\n";

    DB::rollBack();
} catch (Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
