<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsInOrdentrabajos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ordentrabajos', function (Blueprint $table) {
            $table->renameColumn('ancho_material', 'tamano');
            $table->renameColumn('largo_material', 'medida_material');
            $table->renameColumn('unidad_medida',  'cabida');
            $table->renameColumn('idcostois',      'medida_final');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ordentrabajos', function (Blueprint $table) {
            $table->renameColumn('tamano',         'ancho_material');
            $table->renameColumn('medida_material','largo_material');
            $table->renameColumn('cabida',         'unidad_medida');
            $table->renameColumn('medida_final',   'idcostois');
        });
    }
}
