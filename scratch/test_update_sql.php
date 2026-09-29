<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tempDb = 'temp_test_update';

try {
    $pdo = DB::connection()->getPdo();
    
    echo "Creating '$tempDb'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$tempDb`");
    $pdo->exec("CREATE DATABASE `$tempDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$tempDb`");
    
    // 1. Import server sql dump
    echo "Importing server SQL dump (empaque1_sistema.sql)...\n";
    $serverSql = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
    $serverSql = preg_replace('/^[ \t]*--.*/m', '', $serverSql);
    $serverSql = preg_replace('/^[ \t]*\/\*.*?\*\//ms', '', $serverSql);
    $queries = preg_split('/;[ \t]*\r?\n/', $serverSql);
    
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    foreach ($queries as $q) {
        $q = trim($q);
        if ($q !== '') {
            $pdo->exec($q);
        }
    }
    
    // 2. Import update_server.sql and capture error
    echo "Importing generated update_server.sql...\n";
    $updateSqlFile = __DIR__ . '/../update_server.sql';
    if (!file_exists($updateSqlFile)) {
        die("update_server.sql does not exist!\n");
    }
    
    $updateSql = file_get_contents($updateSqlFile);
    // Split by semicolon
    $updateSql = preg_replace('/^[ \t]*--.*/m', '', $updateSql);
    $updateSql = preg_replace('/^[ \t]*\/\*.*?\*\//ms', '', $updateSql);
    $queriesUpdate = preg_split('/;[ \t]*\r?\n/', $updateSql);
    
    foreach ($queriesUpdate as $q) {
        $q = trim($q);
        if ($q !== '') {
            try {
                $pdo->exec($q);
            } catch (PDOException $e) {
                echo "\n--- MYSQL ERROR DETECTED ---\n";
                echo "Query: " . $q . "\n";
                echo "Error message: " . $e->getMessage() . "\n";
                echo "----------------------------\n";
            }
        }
    }
    
    // Clean up
    echo "Cleaning up...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$tempDb`");
    echo "Done!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
