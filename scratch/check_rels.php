<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\AtributoTienda;

$atributos = AtributoTienda::with('tiposProducto')->get();
foreach ($atributos as $a) {
    $tpIds = $a->tiposProducto->pluck('id')->toArray();
    echo "ID: {$a->id} | Name: {$a->nombre} | TP IDs: " . implode(',', $tpIds) . "\n";
}
