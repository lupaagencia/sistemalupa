<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSistemaAperturaToDespieceMuebles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->string('sistema_apertura')->nullable()->after('material');
            $table->string('tipo_tirador')->nullable()->after('sistema_apertura');
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
            $table->dropColumn(['sistema_apertura', 'tipo_tirador']);
        });
    }
}
