<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCuentacobroIdToEntregas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->unsignedBigInteger('cuentacobro_id')->nullable()->after('comprobante_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropForeign(['cuentacobro_id']);
            $table->dropColumn('cuentacobro_id');
        });
    }
}
