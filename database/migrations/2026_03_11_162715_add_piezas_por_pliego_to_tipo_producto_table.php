<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPiezasPorPliegoToTipoProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->integer('piezas_por_pliego')->nullable()->after('cabida');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->dropColumn('piezas_por_pliego');
        });
    }
}
