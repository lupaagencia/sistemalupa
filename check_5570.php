<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$comps = \App\Comprobante::whereIn('id', [5570, 5592])->get();
echo "--- Comprobantes 5570, 5592 ---\n";
echo json_encode($comps->map(function($c) {
    return [
        'id' => $c->id,
        'tipo' => $c->tipo,
        'num' => $c->num_comprobante,
        'total' => $c->total,
        'abono' => $c->abono,
        'saldo' => $c->saldo,
        'pedidos' => $c->getPedidoPadreIds()
    ];
}), JSON_PRETTY_PRINT) . "\n\n";

echo "--- Cruces asociados a 5570, 5592 ---\n";
$cruces = \App\CruceCartera::whereIn('comprobante_id', [5570, 5592])->get();
echo json_encode($cruces, JSON_PRETTY_PRINT) . "\n";
