<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumeroFacturaAndSoporteToCuentas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->string('numero_factura', 50)->nullable()->after('id');
            $table->string('soporte', 255)->nullable()->after('estado');
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
            $table->dropColumn(['numero_factura', 'soporte']);
        });
    }
}
