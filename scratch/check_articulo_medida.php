<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$articulos = App\Articulo::whereNotNull('medida_final')->limit(10)->get(['id', 'nombre', 'medida_final']);
echo json_encode($articulos, JSON_PRETTY_PRINT);
