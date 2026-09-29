<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $pdo = DB::connection()->getPdo();
    $db = DB::connection()->getDatabaseName();
    
    $stmt = $pdo->prepare("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                           FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                           WHERE TABLE_SCHEMA = ? 
                             AND TABLE_NAME = 'comprobantes_contables' 
                             AND REFERENCED_TABLE_NAME IS NOT NULL");
    $stmt->execute([$db]);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Foreign Keys of comprobantes_contables in local DB:\n";
    print_r($fks);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
