<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComprobanteIdToIngresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->integer('comprobante_id')->unsigned()->nullable()->after('dias_credito');
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
        Schema::table('ingresos', function (Blueprint $table) {
            $table->dropForeign(['comprobante_id']);
            $table->dropColumn('comprobante_id');
        });
    }
}
