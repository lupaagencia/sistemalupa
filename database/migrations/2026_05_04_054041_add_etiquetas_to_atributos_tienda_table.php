<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEtiquetasToAtributosTiendaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atributos_tienda', function (Blueprint $table) {
            $table->text('etiquetas')->nullable()->after('nombre');
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
            $table->dropColumn('etiquetas');
        });
    }
}
