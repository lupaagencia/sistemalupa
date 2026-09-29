<?php
$pdo = new PDO('mysql:host=localhost;dbname=dbsistemalaravel', 'root', '');

$stmt = $pdo->query('SHOW COLUMNS FROM costois');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$out = "=== COSTOIS TABLE COLUMNS ===\n";
foreach($rows as $row) { $out .= $row['Field']."\n"; }

$stmt = $pdo->query('SHOW COLUMNS FROM ordentrabajos');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$out .= "\n=== ORDENTRABAJOS TABLE COLUMNS ===\n";
foreach($rows as $row) { $out .= $row['Field']."\n"; }

file_put_contents('tmp/col_names_only.txt', $out);
echo "Done. Written to tmp/col_names_only.txt\n";
