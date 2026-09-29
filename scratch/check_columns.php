<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "--- proveedores table columns ---\n";
print_r(Schema::getColumnListing('proveedores'));

echo "\n--- personas table columns ---\n";
print_r(Schema::getColumnListing('personas'));

echo "\n--- clientes table columns ---\n";
print_r(Schema::getColumnListing('clientes'));

