<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprobatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comprobates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('num_comprobante')->unique();
            $table->integer('id_cliente')->unsigned();
            $table->foreign('id_cliente')->references('id')->on('clientes');
            $table->integer('id_user')->unsigned();
            $table->foreign('id_user')->references('id')->on('users');
            $table->date('fecha');
            $table->string('forma_pago', 50);
            $table->decimal('subtotal',20,2);
            $table->decimal('descuento',20,2);
            $table->decimal('total', 20, 2);
            $table->decimal('impuestos',20,2);
            $table->string('fuente', 50);
            $table->string('estado', 50);
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
        Schema::dropIfExists('comprobates');
    }
}
