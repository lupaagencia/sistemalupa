<?php
$content = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
$lines = explode("\n", $content);
$tables = [];
foreach ($lines as $line) {
    if (preg_match('/CREATE TABLE `([^`]+)`/', $line, $matches)) {
        $tables[] = $matches[1];
    }
}
echo "Tables in server SQL dump:\n";
print_r($tables);
