<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPadreIdToCategorias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('categorias', 'padre_id')) {
                $table->unsignedInteger('padre_id')->nullable()->after('id');
            }
            $table->foreign('padre_id')->references('id')->on('categorias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['padre_id']);
            $table->dropColumn('padre_id');
        });
    }
}
