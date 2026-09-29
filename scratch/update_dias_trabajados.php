<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE `liquidacion_quincena` MODIFY COLUMN `dias_trabajados` DECIMAL(8,2) NOT NULL DEFAULT 15.00");
    echo "1. liquidacion_quincena.dias_trabajados converted to DECIMAL(8,2) successfully.\n";
} catch (\Exception $e) {
    echo "1. Error: " . $e->getMessage() . "\n";
}

try {
    DB::statement("ALTER TABLE `liquidacion_primas` MODIFY COLUMN `dias_trabajados` DECIMAL(8,2) NOT NULL DEFAULT 180.00");
    echo "2. liquidacion_primas.dias_trabajados converted to DECIMAL(8,2) successfully.\n";
} catch (\Exception $e) {
    echo "2. Error: " . $e->getMessage() . "\n";
}
