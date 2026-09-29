<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagosProgramadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagos_programados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('concepto');
            $table->string('categoria')->default('Préstamo / Crédito'); // Préstamo / Crédito, Impuesto / Tributario, Vehicular (SOAT/Tecno), Arriendo / Servicios, Seguros / Licencias, Otro
            $table->unsignedInteger('proveedor_id')->nullable();
            $table->decimal('monto_estimado', 12, 2)->default(0);
            $table->string('frecuencia')->default('Mensual'); // Mensual, Bimensual, Cuatrimestral, Semestral, Anual, Única vez
            $table->date('proxima_fecha_pago');
            $table->integer('recordatorio_dias')->default(5);
            $table->unsignedInteger('cuenta_id')->nullable(); // PUC
            $table->string('estado')->default('Activo'); // Activo, Pausado, Finalizado
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('set null');
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagos_programados');
    }
}
