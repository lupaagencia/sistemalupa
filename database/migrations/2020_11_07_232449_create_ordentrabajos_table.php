<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdentrabajosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordentrabajos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idcliente')->unsigned();
            $table->foreign('idcliente')->references('id')->on('clientes');
            $table->integer('idarticulo')->unsigned();
            $table->foreign('idarticulo')->references('id')->on('articulos');
            $table->decimal('cantidad',20,2);
            $table->decimal('ancho_material', 20,2);
            $table->decimal('largo_material', 20,2);
            $table->string('areas_impresas',20);
            $table->string('tipo_impresion', 20);
            $table->string('colores_impresion',20);
            $table->string('unidad_medida', 20);
            $table->string('detalles_diseno',400);
            $table->string('observaciones',400);
            $table->decimal('tamano', 4, 2);
            $table->decimal('abono', 4, 2);
            $table->decimal('saldo', 4, 2);
            $table->decimal('total', 4, 2);
            $table->string('estado', 20);
            $table->date('fecha_entrega');
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
        Schema::dropIfExists('ordentrabajos');
    }
}
