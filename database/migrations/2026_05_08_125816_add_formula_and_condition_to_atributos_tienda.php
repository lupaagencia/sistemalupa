<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormulaAndConditionToAtributosTienda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atributos_tienda', function (Blueprint $table) {
            $table->text('formula')->nullable()->after('valor_extra');
            $table->text('condicion')->nullable()->after('formula');
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
            $table->dropColumn(['formula', 'condicion']);
        });
    }
}
