<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiquidacionQuincenaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liquidacion_quincena', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empleado_id'); // Signed int(11) to match empleados.id
            $table->date('fecha_pago');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_trabajados')->default(15);
            $table->decimal('salario_base', 12, 2);
            $table->decimal('sueldo_neto', 12, 2);
            $table->decimal('auxilio_transporte', 12, 2)->default(0.00);
            $table->text('horas_extras_json')->nullable();
            $table->decimal('monto_extras', 12, 2)->default(0.00);
            $table->decimal('salud_deduccion', 12, 2)->default(0.00);
            $table->decimal('pension_deduccion', 12, 2)->default(0.00);
            $table->decimal('otras_deducciones', 12, 2)->default(0.00);
            $table->decimal('neto_pagado', 12, 2);
            $table->integer('egreso_id')->unsigned()->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
            $table->foreign('egreso_id')->references('id')->on('egresos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liquidacion_quincena');
    }
}
