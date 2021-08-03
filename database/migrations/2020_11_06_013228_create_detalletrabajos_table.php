<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalletrabajosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalletrabajos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idorden')->unsigned();
            $table->foreign('idorden')->references('id')->on('costois');
            $table->string('titulo',20);
            $table->string('descripcion',400);
            $table->string('valor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalletrabajos');
    }
}
