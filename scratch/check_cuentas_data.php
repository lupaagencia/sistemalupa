<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $count = DB::table('cuentas')->count();
    echo "Total rows in 'cuentas' table locally: $count\n\n";
    if ($count > 0) {
        $rows = DB::table('cuentas')->limit(10)->get();
        echo "First 10 rows in 'cuentas':\n";
        print_r($rows->toArray());
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
