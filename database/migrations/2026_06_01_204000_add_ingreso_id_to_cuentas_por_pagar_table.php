<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIngresoIdToCuentasPorPagarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->integer('ingreso_id')->unsigned()->nullable()->after('costo_id');
            $table->foreign('ingreso_id')->references('id')->on('ingresos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->dropForeign(['ingreso_id']);
            $table->dropColumn('ingreso_id');
        });
    }
}
