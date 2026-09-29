<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = DB::select('DESC articulos');
foreach ($columns as $column) {
    echo $column->Field . " (" . $column->Type . ")\n";
}
