<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\AtributoTienda;

$atributos = AtributoTienda::orderBy('id', 'asc')->get();
echo "ID | Name | Type | Visible | Active\n";
foreach ($atributos as $a) {
    echo "{$a->id} | {$a->nombre} | {$a->tipo} | {$a->mostrar_en_producto} | {$a->activo}\n";
}
