<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostoArticulosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('costo_articulos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idarticulo')->unsigned();
            $table->foreign('idarticulo')->references('id')->on('ordentrabajos');
            $table->integer('idcostois')->unsigned();
            $table->foreign('idcostois')->references('id')->on('costois');
            $table->string('titulo',20);
            $table->string('descripcion',400);
            $table->integer('orden_produccion');
            $table->decimal('fraccion',10,2);
            $table->decimal('rentabilidad',10,2);
            $table->decimal('valor',10,2);
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
        Schema::dropIfExists('costo_articulos');
    }
}
