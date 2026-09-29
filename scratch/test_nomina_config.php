<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ajustes;
use App\Empleado;

echo "1. Current Auxilio Transporte: " . Ajustes::getAuxilioTransporte() . "\n";
echo "2. Current Salario Minimo: " . Ajustes::getSalarioMinimo() . "\n";

// Test update
$req = Illuminate\Http\Request::create('/ajustes/nomina-config', 'POST', [
    'auxilio_transporte' => 200000,
    'salario_minimo' => 1400000,
    'aplicar_a_empleados' => true
]);

$controller = new \App\Http\Controllers\AjustesController();
$res = $controller->saveNominaConfig($req);

echo "3. Save Response: " . json_encode($res->getData()) . "\n";
echo "4. Updated Auxilio Transporte: " . Ajustes::getAuxilioTransporte() . "\n";
