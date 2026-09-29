<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorExtraToAtributosTienda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atributos_tienda', function (Blueprint $table) {
            $table->decimal('valor_extra', 11, 2)->default(0.00)->after('nombre');
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
            $table->dropColumn('valor_extra');
        });
    }
}
