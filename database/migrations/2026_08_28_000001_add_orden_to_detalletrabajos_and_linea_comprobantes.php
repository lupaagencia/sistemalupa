<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrdenToDetalletrabajosAndLineaComprobantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('detalletrabajos') && !Schema::hasColumn('detalletrabajos', 'orden')) {
            Schema::table('detalletrabajos', function (Blueprint $table) {
                $table->integer('orden')->nullable()->default(1)->after('costos_id');
            });
        }

        if (Schema::hasTable('linea_comprobantes') && !Schema::hasColumn('linea_comprobantes', 'orden')) {
            Schema::table('linea_comprobantes', function (Blueprint $table) {
                $table->integer('orden')->nullable()->default(1)->after('ordentrabajo_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('detalletrabajos') && Schema::hasColumn('detalletrabajos', 'orden')) {
            Schema::table('detalletrabajos', function (Blueprint $table) {
                $table->dropColumn('orden');
            });
        }

        if (Schema::hasTable('linea_comprobantes') && Schema::hasColumn('linea_comprobantes', 'orden')) {
            Schema::table('linea_comprobantes', function (Blueprint $table) {
                $table->dropColumn('orden');
            });
        }
    }
}
