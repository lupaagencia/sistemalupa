<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['c', 'p', 'u'] as $p) {
    $f = public_path("colors{$p}.json");
    if (file_exists($f)) {
        $c = json_decode(file_get_contents($f), true);
        echo "=== colors{$p}.json (Count: " . count($c) . ") ===" . PHP_EOL;
        echo "First 2: " . json_encode(array_slice($c, 0, 2)) . PHP_EOL;
        echo "Last 2: " . json_encode(array_slice($c, -2)) . PHP_EOL;
    }
}
