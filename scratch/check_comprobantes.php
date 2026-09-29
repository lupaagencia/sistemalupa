<?php
$content = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
echo "File size: " . strlen($content) . " bytes\n";
if (stripos($content, 'comprobantes') !== false) {
    echo "Found 'comprobantes' in file!\n";
    // Print lines containing it
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'comprobantes') !== false) {
            echo ($i + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "Did NOT find 'comprobantes' in file.\n";
}
