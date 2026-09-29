<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeriodosContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periodos_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mes');
            $table->integer('anio');
            $table->string('estado', 20)->default('Abierto'); // 'Abierto' or 'Cerrado'
            $table->timestamps();

            // Unique index to prevent duplicate period settings
            $table->unique(['mes', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('periodos_contables');
    }
}
