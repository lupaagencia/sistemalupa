<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- 1. Cruces con Recibos Inexistentes ---\n";
$orphanCruces = \App\CruceCartera::whereDoesntHave('reciboPago')->get();
echo json_encode($orphanCruces, JSON_PRETTY_PRINT) . "\n\n";

echo "--- 2. Cuentas de Cobro con Abonos mayores a la suma de sus cruces vigentes ---\n";
$ccs = \App\Comprobante::where('tipo', 'cuentacobro')->get();
$mismatchedCCs = [];
foreach ($ccs as $cc) {
    $realCruces = (float) \App\CruceCartera::where('comprobante_id', $cc->id)->sum('monto');
    if (abs((float)$cc->abono - $realCruces) > 0.01) {
        $mismatchedCCs[] = [
            'id' => $cc->id,
            'num' => $cc->num_comprobante ?: $cc->id,
            'abono_guardado' => $cc->abono,
            'saldo_guardado' => $cc->saldo,
            'cruces_reales' => $realCruces
        ];
    }
}
echo json_encode($mismatchedCCs, JSON_PRETTY_PRINT) . "\n\n";

echo "--- 3. Recibos de Pago Existentes ---\n";
$recibos = \App\ReciboPago::orderBy('id', 'desc')->take(10)->get(['id', 'num_recibo', 'monto', 'pedido_id', 'created_at']);
echo json_encode($recibos, JSON_PRETTY_PRINT) . "\n";
