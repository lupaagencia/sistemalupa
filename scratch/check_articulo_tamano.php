<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$articulos = App\Articulo::whereNotNull('tamano')->limit(5)->get(['id', 'nombre', 'tamano', 'medida_final']);
echo json_encode($articulos, JSON_PRETTY_PRINT);
