<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ccs = \App\Comprobante::where('tipo', 'cuentacobro')->where('num_comprobante', 90)->get();
if ($ccs->isEmpty()) {
    $ccs = \App\Comprobante::where('tipo', 'cuentacobro')->where('id', 90)->get();
}

echo "--- Cuenta de Cobro #90 en DB ---\n";
echo json_encode($ccs->map(function($c) {
    return [
        'id' => $c->id,
        'num_comprobante' => $c->num_comprobante,
        'total' => $c->total,
        'abono' => $c->abono,
        'saldo' => $c->saldo,
        'pedidos' => $c->getPedidoPadreIds()
    ];
}), JSON_PRETTY_PRINT) . "\n\n";

$ccIds = $ccs->pluck('id')->toArray();

echo "--- Cruces de Cartera para CC #90 ---\n";
$cruces = \App\CruceCartera::whereIn('comprobante_id', $ccIds)->get();
echo json_encode($cruces, JSON_PRETTY_PRINT) . "\n\n";

echo "--- Recibos vinculados por recibo_pago_id ---\n";
$reciboIds = $cruces->pluck('recibo_pago_id')->filter()->toArray();
$recibos = \App\ReciboPago::whereIn('id', $reciboIds)->get();
echo json_encode($recibos, JSON_PRETTY_PRINT) . "\n";
