<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReciboPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recibo_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comprobante_id');
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('user_id');
            $table->date('fecha');
            $table->decimal('monto', 20, 2);
            $table->string('forma_pago'); // Efectivo, Banco, Transferencia
            $table->string('num_recibo')->unique();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('comprobante_id')->references('id')->on('comprobantes')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recibo_pagos');
    }
}
