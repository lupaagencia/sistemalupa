<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Rol;
use App\User;

echo "=== CONFIGURANDO ROL SUPERADMINISTRADOR Y USUARIO JULIAN ===\n";

// 1. Crear el rol Superadministrador si no existe
$rolSuper = Rol::where('nombre', 'Superadministrador')->first();
if (!$rolSuper) {
    $rolSuper = new Rol();
    $rolSuper->nombre = 'Superadministrador';
    $rolSuper->descripcion = 'Super Administrador con control total del sistema';
    $rolSuper->condicion = 1;
    $rolSuper->produccion = 0;
    $rolSuper->save();
    echo "✔ Rol 'Superadministrador' creado con ID: " . $rolSuper->id . "\n";
} else {
    echo "ℹ El rol 'Superadministrador' ya existía con ID: " . $rolSuper->id . "\n";
}

// 2. Asignar el rol Superadministrador al usuario julian
$users = User::where('usuario', 'julian')->orWhere('usuario', 'LIKE', '%julian%')->get();
if ($users->count() > 0) {
    foreach ($users as $user) {
        $user->idrol = 'Superadministrador';
        $user->save();
        echo "✔ Usuario '" . $user->usuario . "' (ID: " . $user->id . ") actualizado a rol 'Superadministrador'\n";
    }
} else {
    echo "⚠️ No se encontró ningún usuario con nombre 'julian'\n";
}

echo "=== PROCESO COMPLETADO ===\n";
