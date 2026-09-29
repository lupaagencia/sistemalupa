<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAltoMesonToDespieceMueblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->integer('alto_meson')->default(0)->after('tipo_meson');
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
            $table->dropColumn('alto_meson');
        });
    }
}
