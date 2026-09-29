<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = Schema::getAllTables();
foreach ($tables as $t) {
    $name = current((array)$t);
    if (strpos($name, 'quin') !== false || strpos($name, 'liqui') !== false) {
        echo "Table: " . $name . "\n";
    }
}
