<?php
$pdo = new PDO('mysql:host=localhost;dbname=dbsistemalaravel', 'root', '');

// Check if migration is already run
$stmt = $pdo->query("SELECT * FROM migrations WHERE migration = '2026_03_18_053954_rename_columns_in_ordentrabajos'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {
    echo "Migration already recorded.\n";
} else {
    // Get max batch
    $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $batch = ($row['max_batch'] ?? 0) + 1;
    
    $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
    $stmt->execute(['2026_03_18_053954_rename_columns_in_ordentrabajos', $batch]);
    echo "Migration recorded in batch $batch.\n";
}
