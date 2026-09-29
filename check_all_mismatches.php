<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ot = new \App\Http\Controllers\OrdentrabajoController();

// 1. Cuentas de Cobro
$ccs = \App\Comprobante::where('tipo', 'cuentacobro')->get();
$ccMismatches = [];
foreach ($ccs as $cc) {
    $crucesSum = (float) \App\CruceCartera::where('comprobante_id', $cc->id)->sum('monto');
    if (abs((float)$cc->abono - $crucesSum) > 0.01) {
        $ccMismatches[] = [
            'id' => $cc->id,
            'num' => $cc->num_comprobante ?: $cc->id,
            'abono_guardado' => $cc->abono,
            'cruces_reales' => $crucesSum
        ];
    }
}

// 2. Pedidos
$peds = \App\Comprobante::where('tipo', 'pedido')->get();
$pedMismatches = [];
foreach ($peds as $p) {
    $abonoCalculado = $ot->calcularAbonoRealDelPedido($p->id);
    if (abs((float)$p->abono - $abonoCalculado) > 0.01) {
        $pedMismatches[] = [
            'id' => $p->id,
            'num' => $p->num_comprobante ?: $p->id,
            'abono_guardado' => $p->abono,
            'abono_calculado' => $abonoCalculado
        ];
    }
}

echo "--- Desfasajes en Cuentas de Cobro ---\n";
echo json_encode($ccMismatches, JSON_PRETTY_PRINT) . "\n\n";

echo "--- Desfasajes en Pedidos ---\n";
echo json_encode($pedMismatches, JSON_PRETTY_PRINT) . "\n";
