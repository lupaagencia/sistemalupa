<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticuloTroquelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articulo_troquels', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('articulo_id');
            $table->integer('cabida');
            $table->string('imagen')->nullable();
            $table->decimal('ancho_impresion', 10, 2)->nullable();
            $table->decimal('largo_impresion', 10, 2)->nullable();
            $table->string('tamano')->nullable();
            $table->boolean('mostrar')->default(true);
            $table->timestamps();

            $table->foreign('articulo_id')->references('id')->on('articulos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articulo_troquels');
    }
}
