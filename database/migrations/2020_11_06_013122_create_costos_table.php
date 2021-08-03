<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('costos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idorden')->unsigned();
            $table->foreign('idorden')->references('id')->on('ordentrabajos');
            $table->integer('idcostois')->unsigned();
            $table->foreign('idcostois')->references('id')->on('costois');
            $table->string('titulo',20);
            $table->string('descripcion',400);
            $table->string('cantidad',20);
            $table->decimal('valor',10,2);
            $table->decimal('total',20,2);
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
        Schema::dropIfExists('costos');
    }
}
