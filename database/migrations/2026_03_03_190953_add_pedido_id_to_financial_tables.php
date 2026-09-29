<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPedidoIdToFinancialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id')->nullable()->after('num_comprobante');
            $table->decimal('monto_aplicado_anticipo', 20, 2)->default(0)->after('abono');
        });

        Schema::table('recibo_pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id')->nullable()->after('comprobante_id');
        });
    }

    public function down()
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropColumn(['pedido_id', 'monto_aplicado_anticipo']);
        });

        Schema::table('recibo_pagos', function (Blueprint $table) {
            $table->dropColumn('pedido_id');
        });
    }
}
