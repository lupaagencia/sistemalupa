<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $databases = DB::select("SHOW DATABASES");
    foreach ($databases as $db) {
        $dbName = $db->Database;
        echo "Database: $dbName\n";
        try {
            // Check if egresos table exists in this DB
            $tables = DB::select("SHOW TABLES FROM `$dbName` LIKE 'egresos'");
            if (count($tables) > 0) {
                echo "  -> Has 'egresos' table. Let's describe it:\n";
                $cols = DB::select("SHOW COLUMNS FROM `$dbName`.`egresos` LIKE 'cuenta_contable'");
                if (count($cols) > 0) {
                    print_r($cols);
                } else {
                    echo "    (No cuenta_contable column)\n";
                }
            }
        } catch (Exception $e) {
            echo "  Error: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
