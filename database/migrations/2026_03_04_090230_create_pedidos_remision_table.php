<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosRemisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos_remision', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('remision_id')->nullable();
            $table->unsignedBigInteger('cuentacobro_id')->nullable();

            $table->foreign('pedido_id')->references('id')->on('comprobantes')->onDelete('cascade');
            $table->foreign('remision_id')->references('id')->on('comprobantes')->onDelete('cascade');
            $table->foreign('cuentacobro_id')->references('id')->on('comprobantes')->onDelete('cascade');

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
        Schema::dropIfExists('pedidos_remision');
    }
}
