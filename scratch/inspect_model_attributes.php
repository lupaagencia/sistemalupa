<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ordentrabajo;

$orden = Ordentrabajo::with('detalles')->orderBy('id', 'desc')->first();
if ($orden) {
    echo "Ordentrabajo Model Attributes:\n";
    print_r($orden->toArray());
} else {
    echo "No ordentrabajos found.\n";
}

