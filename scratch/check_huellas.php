<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emps = App\Empleado::whereNotNull('huella_dactilar')->where('huella_dactilar', '!=', '')->get(['id', 'nombre', 'huella_dactilar']);
foreach ($emps as $e) {
    echo "ID: {$e->id}, Nombre: {$e->nombre}, Huella: " . substr($e->huella_dactilar, 0, 50) . "...\n";
}
