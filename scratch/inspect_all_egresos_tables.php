<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$databases = ['copia', 'copia 2', 'copia hosting', 'dbsistemalaravel'];
foreach ($databases as $db) {
    echo "=== Columns of $db.egresos ===\n";
    try {
        $cols = DB::select("SHOW COLUMNS FROM `$db`.`egresos`");
        foreach ($cols as $c) {
            echo "  {$c->Field} ({$c->Type}) - Null: {$c->Null}, Default: " . json_encode($c->Default) . "\n";
        }
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
