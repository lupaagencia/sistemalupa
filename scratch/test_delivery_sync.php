<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ordentrabajo;
use App\Comprobante;
use App\LineaComprobante;
use App\Cliente;
use App\Articulo;
use App\Http\Controllers\EntregasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

try {
    DB::beginTransaction();

    // Find first client and article
    $cliente = Cliente::first();
    $articulo = Articulo::first();

    if (!$cliente || !$articulo) {
        throw new Exception("Please ensure database has at least one client and article.");
    }

    echo "Setting up Pedido and Ordentrabajo for test...\n";

    // 1. Create a parent Pedido (Comprobante)
    $pedido = new Comprobante();
    $pedido->tipo = 'pedido';
    $pedido->cliente_id = $cliente->id;
    $pedido->user_id = 1;
    $pedido->fecha = date('Y-m-d');
    $pedido->iva = 0.19; // 19% tax rate
    $pedido->subtotal = 5000.00; // 1000 units * 5.00
    $pedido->impuestos = 950.00; // 5000 * 0.19
    $pedido->total = 5950.00;
    $pedido->abono = 1000.00; // prepaid amount
    $pedido->saldo = 4950.00;
    $pedido->estado = 2; // En producción
    $pedido->fuente_id = 0;
    $pedido->datos_factura_id = 0;
    $pedido->save();
    echo "Created Pedido #{$pedido->id}\n";

    // Create a real ReciboPago for this Pedido so abono calculations verify correctly
    $recibo = new \App\ReciboPago();
    $recibo->cliente_id = $cliente->id;
    $recibo->pedido_id = $pedido->id;
    $recibo->user_id = 1;
    $recibo->monto = 1000.00;
    $recibo->fecha = date('Y-m-d');
    $recibo->saldo_recibo = 0.00;
    $recibo->forma_pago = 'Efectivo';
    $recibo->num_recibo = 99999;
    $recibo->save();
    echo "Created ReciboPago #{$recibo->id} for Pedido #{$pedido->id} with amount = 1000.00\n";

    // 2. Create Ordentrabajo
    $orden = new Ordentrabajo();
    $orden->cliente_id = $cliente->id;
    $orden->articulo_id = $articulo->id;
    $orden->cantidad = 1000;
    $orden->valor_unitario = 5.00;
    $orden->produccion = 'P';
    $orden->estado = 'OSP';
    $orden->totalParcial = 5000.00;
    $orden->total = 5000.00;
    $orden->save();
    echo "Created Ordentrabajo #{$orden->id}\n";

    // 3. Create LineaComprobante linking Pedido and Ordentrabajo
    $linea = new LineaComprobante();
    $linea->comprobante_id = $pedido->id;
    $linea->ordentrabajo_id = $orden->id;
    $linea->articulo_id = $articulo->id;
    $linea->cantidad = 1000;
    $linea->valor_unitario = 5.00;
    $linea->subtotal = 5000.00;
    $linea->valor_total = 5000.00;
    $linea->fecha = date('Y-m-d');
    $linea->save();
    echo "Created LineaComprobante #{$linea->id}\n";

    // Create a dummy statusProduccion
    $status = new \App\statusProduccion();
    $status->estado = 'Corte material';
    $status->idorden = $orden->id;
    $status->observaciones = '';
    $status->prioridad = 0;
    $status->fecha_termina = date('Y-m-d');
    $status->hora = date('H:i:s');
    $status->save();

    // Force login a user for testing Auth::id()
    $user = DB::table('users')->first();
    if ($user) {
        Auth::loginUsingId($user->id);
    }

    echo "Invoking EntregasController@store with quantity = 1100 (exceeds balance of 1000)...\n";

    $request = new Request();
    $request->replace([
        'ordentrabajo_id' => $orden->id,
        'cantidad' => 1100, // 100 units more than original order/line item!
        'tipo_documento' => 'Remision',
        'observaciones' => 'Testing delivery exceeding balance'
    ]);

    $controller = new EntregasController();
    $response = $controller->store($request);

    echo "Response status: " . $response->getStatusCode() . "\n";
    $data = json_decode($response->getContent(), true);
    echo "Response data: " . json_encode($data) . "\n";

    if (empty($data['success'])) {
        throw new Exception("Response did not indicate success! Error: " . ($data['error'] ?? 'Unknown'));
    }

    // Refresh and check Ordentrabajo
    $orden->refresh();
    echo "--- Ordentrabajo values ---\n";
    echo "Cantidad: {$orden->cantidad} (Expected: 1100)\n";
    echo "Cantidad Entregada: {$orden->cantidad_entregada} (Expected: 1100)\n";
    echo "Produccion: {$orden->produccion} (Expected: T)\n";

    if ($orden->cantidad != 1100) {
        throw new Exception("Ordentrabajo cantidad not updated to 1100!");
    }
    if ($orden->cantidad_entregada != 1100) {
        throw new Exception("Ordentrabajo cantidad_entregada not updated to 1100!");
    }

    // Refresh and check LineaComprobante
    $linea->refresh();
    echo "--- LineaComprobante values ---\n";
    echo "Cantidad: {$linea->cantidad} (Expected: 1100)\n";
    echo "Subtotal: {$linea->subtotal} (Expected: 5500)\n";
    echo "Valor Total: {$linea->valor_total} (Expected: 5500)\n";

    if ($linea->cantidad != 1100) {
        throw new Exception("LineaComprobante cantidad not updated to 1100!");
    }
    if ($linea->subtotal != 5500.00) {
        throw new Exception("LineaComprobante subtotal not updated to 5500!");
    }

    // Refresh and check parent Pedido Comprobante
    $pedido->refresh();
    echo "--- Parent Pedido (Comprobante) values ---\n";
    echo "Subtotal: {$pedido->subtotal} (Expected: 5500.00)\n";
    echo "Impuestos: {$pedido->impuestos} (Expected: 1045.00)\n";
    echo "Total: {$pedido->total} (Expected: 6545.00)\n";
    echo "Abono: {$pedido->abono} (Expected: 1000.00)\n";
    echo "Saldo: {$pedido->saldo} (Expected: 5545.00)\n";

    if (abs($pedido->subtotal - 5500.00) > 0.01) {
        throw new Exception("Pedido subtotal is incorrect!");
    }
    if (abs($pedido->impuestos - 1045.00) > 0.01) {
        throw new Exception("Pedido impuestos is incorrect!");
    }
    if (abs($pedido->total - 6545.00) > 0.01) {
        throw new Exception("Pedido total is incorrect!");
    }
    if (abs($pedido->saldo - 5545.00) > 0.01) {
        throw new Exception("Pedido saldo is incorrect!");
    }

    echo "✓ All assertions passed successfully! DB transaction will now be rolled back.\n";
    DB::rollBack();
} catch (Exception $e) {
    DB::rollBack();
    echo "Test failed! Error: " . $e->getMessage() . "\n";
    exit(1);
}
