<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Proveedor;
use App\CuentaPorPagar;
use App\AbonoCuentaPorPagar;

$provs = Proveedor::all();

echo "Found " . count($provs) . " providers:\n";

foreach ($provs as $prov) {
    echo "ID: {$prov->id} | Nombre: {$prov->nombre} | Doc: {$prov->num_documento} | Cupo: {$prov->cupo_credito}\n";
    
    $cuentas = CuentaPorPagar::where('proveedor_id', $prov->id)->with('abonos')->get();
    echo "Cuentas por Pagar (" . count($cuentas) . "):\n";
    foreach ($cuentas as $c) {
        echo "  Cuenta #{$c->id} | Factura: '{$c->numero_factura}' | Desc: '{$c->descripcion}' | Monto: {$c->monto} | Saldo: {$c->saldo} | Estado: {$c->estado}\n";
        foreach ($c->abonos as $a) {
            echo "    -> Abono #{$a->id} | Monto: {$a->monto} | Metodo: {$a->metodo_pago} | Obs: '{$a->observaciones}' | Soporte: '{$a->soporte}'\n";
        }
    }
}
