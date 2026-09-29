<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBeneficiarioToCuentasAndPagos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            if (!Schema::hasColumn('cuentas_por_pagar', 'beneficiario')) {
                $table->string('beneficiario')->nullable()->after('proveedor_id');
            }
        });

        Schema::table('pagos_programados', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos_programados', 'beneficiario')) {
                $table->string('beneficiario')->nullable()->after('proveedor_id');
            }
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
            if (Schema::hasColumn('cuentas_por_pagar', 'beneficiario')) {
                $table->dropColumn('beneficiario');
            }
        });

        Schema::table('pagos_programados', function (Blueprint $table) {
            if (Schema::hasColumn('pagos_programados', 'beneficiario')) {
                $table->dropColumn('beneficiario');
            }
        });
    }
}
