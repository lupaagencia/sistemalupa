<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pedidos = \App\Comprobante::where('tipo', 'pedido')->get();
$actualizados = 0;
$controller = new \App\Http\Controllers\OrdentrabajoController();

foreach ($pedidos as $pedido) {
    $abonoViejo = $pedido->abono;
    $saldoViejo = $pedido->saldo;
    
    $controller->verificarYActualizarPedido($pedido->id);
    
    $pedidoF = \App\Comprobante::find($pedido->id);
    if ($pedidoF && ($pedidoF->abono != $abonoViejo || $pedidoF->saldo != $saldoViejo)) {
        echo "Pedido #{$pedido->id} (Num: {$pedido->num_comprobante}): Abono de {$abonoViejo} -> {$pedidoF->abono}, Saldo de {$saldoViejo} -> {$pedidoF->saldo}\n";
        $actualizados++;
    }
}

echo "Finalizado! Se recalcularon y sincronizaron {$actualizados} pedidos.\n";
