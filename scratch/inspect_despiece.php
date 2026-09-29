<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\DespieceMueble;

$mueble = DespieceMueble::with(['piezas'])->latest()->first();
if ($mueble) {
    echo "=== MUEBLE ===\n";
    print_r($mueble->toArray());
} else {
    echo "No muebles found in db.\n";
}
