<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEgresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('egresos', function (Blueprint $table) {
            $table->increments('id');
            $table->foreign('id_documento');
            $table->foreign('id_persona')->references('id')->on('personas');
            $table->string('cuenta_contable',100);
            $table->string('tipo_documento', 20);
            $table->string('tipo_egreso', 20);
            $table->string('forma_pago', 20);
            $table->decimal('subtotal',20,2);
            $table->decimal('total', 4, 2);
            $table->decimal('iva',4,2);
            $table->string('estado', 20);
            $table->date('fecha');

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
        Schema::dropIfExists('egresos');
    }
}
