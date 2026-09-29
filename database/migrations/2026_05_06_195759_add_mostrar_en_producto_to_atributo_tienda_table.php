<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMostrarEnProductoToAtributoTiendaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atributos_tienda', function (Blueprint $table) {
            $table->boolean('mostrar_en_producto')->default(true)->after('es_buscable');
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
            $table->dropColumn('mostrar_en_producto');
        });
    }
}
