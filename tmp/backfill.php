<?php
use App\Comprobante;
use App\ReciboPago;

echo "Backfilling Comprobantes...\n";
$docs = Comprobante::whereIn('tipo', ['cuentacobro', 'remision', 'factura'])->whereNull('pedido_id')->get();
foreach ($docs as $doc) {
    try {
        $pid = $doc->getPedidoPadreId();
        if ($pid) {
            $doc->pedido_id = $pid;
            $doc->save();
            echo ".";
        }
    } catch (\Exception $e) {
        echo "E";
    }
}

echo "\nBackfilling ReciboPagos...\n";
$recibos = ReciboPago::whereNull('pedido_id')->get();
foreach ($recibos as $recibo) {
    try {
        if ($recibo->comprobante_id) {
            $t = Comprobante::find($recibo->comprobante_id);
            if ($t) {
                $pid = $t->getPedidoPadreId();
                if ($pid) {
                    $recibo->pedido_id = $pid;
                    $recibo->save();
                    echo ".";
                }
            }
        }
    } catch (\Exception $e) {
        echo "X";
    }
}
echo "\nDone!\n";
