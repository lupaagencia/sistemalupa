<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnchoLargoToArticulos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->decimal('ancho_final', 10, 2)->nullable()->after('medida_final');
            $table->decimal('largo_final', 10, 2)->nullable()->after('ancho_final');
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
            $table->dropColumn(['ancho_final', 'largo_final']);
        });
    }
}
