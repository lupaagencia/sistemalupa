<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $results = DB::select("SHOW CREATE TABLE egresos");
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
