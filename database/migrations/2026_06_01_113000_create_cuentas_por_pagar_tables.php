<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCuentasPorPagarTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuentas_por_pagar', function (Blueprint $table) {
            $table->increments('id');
            // Relation with assets table (finishing ladies) - signed int(10) to match activos.id
            $table->integer('activo_id')->nullable();
            $table->foreign('activo_id')->references('id')->on('activos')->onDelete('cascade');
            
            // Relation with orders - unsigned int(10) to match ordentrabajos.id
            $table->integer('ordentrabajo_id')->unsigned()->nullable();
            $table->foreign('ordentrabajo_id')->references('id')->on('ordentrabajos')->onDelete('cascade');
            
            // Relation with production cost record - unsigned int(10) to match costos.id
            $table->integer('costo_id')->unsigned()->nullable();
            $table->foreign('costo_id')->references('id')->on('costos')->onDelete('cascade');
            
            $table->string('descripcion', 255);
            $table->integer('cantidad');
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo', 10, 2);
            $table->string('estado', 20)->default('Pendiente'); // 'Pendiente', 'Abonado', 'Pagado'
            $table->date('fecha');
            
            $table->timestamps();
        });

        Schema::create('abonos_cuentas_por_pagar', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cuenta_por_pagar_id')->unsigned();
            $table->foreign('cuenta_por_pagar_id')->references('id')->on('cuentas_por_pagar')->onDelete('cascade');
            
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('observaciones', 255)->nullable();
            
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
        Schema::dropIfExists('abonos_cuentas_por_pagar');
        Schema::dropIfExists('cuentas_por_pagar');
    }
}
