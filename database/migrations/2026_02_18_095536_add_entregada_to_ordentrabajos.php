<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEntregadaToOrdentrabajos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ordentrabajos', function (Blueprint $table) {
            $table->integer('cantidad_entregada')->default(0)->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ordentrabajos', function (Blueprint $table) {
            $table->dropColumn('cantidad_entregada');
        });
    }
}
