<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\CostoProduccion;
use App\CuentaPorPagar;
use App\Activo;

echo "--- 5 Terminado Costos ---\n";
$costs = CostoProduccion::where('titulo', 'Terminado')->limit(5)->get();
foreach ($costs as $c) {
    echo "ID: {$c->id}, ordentrabajo_id: {$c->ordentrabajo_id}, costois_id: {$c->costois_id}, cantidad: {$c->cantidad}, valor: {$c->valor}, total: {$c->total}\n";
    // Check if costois_id matches an Activo
    $act = Activo::find($c->costois_id);
    if ($act) {
        echo "  -> Matches Activo ID {$act->id}: Name '{$act->activo}', Type '{$act->tipo}'\n";
    } else {
        echo "  -> No Activo found for ID {$c->costois_id}\n";
    }
    // Check if costois_id matches a Costois record
    $cos = \App\Costois::find($c->costois_id);
    if ($cos) {
        echo "  -> Matches Costois ID {$cos->id}: Name '{$cos->nombre}', Type '{$cos->tipo_costo}'\n";
    } else {
        echo "  -> No Costois found for ID {$c->costois_id}\n";
    }
    // Check associated CuentaPorPagar
    $cuenta = CuentaPorPagar::where('costo_id', $c->id)->first();
    if ($cuenta) {
        echo "  -> Account: ID {$cuenta->id}, activo_id: {$cuenta->activo_id}, cantidad: {$cuenta->cantidad}, monto: {$cuenta->monto}, saldo: {$cuenta->saldo}, estado: {$cuenta->estado}\n";
    } else {
        echo "  -> No Account found\n";
    }
}
