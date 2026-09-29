<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComprobanteIdToMovimientosMateriaPrimaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimiento_materia_primas', function (Blueprint $table) {
            $table->integer('comprobante_id')->unsigned()->nullable()->after('costo_total');
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
        Schema::table('movimiento_materia_primas', function (Blueprint $table) {
            $table->dropForeign(['comprobante_id']);
            $table->dropColumn('comprobante_id');
        });
    }
}
