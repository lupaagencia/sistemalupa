<?php
$logPath = 'storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50);
    foreach ($lastLines as $line) {
        if (strpos($line, 'local.ERROR') !== false || strpos($line, 'exception') !== false) {
            echo $line . "\n";
        }
    }
} else {
    echo "Log file not found.\n";
}
