<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineaComprobantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linea_comprobantes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_comprobante')->unsigned();
            $table->foreign('id_comprobante')->references('id')->on('comprobantes')->onDelete('cascade');
            $table->integer('id_articulo')->unsigned();
            $table->foreign('id_articulo')->references('id')->on('articulos');
            $table->date('fecha');
            $table->date('fecha_entrega');
            $table->integer('cantidad');
            $table->decimal('valor_unitario',20,2);
            $table->decimal('descuento',20,2);
            $table->decimal('valor_total',20,2);
            $table->string('estado', 20);
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
        Schema::dropIfExists('linea_comprobantes');
    }
}
