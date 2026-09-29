<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPedidoRemisionIdToCruceCarteraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cruce_cartera', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_remision_id')->nullable()->after('comprobante_id');
            $table->foreign('pedido_remision_id')->references('id')->on('pedidos_remision')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cruce_cartera', function (Blueprint $table) {
            //
        });
    }
}
