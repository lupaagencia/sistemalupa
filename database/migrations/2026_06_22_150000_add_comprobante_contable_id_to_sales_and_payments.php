<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComprobanteContableIdToSalesAndPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->integer('comprobante_contable_id')->unsigned()->nullable();
            $table->foreign('comprobante_contable_id')->references('id')->on('comprobantes_contables')->onDelete('set null');
        });

        Schema::table('recibo_pagos', function (Blueprint $table) {
            $table->integer('comprobante_contable_id')->unsigned()->nullable();
            $table->foreign('comprobante_contable_id')->references('id')->on('comprobantes_contables')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recibo_pagos', function (Blueprint $table) {
            $table->dropForeign(['comprobante_contable_id']);
            $table->dropColumn('comprobante_contable_id');
        });

        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropForeign(['comprobante_contable_id']);
            $table->dropColumn('comprobante_contable_id');
        });
    }
}
