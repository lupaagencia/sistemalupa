<?php
$content = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
$lines = explode("\n", $content);
$found = false;
foreach ($lines as $i => $line) {
    if (stripos($line, 'ALTER TABLE `users`') !== false) {
        $found = true;
    }
    if ($found) {
        echo ($i + 1) . ": " . trim($line) . "\n";
        if (trim($line) === '' || stripos($line, '--') === 0) {
            // Keep printing until next section
        }
        if (stripos($line, ';') !== false && stripos($line, 'ALTER TABLE') === false) {
            $found = false;
        }
    }
}
