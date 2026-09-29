<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoProductoAtributoTiendaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_producto_atributo_tienda', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo_producto_id');
            $table->unsignedInteger('atributo_tienda_id');
            
            // $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->onDelete('cascade');
            // $table->foreign('atributo_tienda_id')->references('id')->on('atributos_tienda')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_producto_atributo_tienda');
    }
}
