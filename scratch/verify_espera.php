<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\CuentaPorPagar;
use App\Proveedor;
use App\Activo;
use Illuminate\Http\Request;
use App\Http\Controllers\CuentasPorPagarController;
use Illuminate\Support\Facades\Schema;

echo "=== Running 'En Espera' Accounts Tests ===\n";

// Authenticate a user for CLI run to satisfy user foreign key constraints
$user = \App\User::first();
$createdUser = false;
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
} else {
    $user = new \App\User();
    $user->usuario = "admin_espera_test";
    $user->password = bcrypt("secret");
    $user->idrol = 1; // Assuming Rol 1 exists
    $user->save();
    \Illuminate\Support\Facades\Auth::login($user);
    $createdUser = true;
}

// 1. Fetch or create a mockup provider for the tests
$proveedor = new Proveedor();
$proveedor->nombre = "Proveedor Prueba Espera Isolado";
$proveedor->num_documento = "999888777";
$proveedor->cupo_credito = 1000000;
$proveedor->save();
$createdProveedor = true;

$proveedorId = $proveedor->id;
echo "Using provider ID: {$proveedorId}\n";

// 2. Create an account with status 'En Espera'
$cuentaEspera = new CuentaPorPagar();
$cuentaEspera->proveedor_id = $proveedorId;
$cuentaEspera->numero_factura = 'TEST-ESPERA-1';
$cuentaEspera->descripcion = "Cuenta de prueba en espera";
$cuentaEspera->cantidad = 1;
$cuentaEspera->valor_unitario = 100000;
$cuentaEspera->monto = 100000;
$cuentaEspera->saldo = 100000;
$cuentaEspera->estado = 'En Espera';
$cuentaEspera->fecha = '2026-06-07';
$cuentaEspera->save();
$esperaId = $cuentaEspera->id;
echo "✅ Created test account 'En Espera' with ID: {$esperaId}\n";

// Create another account in 'Pendiente' to have comparative totals
$cuentaPendiente = new CuentaPorPagar();
$cuentaPendiente->proveedor_id = $proveedorId;
$cuentaPendiente->numero_factura = 'TEST-PEND-1';
$cuentaPendiente->descripcion = "Cuenta de prueba pendiente";
$cuentaPendiente->cantidad = 1;
$cuentaPendiente->valor_unitario = 50000;
$cuentaPendiente->monto = 50000;
$cuentaPendiente->saldo = 50000;
$cuentaPendiente->estado = 'Pendiente';
$cuentaPendiente->fecha = '2026-06-07';
$cuentaPendiente->save();
$pendienteId = $cuentaPendiente->id;
echo "✅ Created test account 'Pendiente' with ID: {$pendienteId}\n";

$controller = new CuentasPorPagarController();

// 3. Test Block registrarAbono on 'En Espera'
echo "Testing registrarAbono on 'En Espera' account (should fail)...\n";
$requestAbono = new Request();
$requestAbono->replace([
    'cuenta_por_pagar_id' => $esperaId,
    'monto' => 10000,
    'fecha' => '2026-06-07',
    'metodo_pago' => 'Banco'
]);

try {
    $response = $controller->registrarAbono($requestAbono);
    $status = $response->getStatusCode();
    $data = json_decode($response->getContent(), true);
    if ($status === 422 && isset($data['error']) && strpos($data['error'], 'En Espera') !== false) {
        echo "✅ registrarAbono was successfully blocked: " . $data['error'] . "\n";
    } else {
        echo "❌ registrarAbono returned status {$status} instead of 422: " . json_encode($data) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ registrarAbono threw an exception: " . $e->getMessage() . "\n";
}

// 4. Test obtain individual account statement calculations (excludability check)
echo "Testing obtenerEstadoCuentaIndividual (totals check)...\n";
$requestStatement = new Request();
$requestStatement->replace([
    'tipo' => 'proveedor',
    'id' => $proveedorId
]);

$statement = $controller->obtenerEstadoCuentaIndividual($requestStatement);

echo "   Total Registrado (API): {$statement['total_registrado']} (Expected: 50000, excluding the 100000 in espera)\n";
echo "   Total Pendiente (API): {$statement['total_pendiente']} (Expected: 50000, excluding the 100000 in espera)\n";

if ($statement['total_registrado'] == 50000 && $statement['total_pendiente'] == 50000) {
    echo "✅ obtenerEstadoCuentaIndividual successfully excluded 'En Espera' account from totals.\n";
} else {
    echo "❌ obtenerEstadoCuentaIndividual totals are wrong: total_registrado={$statement['total_registrado']}, total_pendiente={$statement['total_pendiente']}\n";
}

// 5. Verify that 'En Espera' is in the accounts list returned
$hasEsperaInList = false;
foreach ($statement['cuentas'] as $c) {
    if ($c['id'] == $esperaId) {
        $hasEsperaInList = true;
    }
}
if ($hasEsperaInList) {
    echo "✅ 'En Espera' account is still returned in detail list for display.\n";
} else {
    echo "❌ 'En Espera' account was not found in the detail list!\n";
}

// 6. Test registrarAbonoGeneral excludability
echo "Testing registrarAbonoGeneral (should not touch 'En Espera' account)...\n";
$requestGeneral = new Request();
$requestGeneral->replace([
    'tipo' => 'proveedor',
    'id' => $proveedorId,
    'monto' => 50000,
    'fecha' => '2026-06-07',
    'metodo_pago' => 'Banco'
]);

try {
    $responseGen = $controller->registrarAbonoGeneral($requestGeneral);
    echo "Response status code: " . $responseGen->getStatusCode() . "\n";
    echo "Response content: " . $responseGen->getContent() . "\n";
    
    // Refresh database instances
    $dbPendiente = CuentaPorPagar::find($pendienteId);
    $dbEspera = CuentaPorPagar::find($esperaId);
    
    echo "   Pendiente account status after general abono: {$dbPendiente->estado} (Expected: Pagado), Saldo: {$dbPendiente->saldo}\n";
    echo "   Espera account status after general abono: {$dbEspera->estado} (Expected: En Espera), Saldo: {$dbEspera->saldo}\n";
    
    if ($dbPendiente->estado === 'Pagado' && $dbEspera->estado === 'En Espera' && $dbEspera->saldo == 100000) {
        echo "✅ registrarAbonoGeneral successfully paid the Pendiente account and left the 'En Espera' account untouched.\n";
    } else {
        echo "❌ registrarAbonoGeneral touched 'En Espera' or failed to pay Pendiente!\n";
    }
} catch (\Illuminate\Validation\ValidationException $ve) {
    echo "❌ registrarAbonoGeneral failed validation: " . json_encode($ve->errors()) . "\n";
} catch (\Exception $e) {
    echo "❌ registrarAbonoGeneral threw an exception: " . $e->getMessage() . "\n";
}

// Clean up
$cuentaEspera->delete();
$cuentaPendiente->delete();
// Clean up any abonos created by registrarAbonoGeneral
\App\AbonoCuentaPorPagar::where('cuenta_por_pagar_id', $pendienteId)->delete();
\App\Egresos::where('cuenta_por_pagar_id', $pendienteId)->delete();

if ($createdProveedor) {
    $proveedor->delete();
}

if ($createdUser && isset($user)) {
    $user->delete();
}

echo "=== Tests Finished ===\n";
