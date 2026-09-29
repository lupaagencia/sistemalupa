<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCupoToProveedoresAndVencimientoToCuentas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->decimal('cupo_credito', 11, 2)->default(0.00)->after('email');
        });

        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->after('fecha');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn('cupo_credito');
        });

        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->dropColumn('fecha_vencimiento');
        });
    }
}
