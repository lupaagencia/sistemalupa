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

echo "=== Laravel Bootstrapped Successfully ===\n";

// 1. Verify table columns
$columns = Schema::getColumnListing('cuentas_por_pagar');
echo "Columns in 'cuentas_por_pagar': " . implode(', ', $columns) . "\n";
$hasNumeroFactura = in_array('numero_factura', $columns);
$hasSoporte = in_array('soporte', $columns);
$hasVencimiento = in_array('fecha_vencimiento', $columns);

if ($hasNumeroFactura && $hasSoporte && $hasVencimiento) {
    echo "✅ Database schema has all required columns.\n";
} else {
    echo "❌ Database schema missing columns!\n";
    exit(1);
}

// 2. Fetch a dummy provider and active
$proveedor = Proveedor::first();
$operaria = Activo::where('tipo', 'Terminado')->first();

if (!$proveedor) {
    echo "⚠️ No providers found in database. Using a mockup ID.\n";
    $proveedorId = 1;
} else {
    $proveedorId = $proveedor->id;
    echo "Found provider: {$proveedor->nombre} (ID: {$proveedorId})\n";
}

if (!$operaria) {
    echo "⚠️ No operarias of Terminado found in database. Using a mockup ID.\n";
    $operariaId = 1;
} else {
    $operariaId = $operaria->id;
    echo "Found operaria: {$operaria->activo} (ID: {$operariaId})\n";
}

// 3. Test Controller Store method
$controller = new CuentasPorPagarController();

$request = new Request();
$request->replace([
    'proveedor_id' => $proveedorId,
    'numero_factura' => 'TEST-INV-12345',
    'monto' => 150000,
    'fecha' => '2026-06-02',
    'fecha_vencimiento' => '2026-07-02'
]);

echo "Testing CuentaPorPagarController@store...\n";
$response = $controller->store($request);
$data = json_decode($response->getContent(), true);

if (isset($data['success']) && $data['success']) {
    echo "✅ Cuenta por Pagar created successfully: ID " . $data['cuenta']['id'] . "\n";
    $cuentaId = $data['cuenta']['id'];
} else {
    echo "❌ Failed to create Cuenta por Pagar: " . json_encode($data) . "\n";
    exit(1);
}

// 4. Test obtaining Individual Account Statement (Default Pending View)
echo "Testing obtaining account statement for provider ID {$proveedorId} (Default Pending)...\n";
$statementRequest = new Request();
$statementRequest->replace([
    'tipo' => 'proveedor',
    'id' => $proveedorId
]);
$statementResponse = $controller->obtenerEstadoCuentaIndividual($statementRequest);
if (isset($statementResponse['nombre']) && isset($statementResponse['total_pendiente'])) {
    echo "✅ Account statement obtained successfully: " . $statementResponse['nombre'] . ", Pendiente: " . $statementResponse['total_pendiente'] . "\n";
} else {
    echo "❌ Failed to obtain account statement!\n";
    exit(1);
}

// 5. Test obtaining Individual Account Statement (Filtered by Date Range)
echo "Testing obtaining account statement for provider ID {$proveedorId} (Date Filtered: 2026-06-01 to 2026-06-30)...\n";
$filterRequest = new Request();
$filterRequest->replace([
    'tipo' => 'proveedor',
    'id' => $proveedorId,
    'fecha_inicio' => '2026-06-01',
    'fecha_fin' => '2026-06-30'
]);
$filterResponse = $controller->obtenerEstadoCuentaIndividual($filterRequest);
if (isset($filterResponse['fecha_inicio']) && $filterResponse['fecha_inicio'] === '2026-06-01' && isset($filterResponse['abonos'])) {
    echo "✅ Filtered account statement obtained successfully. Date range: " . $filterResponse['fecha_inicio'] . " to " . $filterResponse['fecha_fin'] . "\n";
    echo "   Returned accounts count: " . count($filterResponse['cuentas']) . "\n";
    echo "   Returned abonos count: " . count($filterResponse['abonos']) . "\n";
} else {
    echo "❌ Failed to obtain filtered account statement!\n";
    exit(1);
}

// Clean up test account
$testCuenta = CuentaPorPagar::find($cuentaId);
if ($testCuenta) {
    $testCuenta->delete();
    echo "✅ Cleaned up test Cuenta por Pagar.\n";
}

echo "=== All Tests Completed Successfully ===\n";
