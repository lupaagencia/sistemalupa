<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUnidadMedidaToCabidaInCostoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costois', function (Blueprint $table) {
            $table->renameColumn('unidad_medida', 'cabida');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costois', function (Blueprint $table) {
            $table->renameColumn('cabida', 'unidad_medida');
        });
    }
}
