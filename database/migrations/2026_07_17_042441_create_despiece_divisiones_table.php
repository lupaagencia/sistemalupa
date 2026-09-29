<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDespieceDivisionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('despiece_divisiones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mueble_id')->unsigned();
            $table->integer('posicion')->default(1);
            $table->string('tipo')->default('Cajones'); // Cajones, Puertas, Abierto
            $table->integer('cantidad')->default(1); // Número de elementos (ej. 3 cajones, 2 puertas)
            $table->timestamps();

            $table->foreign('mueble_id')->references('id')->on('despiece_muebles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('despiece_divisiones');
    }
}
