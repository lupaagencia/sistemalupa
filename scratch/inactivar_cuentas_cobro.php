<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \DB::table('comprobantes')
    ->where('tipo', 'cuentacobro')
    ->update(['estado' => 'Invalida']);

echo "Exito: Se actualizaron {$count} cuentas de cobro existentes a estado 'Invalida'.\n";
