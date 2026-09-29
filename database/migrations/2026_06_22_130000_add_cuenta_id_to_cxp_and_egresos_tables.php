<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCuentaIdToCxpAndEgresosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->integer('cuenta_id')->unsigned()->nullable()->after('comprobante_id');
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('set null');
        });

        Schema::table('egresos', function (Blueprint $table) {
            $table->integer('cuenta_id')->unsigned()->nullable()->after('comprobante_id');
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('egresos', function (Blueprint $table) {
            $table->dropForeign(['cuenta_id']);
            $table->dropColumn('cuenta_id');
        });

        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->dropForeign(['cuenta_id']);
            $table->dropColumn('cuenta_id');
        });
    }
}
