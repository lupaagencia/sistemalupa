<?php
$content = file_get_contents(__DIR__ . '/../empaque1_sistema.sql');
if (preg_match('/CREATE TABLE `users` \((.*?)\) ENGINE=InnoDB DEFAULT CHARSET=\w+ COLLATE=\w+/s', $content, $matches)) {
    echo "Table users definition:\n";
    echo $matches[0] . "\n";
} else {
    // Try simpler match
    $pos = strpos($content, 'CREATE TABLE `users`');
    if ($pos !== false) {
        echo "Found CREATE TABLE users. Printing 500 chars:\n";
        echo substr($content, $pos, 1000) . "\n";
    } else {
        echo "Could not find table users definition.\n";
    }
}
