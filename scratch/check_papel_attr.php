<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$atributos = App\AtributoTienda::where('tipo', 'Papel')->get();
echo json_encode($atributos, JSON_PRETTY_PRINT);
