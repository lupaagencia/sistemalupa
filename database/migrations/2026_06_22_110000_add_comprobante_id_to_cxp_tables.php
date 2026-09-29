<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComprobanteIdToCxpTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->integer('comprobante_id')->unsigned()->nullable()->after('fecha_vencimiento');
            $table->foreign('comprobante_id')->references('id')->on('comprobantes_contables')->onDelete('set null');
        });

        Schema::table('abonos_cuentas_por_pagar', function (Blueprint $table) {
            $table->integer('comprobante_id')->unsigned()->nullable()->after('metodo_pago');
            $table->foreign('comprobante_id')->references('id')->on('comprobantes_contables')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abonos_cuentas_por_pagar', function (Blueprint $table) {
            $table->dropForeign(['comprobante_id']);
            $table->dropColumn('comprobante_id');
        });

        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->dropForeign(['comprobante_id']);
            $table->dropColumn('comprobante_id');
        });
    }
}
