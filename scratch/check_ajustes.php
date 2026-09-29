<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ajustes;
use Illuminate\Support\Facades\Schema;

echo "Ajustes table exists: " . (Schema::hasTable('ajustes') ? 'YES' : 'NO') . "\n";
if (Schema::hasTable('ajustes')) {
    $rows = Ajustes::where('tipo', 'nomina')->get();
    echo "Nomina settings count: " . count($rows) . "\n";
    foreach ($rows as $r) {
        echo " - {$r->tipo} | {$r->detalle} | {$r->valor}\n";
    }
}
