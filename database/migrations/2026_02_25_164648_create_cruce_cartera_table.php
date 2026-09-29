<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCruceCarteraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cruce_cartera', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recibo_pago_id');
            $table->unsignedBigInteger('comprobante_id');
            $table->decimal('monto', 20, 2);
            $table->date('fecha_cruce');
            $table->timestamps();

            $table->foreign('recibo_pago_id')->references('id')->on('recibo_pagos')->onDelete('cascade');
            $table->foreign('comprobante_id')->references('id')->on('comprobantes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cruce_cartera');
    }
}
