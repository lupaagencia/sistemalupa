<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoporteToAbonosCuentasPorPagarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('abonos_cuentas_por_pagar', function (Blueprint $table) {
            $table->string('soporte', 255)->nullable()->after('observaciones');
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
            $table->dropColumn('soporte');
        });
    }
}
