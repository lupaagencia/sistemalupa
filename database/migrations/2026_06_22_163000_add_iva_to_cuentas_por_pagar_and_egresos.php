<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIvaToCuentasPorPagarAndEgresos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->decimal('iva', 15, 2)->default(0.00);
        });

        Schema::table('egresos', function (Blueprint $table) {
            $table->decimal('iva', 15, 2)->default(0.00);
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
            $table->dropColumn('iva');
        });

        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->dropColumn('iva');
        });
    }
}
