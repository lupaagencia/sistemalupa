<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEsBuscableToAtributosTienda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atributos_tienda', function (Blueprint $table) {
            $table->boolean('es_buscable')->default(true)->after('valor_extra');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atributos_tienda', function (Blueprint $table) {
            $table->dropColumn('es_buscable');
        });
    }
}
