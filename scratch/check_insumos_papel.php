<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$insumos = App\Costois::where('nombre', 'LIKE', '%Cartulina%')->orWhere('nombre', 'LIKE', '%Papel%')->limit(10)->get();
echo json_encode($insumos, JSON_PRETTY_PRINT);
