<?php
$pdo = new PDO('mysql:host=localhost;dbname=dbsistemalaravel', 'root', '');

$queries = [
    "ALTER TABLE ordentrabajos CHANGE ancho_material tamano DECIMAL(20,2)",
    "ALTER TABLE ordentrabajos CHANGE largo_material medida_material VARCHAR(50)",
    "ALTER TABLE ordentrabajos CHANGE unidad_medida cabida VARCHAR(20)",
    "ALTER TABLE ordentrabajos CHANGE idcostois medida_final VARCHAR(50)",
];

foreach($queries as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $sql\n";
    } catch(Exception $e) {
        echo "ERROR: ".$e->getMessage()."\n   SQL: $sql\n";
    }
}
echo "\nDone.\n";
