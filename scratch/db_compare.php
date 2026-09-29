<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Configure execution parameters
$localDb = DB::connection()->getDatabaseName();
$serverSqlFile = __DIR__ . '/../empaque1_sistema.sql';
$tempDbName = 'temp_empaque_server';

echo "Local Database: $localDb\n";
echo "Server SQL File: $serverSqlFile\n";
echo "Temporary Database: $tempDbName\n\n";

try {
    $pdo = DB::connection()->getPdo();
    
    // 1. Create temporary database
    echo "Creating temporary database '$tempDbName'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$tempDbName`");
    $pdo->exec("CREATE DATABASE `$tempDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$tempDbName`");
    
    // 2. Read and parse/execute server SQL dump
    echo "Importing server SQL file into '$tempDbName'...\n";
    if (!file_exists($serverSqlFile)) {
        throw new Exception("Server SQL file not found at: $serverSqlFile");
    }
    
    $sqlContent = file_get_contents($serverSqlFile);
    // Remove comments
    $sqlContent = preg_replace('/^[ \t]*--.*/m', '', $sqlContent);
    $sqlContent = preg_replace('/^[ \t]*\/\*.*?\*\//ms', '', $sqlContent);
    
    // Split queries by semicolon followed by newline
    $queries = preg_split('/;[ \t]*\r?\n/', $sqlContent);
    
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    
    $successCount = 0;
    $failCount = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if ($query !== '') {
            try {
                $pdo->exec($query);
                $successCount++;
            } catch (PDOException $e) {
                // If there's an error, output it
                echo "Error executing query: " . substr($query, 0, 80) . "...\n";
                echo "Reason: " . $e->getMessage() . "\n\n";
                $failCount++;
            }
        }
    }
    echo "Import finished: $successCount queries succeeded, $failCount queries failed.\n\n";
    
    // 3. Retrieve schemas
    echo "Retrieving schema metadata...\n";
    
    // Get Tables
    $getTables = function($db) use ($pdo) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'");
        $stmt->execute([$db]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    };
    
    $tablesLocal = $getTables($localDb);
    $tablesServer = $getTables($tempDbName);
    
    // Get Columns
    $getColumns = function($db) use ($pdo) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLUMN_KEY 
                               FROM INFORMATION_SCHEMA.COLUMNS 
                               WHERE TABLE_SCHEMA = ? 
                               ORDER BY TABLE_NAME, ORDINAL_POSITION");
        $stmt->execute([$db]);
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row;
        }
        return $columns;
    };
    
    $colsLocal = $getColumns($localDb);
    $colsServer = $getColumns($tempDbName);
    
    // Get Indexes
    $getIndexes = function($db) use ($pdo) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE 
                               FROM INFORMATION_SCHEMA.STATISTICS 
                               WHERE TABLE_SCHEMA = ? 
                               ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX");
        $stmt->execute([$db]);
        $indexes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $indexes[$row['TABLE_NAME']][$row['INDEX_NAME']][] = $row;
        }
        return $indexes;
    };
    
    $indexesLocal = $getIndexes($localDb);
    $indexesServer = $getIndexes($tempDbName);
    
    // 4. Generate comparison SQL
    $outputSql = [];
    $outputSql[] = "-- ==========================================================================";
    $outputSql[] = "-- SQL MIGRATION SCRIPT FOR SERVER DATABASE (to match local database schema)";
    $outputSql[] = "-- Generated on: " . date('Y-m-d H:i:s');
    $outputSql[] = "-- Local database: $localDb";
    $outputSql[] = "-- Server SQL file used: " . basename($serverSqlFile);
    $outputSql[] = "-- ==========================================================================";
    $outputSql[] = "";
    $outputSql[] = "SET FOREIGN_KEY_CHECKS = 0;";
    $outputSql[] = "";

    // A. Tables to Create (exist in local, but not in server)
    $tablesToCreate = array_diff($tablesLocal, $tablesServer);
    
    // Sort tablesToCreate by dependencies so referenced tables are created first
    if (!empty($tablesToCreate)) {
        $dependencies = [];
        foreach ($tablesToCreate as $tableName) {
            $stmt = $pdo->prepare("SELECT DISTINCT REFERENCED_TABLE_NAME 
                                   FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                                   WHERE TABLE_SCHEMA = ? 
                                     AND TABLE_NAME = ? 
                                     AND REFERENCED_TABLE_NAME IS NOT NULL");
            $stmt->execute([$localDb, $tableName]);
            $deps = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $dependencies[$tableName] = array_intersect($deps, $tablesToCreate);
        }
        
        $sortedTables = [];
        $visited = [];
        $visit = function($table) use (&$visit, &$sortedTables, &$visited, $dependencies) {
            if (isset($visited[$table])) {
                return;
            }
            $visited[$table] = true;
            foreach ($dependencies[$table] as $dep) {
                $visit($dep);
            }
            $sortedTables[] = $table;
        };
        
        foreach ($tablesToCreate as $tableName) {
            $visit($tableName);
        }
        $tablesToCreate = $sortedTables;
    }
    if (!empty($tablesToCreate)) {
        $outputSql[] = "-- --------------------------------------------------------";
        $outputSql[] = "-- TABLES TO CREATE";
        $outputSql[] = "-- --------------------------------------------------------";
        foreach ($tablesToCreate as $tableName) {
            $stmt = $pdo->query("SHOW CREATE TABLE `$localDb`.`$tableName`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $createTableSql = $row['Create Table'];
            // Add IF NOT EXISTS for safety and idempotency
            $createTableSql = str_replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $createTableSql);
            // Normalize AUTO_INCREMENT if needed, but we can keep it
            $outputSql[] = $createTableSql . ";";
            $outputSql[] = "";
            
            // If it is 'cuentas', also dump the local data (PUC)
            if ($tableName === 'cuentas') {
                $rows = $pdo->query("SELECT * FROM `$localDb`.`cuentas`")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $outputSql[] = "-- --------------------------------------------------------";
                    $outputSql[] = "-- DUMPING DATA FOR TABLE `cuentas` (Plan Único de Cuentas - PUC)";
                    $outputSql[] = "-- --------------------------------------------------------";
                    foreach ($rows as $r) {
                        $cols = array_keys($r);
                        $escapedCols = array_map(function($c) { return "`$c`"; }, $cols);
                        $vals = array_map(function($v) use ($pdo) {
                            if ($v === null) return 'NULL';
                            return $pdo->quote($v);
                        }, array_values($r));
                        $outputSql[] = "INSERT INTO `cuentas` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $vals) . ") ON DUPLICATE KEY UPDATE `id`=`id`;";
                    }
                    $outputSql[] = "";
                }
            }
        }
    }

    // B. Tables to Drop (exist in server, but not in local)
    $tablesToDrop = array_diff($tablesServer, $tablesLocal);
    if (!empty($tablesToDrop)) {
        $outputSql[] = "-- --------------------------------------------------------";
        $outputSql[] = "-- TABLES TO DROP (Commented out for safety)";
        $outputSql[] = "-- --------------------------------------------------------";
        foreach ($tablesToDrop as $tableName) {
            $outputSql[] = "-- DROP TABLE `$tableName`;";
        }
        $outputSql[] = "";
    }

    // Helper function to format column definition
    $formatColumnDef = function($col) use ($pdo) {
        $def = $col['COLUMN_TYPE'];
        if ($col['IS_NULLABLE'] === 'NO') {
            $def .= ' NOT NULL';
        } else {
            $def .= ' NULL';
        }
        
        if ($col['COLUMN_DEFAULT'] !== null) {
            if ($col['COLUMN_DEFAULT'] === 'CURRENT_TIMESTAMP') {
                $def .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif (strtoupper($col['COLUMN_DEFAULT']) === 'NULL') {
                $def .= ' DEFAULT NULL';
            } else {
                // Remove outer quotes/parentheses that sometimes appear in INFORMATION_SCHEMA
                $defaultVal = $col['COLUMN_DEFAULT'];
                // If it's a numeric default, don't necessarily need quotes, but SQL quote is safe
                $def .= ' DEFAULT ' . $pdo->quote($defaultVal);
            }
        }
        
        if (!empty($col['EXTRA'])) {
            $def .= ' ' . strtoupper($col['EXTRA']);
        }
        return $def;
    };

    // C. Table Column and Index Modifications (for tables that exist in both)
    $commonTables = array_intersect($tablesLocal, $tablesServer);
    
    $hasModifications = false;
    $modificationsSql = [];
    
    foreach ($commonTables as $tableName) {
        $tableLocalCols = $colsLocal[$tableName] ?? [];
        $tableServerCols = $colsServer[$tableName] ?? [];
        
        $tableLocalColNames = array_keys($tableLocalCols);
        $tableServerColNames = array_keys($tableServerCols);
        
        $tableAlterActions = [];
        
        // 1. Missing columns in server (exist in local, but not in server)
        $colsToAdd = array_diff($tableLocalColNames, $tableServerColNames);
        foreach ($colsToAdd as $colName) {
            $colInfo = $tableLocalCols[$colName];
            $colDef = $formatColumnDef($colInfo);
            
            // Determine position (AFTER or FIRST)
            $position = '';
            $colIndex = array_search($colName, $tableLocalColNames);
            if ($colIndex === 0) {
                $position = ' FIRST';
            } elseif ($colIndex > 0) {
                $prevColName = $tableLocalColNames[$colIndex - 1];
                $position = " AFTER `$prevColName`";
            }
            
            $tableAlterActions[] = "ADD COLUMN `$colName` $colDef$position";
        }
        
        // 2. Extra columns in server (exist in server, but not in local) - commented out for safety
        $colsToDrop = array_diff($tableServerColNames, $tableLocalColNames);
        foreach ($colsToDrop as $colName) {
            $tableAlterActions[] = "/* DROP COLUMN `$colName` */";
        }
        
        // 3. Modified columns (exist in both but properties differ)
        $colsToCompare = array_intersect($tableLocalColNames, $tableServerColNames);
        foreach ($colsToCompare as $colName) {
            $localCol = $tableLocalCols[$colName];
            $serverCol = $tableServerCols[$colName];
            
            // Compare type, nullability, default value, extra
            $typeDiff = $localCol['COLUMN_TYPE'] !== $serverCol['COLUMN_TYPE'];
            $nullDiff = $localCol['IS_NULLABLE'] !== $serverCol['IS_NULLABLE'];
            
            // Normalize defaults for comparison
            $localDefault = $localCol['COLUMN_DEFAULT'];
            $serverDefault = $serverCol['COLUMN_DEFAULT'];
            
            // In MariaDB, sometimes defaults are wrapped in parentheses (e.g. '0' vs 0)
            $normalizeDefault = function($val) {
                if ($val === null) return null;
                $val = trim($val, "'() ");
                if (strtolower($val) === 'null') return null;
                return $val;
            };
            
            $defaultDiff = $normalizeDefault($localDefault) !== $normalizeDefault($serverDefault);
            $extraDiff = strtoupper($localCol['EXTRA']) !== strtoupper($serverCol['EXTRA']);
            
            if ($typeDiff || $nullDiff || $defaultDiff || $extraDiff) {
                $colDef = $formatColumnDef($localCol);
                $tableAlterActions[] = "MODIFY COLUMN `$colName` $colDef";
            }
        }
        
        // 4. Compare indexes/keys
        $tableLocalIndexes = $indexesLocal[$tableName] ?? [];
        $tableServerIndexes = $indexesServer[$tableName] ?? [];
        
        $localIndexNames = array_keys($tableLocalIndexes);
        $serverIndexNames = array_keys($tableServerIndexes);
        
        // Missing indexes in server (exist in local, but not in server)
        $indexesToAdd = array_diff($localIndexNames, $serverIndexNames);
        foreach ($indexesToAdd as $indexName) {
            $indexCols = $tableLocalIndexes[$indexName];
            $colNames = [];
            foreach ($indexCols as $c) {
                $colNames[] = "`" . $c['COLUMN_NAME'] . "`";
            }
            $colsStr = implode(', ', $colNames);
            
            if ($indexName === 'PRIMARY') {
                $tableAlterActions[] = "ADD PRIMARY KEY ($colsStr)";
            } elseif ($indexCols[0]['NON_UNIQUE'] == 0) {
                $tableAlterActions[] = "ADD UNIQUE INDEX `$indexName` ($colsStr)";
            } else {
                $tableAlterActions[] = "ADD INDEX `$indexName` ($colsStr)";
            }
        }
        
        // Extra indexes in server (exist in server, but not in local) - commented out for safety
        $indexesToDrop = array_diff($serverIndexNames, $localIndexNames);
        foreach ($indexesToDrop as $indexName) {
            if ($indexName === 'PRIMARY') {
                $tableAlterActions[] = "/* DROP PRIMARY KEY */";
            } else {
                $tableAlterActions[] = "/* DROP INDEX `$indexName` */";
            }
        }
        
        if (!empty($tableAlterActions)) {
            $hasModifications = true;
            $alterSql = "ALTER TABLE `$tableName` \n  " . implode(",\n  ", $tableAlterActions) . ";";
            
            // Clean up visual formatting if some lines are comments
            // Replace ",\n  /*" with "\n  /*" and so on if needed, but MySQL allows multiple actions separated by commas.
            // Note: comments /* DROP ... */ inside an ALTER TABLE list are tricky in syntax, so let's separate them.
            $cleanActions = [];
            $comments = [];
            foreach ($tableAlterActions as $action) {
                if (strpos($action, '/*') === 0) {
                    $comments[] = str_replace(['/*', '*/'], '', $action);
                } else {
                    $cleanActions[] = $action;
                }
            }
            
            $tableModLines = [];
            if (!empty($cleanActions)) {
                $tableModLines[] = "ALTER TABLE `$tableName` \n  " . implode(",\n  ", $cleanActions) . ";";
            }
            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    $tableModLines[] = "-- ALTER TABLE `$tableName` " . trim($comment) . ";";
                }
            }
            
            $modificationsSql[] = implode("\n", $tableModLines);
        }
    }
    
    if ($hasModifications) {
        $outputSql[] = "-- --------------------------------------------------------";
        $outputSql[] = "-- TABLE MODIFICATIONS (ALTER TABLE)";
        $outputSql[] = "-- --------------------------------------------------------";
        $outputSql[] = implode("\n\n", $modificationsSql);
        $outputSql[] = "";
    }
    
    $outputSql[] = "SET FOREIGN_KEY_CHECKS = 1;";
    $outputSql[] = "";
    
    // Write out the result SQL file
    $outputSqlFile = __DIR__ . '/../update_server.sql';
    file_put_contents($outputSqlFile, implode("\n", $outputSql));
    
    echo "Comparison complete!\n";
    echo "The SQL script to update the server has been saved to: $outputSqlFile\n\n";
    
    // Clean up temporary database
    echo "Cleaning up temporary database '$tempDbName'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$tempDbName`");
    echo "Done!\n";
    
} catch (Exception $e) {
    echo "An error occurred:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
