<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$sql = [];
$sql[] = "SET FOREIGN_KEY_CHECKS = 0;";
$sql[] = "TRUNCATE TABLE `cuentas`;\n";

foreach (DB::table('cuentas')->get() as $row) {
    $vals = [];
    foreach ((array)$row as $col => $val) {
        if ($val === null) {
            $vals[] = "NULL";
        } else {
            $vals[] = "'" . addslashes($val) . "'";
        }
    }
    $sql[] = "INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES (" . implode(", ", $vals) . ") ON DUPLICATE KEY UPDATE `codigo`=VALUES(`codigo`);";
}

$sql[] = "\nSET FOREIGN_KEY_CHECKS = 1;";

file_put_contents(__DIR__ . '/../insert_cuentas_local.sql', implode("\n", $sql));
echo "Archivo SQL de cuentas generado exitosamente en insert_cuentas_local.sql\n";
