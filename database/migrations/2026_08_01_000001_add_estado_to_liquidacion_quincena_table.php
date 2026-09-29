<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddEstadoToLiquidacionQuincenaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('liquidacion_quincena', 'estado')) {
            Schema::table('liquidacion_quincena', function (Blueprint $table) {
                $table->enum('estado', ['Pendiente', 'Pagada'])->default('Pendiente')->after('neto_pagado');
            });
        }

        // Set existing records with egreso_id to 'Pagada'
        DB::table('liquidacion_quincena')
            ->whereNotNull('egreso_id')
            ->update(['estado' => 'Pagada']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('liquidacion_quincena', 'estado')) {
            Schema::table('liquidacion_quincena', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
        }
    }
}
