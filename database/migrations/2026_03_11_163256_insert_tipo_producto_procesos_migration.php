<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertTipoProductoProcesosMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('migrations')->insert([
            'migration' => '2026_03_11_162746_create_tipo_producto_procesos_table',
            'batch' => DB::table('migrations')->max('batch')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('migrations')->where('migration', '2026_03_11_162746_create_tipo_producto_procesos_table')->delete();
    }
}
