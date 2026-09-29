<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTagsAndDimensionsToArticulos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->text('etiquetas')->nullable()->after('descripcion');
            $table->decimal('ancho', 12, 2)->nullable()->after('etiquetas');
            $table->decimal('largo', 12, 2)->nullable()->after('ancho');
            $table->decimal('alto', 12, 2)->nullable()->after('largo');
            $table->decimal('volumen', 12, 2)->nullable()->after('alto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn(['etiquetas', 'ancho', 'largo', 'alto', 'volumen']);
        });
    }
}
