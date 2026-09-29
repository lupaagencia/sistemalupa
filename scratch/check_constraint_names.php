<?php
$content = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
$lines = explode("\n", $content);
$constraints = [];
foreach ($lines as $i => $line) {
    if (preg_match('/CONSTRAINT `([^`]+)` FOREIGN KEY/', $line, $matches)) {
        $constraints[] = [
            'line' => $i + 1,
            'name' => $matches[1],
            'sql' => trim($line)
        ];
    }
}
echo "Constraints in server SQL dump:\n";
print_r($constraints);
