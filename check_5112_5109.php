<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- 1. Pedidos 5112 y 5109 ---\n";
$peds = \App\Comprobante::whereIn('id', [5112, 5109])->get();
echo json_encode($peds->map(function($p) {
    return [
        'id' => $p->id,
        'num' => $p->num_comprobante ?: $p->id,
        'total' => $p->total,
        'abono' => $p->abono,
        'saldo' => $p->saldo
    ];
}), JSON_PRETTY_PRINT) . "\n\n";

echo "--- 2. PedidosRemision Links para 5112 y 5109 ---\n";
$links = \App\PedidoRemision::whereIn('pedido_id', [5112, 5109])->get();
echo json_encode($links, JSON_PRETTY_PRINT) . "\n\n";

$ccIds = $links->pluck('cuentacobro_id')->filter()->unique()->toArray();
echo "--- 3. Cuentas de Cobro asociadas (" . implode(', ', $ccIds) . ") ---\n";
$ccs = \App\Comprobante::whereIn('id', $ccIds)->orWhereIn('pedido_id', [5112, 5109])->get();
echo json_encode($ccs->map(function($c) {
    return [
        'id' => $c->id,
        'num' => $c->num_comprobante ?: $c->id,
        'tipo' => $c->tipo,
        'total' => $c->total,
        'abono' => $c->abono,
        'saldo' => $c->saldo
    ];
}), JSON_PRETTY_PRINT) . "\n\n";

$ccAllIds = $ccs->pluck('id')->toArray();
echo "--- 4. Cruces de Cartera en estas CCs ---\n";
$cruces = \App\CruceCartera::whereIn('comprobante_id', $ccAllIds)->orWhereIn('comprobante_id', [5112, 5109])->get();
echo json_encode($cruces, JSON_PRETTY_PRINT) . "\n\n";

echo "--- 5. Recibos de Pago directos o por cruces ---\n";
$recibos = \App\ReciboPago::whereIn('pedido_id', [5112, 5109])->orWhereIn('id', $cruces->pluck('recibo_pago_id')->toArray())->get();
echo json_encode($recibos, JSON_PRETTY_PRINT) . "\n";
