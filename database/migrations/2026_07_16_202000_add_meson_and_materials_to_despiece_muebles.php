<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMesonAndMaterialsToDespieceMuebles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->string('material_interno')->nullable()->after('material');
            $table->string('material_externo')->nullable()->after('material_interno');
            $table->string('tipo_meson')->nullable()->after('material_externo');
            $table->string('costados_vistos')->nullable()->after('tipo_meson');
        });

        Schema::table('despiece_piezas', function (Blueprint $table) {
            $table->string('material')->nullable()->after('nombre_pieza');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('despiece_piezas', function (Blueprint $table) {
            $table->dropColumn('material');
        });

        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->dropColumn(['material_interno', 'material_externo', 'tipo_meson', 'costados_vistos']);
        });
    }
}
