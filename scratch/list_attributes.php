<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\AtributoTienda;

$atributos = AtributoTienda::all();
foreach ($atributos as $a) {
    echo "ID: {$a->id} | Name: {$a->nombre} | Visible: {$a->mostrar_en_producto} | Active: {$a->activo}\n";
}
