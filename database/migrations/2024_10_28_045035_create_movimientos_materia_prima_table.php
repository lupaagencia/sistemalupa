<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosMateriaPrimaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimientos_materia_prima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_materia_prima_id')->constrained('inventario_materia_prima')->onDelete('cascade');
            $table->foreignId('proveedores_id')->constrained('proveedores')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida']);
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('costo_total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimientos_materia_prima');
    }
}
