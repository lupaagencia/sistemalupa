<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $triggers = DB::select("SHOW TRIGGERS");
    print_r($triggers);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
