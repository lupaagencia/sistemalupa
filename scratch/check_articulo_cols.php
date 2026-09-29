<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = Schema::getColumnListing('articulos');
echo json_encode($columns, JSON_PRETTY_PRINT);
