<?php
$pdo = new PDO('mysql:host=localhost;dbname=dbsistemalaravel', 'root', '');

$out = "=== costois ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM costois');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) $out .= $row['Field'].' | '.$row['Type']."\n";

$out .= "\n=== ordentrabajos ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM ordentrabajos');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) $out .= $row['Field'].' | '.$row['Type']."\n";

file_put_contents('tmp/all_cols.txt', $out);
echo "Done.\n";
