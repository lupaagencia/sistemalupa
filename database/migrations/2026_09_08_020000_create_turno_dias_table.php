<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTurnoDiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('turno_dias')) {
            Schema::create('turno_dias', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('turno_id');
                $table->unsignedTinyInteger('dia_num'); // 1 = Lunes, 7 = Domingo
                $table->string('dia_nombre'); // Lunes, Martes, Miércoles, etc.
                $table->boolean('laborable')->default(true);
                $table->time('hora_entrada')->default('08:00:00');
                $table->integer('tolerancia_minutos')->default(10);
                $table->time('hora_salida_receso')->nullable();
                $table->time('hora_entrada_receso')->nullable();
                $table->time('hora_salida_almuerzo')->nullable();
                $table->time('hora_entrada_almuerzo')->nullable();
                $table->time('hora_salida')->default('17:00:00');
                $table->timestamps();

                $table->foreign('turno_id')->references('id')->on('turnos')->onDelete('cascade');
                $table->unique(['turno_id', 'dia_num']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('turno_dias');
    }
}
