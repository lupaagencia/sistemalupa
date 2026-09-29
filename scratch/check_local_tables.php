<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = DB::select("SHOW TABLES");
    $db = DB::connection()->getDatabaseName();
    $prop = "Tables_in_" . $db;
    
    $tableList = [];
    foreach ($tables as $t) {
        $tableList[] = $t->$prop;
    }
    
    echo "Tables in local database:\n";
    print_r($tableList);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
