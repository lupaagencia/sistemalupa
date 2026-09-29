<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('tipo_producto', 'formula_ancho')) {
    Schema::table('tipo_producto', function (Blueprint $table) {
        $table->string('formula_ancho')->nullable();
        $table->string('formula_largo')->nullable();
    });
    echo "Columns added to tipo_producto table.";
} else {
    echo "Columns already exist.";
}
