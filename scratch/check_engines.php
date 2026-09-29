<?php
$content = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
$lines = explode("\n", $content);
$engines = [];
$currentTable = '';
foreach ($lines as $line) {
    if (preg_match('/CREATE TABLE `([^`]+)`/', $line, $matches)) {
        $currentTable = $matches[1];
    }
    // Find engine line e.g. ENGINE=InnoDB or ENGINE=MyISAM
    if ($currentTable && preg_match('/\) ENGINE=([a-zA-Z]+)/', $line, $matches)) {
        $engines[$currentTable] = $matches[1];
        $currentTable = '';
    }
}
echo "Table Engines in server SQL dump:\n";
print_r($engines);
