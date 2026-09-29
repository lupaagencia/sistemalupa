<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\AtributoTienda;

$atributos = AtributoTienda::all();
foreach ($atributos as $a) {
    if ($a->condicion || $a->formula) {
        echo "ID: {$a->id} | Name: {$a->nombre} | Cond: {$a->condicion} | Formula: {$a->formula}\n";
    }
}
