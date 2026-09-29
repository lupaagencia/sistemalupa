<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function printColumns($table)
{
    echo "--- TABLE: $table ---\n";
    $columns = DB::select("SHOW COLUMNS FROM $table");
    foreach ($columns as $column) {
        echo $column->Field . " | " . $column->Type . "\n";
    }
    echo "\n";
}

printColumns('tipo_producto');
printColumns('costo_articulos');
