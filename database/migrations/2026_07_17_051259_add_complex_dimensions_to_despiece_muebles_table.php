<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComplexDimensionsToDespieceMueblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->integer('ancho_derecho')->nullable()->after('ancho');
            $table->integer('hueco_alto')->nullable()->after('ancho_derecho');
            $table->integer('hueco_ancho')->nullable()->after('hueco_alto');
            $table->integer('espacio_ciego')->nullable()->after('hueco_ancho');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->dropColumn(['ancho_derecho', 'hueco_alto', 'hueco_ancho', 'espacio_ciego']);
        });
    }
}
