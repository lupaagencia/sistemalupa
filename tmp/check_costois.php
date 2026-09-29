<?php
$pdo = new PDO('mysql:host=localhost;dbname=dbsistemalaravel', 'root', '');
$stmt = $pdo->query('SHOW COLUMNS FROM costois');
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
file_put_contents('tmp/costois_cols.txt', implode("\n", $cols));
echo "Done. ".count($cols)." cols written.\n";
