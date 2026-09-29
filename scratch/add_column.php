<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('empleados', 'huella_dactilar')) {
    Schema::table('empleados', function (Blueprint $table) {
        $table->longText('huella_dactilar')->nullable();
    });
    echo "Columna huella_dactilar agregada exitosamente.\n";
} else {
    echo "La columna huella_dactilar ya existe.\n";
}
