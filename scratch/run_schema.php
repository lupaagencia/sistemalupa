<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Dropping old tables...\n";
    Schema::dropIfExists('detalle_ingresos');
    Schema::dropIfExists('ingresos');

    echo "Creating 'ingresos' table...\n";
    Schema::create('ingresos', function ($table) {
        $table->increments('id');
        $table->integer('idproveedor')->unsigned();
        $table->foreign('idproveedor')->references('id')->on('proveedores')->onDelete('cascade');
        $table->integer('idusuario')->unsigned();
        $table->foreign('idusuario')->references('id')->on('users')->onDelete('cascade');
        $table->string('tipo_comprobante', 20);
        $table->string('serie_comprobante', 7)->nullable();
        $table->string('num_comprobante', 10);
        $table->dateTime('fecha_hora');
        $table->decimal('impuesto', 4, 2)->default(0);
        $table->decimal('total', 11, 2);
        $table->string('estado', 20)->default('Registrado');
        $table->string('forma_pago', 20)->default('Contado'); // 'Contado', 'Crédito'
        $table->integer('dias_credito')->nullable(); // Credit days
        $table->timestamps();
    });

    echo "Creating 'detalle_ingresos' table...\n";
    Schema::create('detalle_ingresos', function ($table) {
        $table->increments('id');
        $table->integer('idingreso')->unsigned();
        $table->foreign('idingreso')->references('id')->on('ingresos')->onDelete('cascade');
        $table->integer('idarticulo')->unsigned();
        $table->foreign('idarticulo')->references('id')->on('articulos')->onDelete('cascade');
        $table->integer('cantidad');
        $table->decimal('precio', 11, 2);
        $table->timestamps();
    });

    echo "Successfully updated database tables!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
