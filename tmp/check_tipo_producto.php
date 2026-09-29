<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- TIPO_PRODUCTO ---\n";
$columns = DB::select('SHOW COLUMNS FROM tipo_producto');
foreach ($columns as $column) {
    echo "COLUMN: " . $column->Field . " TYPE: " . $column->Type . "\n";
}

echo "\n--- COSTO_ARTICULOS ---\n";
$columns = DB::select('SHOW COLUMNS FROM costo_articulos');
foreach ($columns as $column) {
    echo "COLUMN: " . $column->Field . " TYPE: " . $column->Type . "\n";
}
