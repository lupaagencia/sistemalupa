<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$newTables = [
    'asientos_detalles',
    'comprobantes_contables',
    'cuentas',
    'extractos_bancarios',
    'facturas_electronicas',
    'liquidacion_primas',
    'periodos_contables'
];

foreach ($newTables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "Table '$table': $count rows locally\n";
    } catch (Exception $e) {
        echo "Table '$table' count failed: " . $e->getMessage() . "\n";
    }
}
