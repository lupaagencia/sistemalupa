<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtractosBancariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extractos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('descripcion');
            $table->decimal('monto', 20, 2); // positive = deposit, negative = withdrawal
            $table->string('referencia')->nullable();
            $table->enum('estado', ['Pendiente', 'Conciliado'])->default('Pendiente');
            $table->integer('comprobante_id')->unsigned()->nullable();
            $table->foreign('comprobante_id')->references('id')->on('comprobantes_contables')->onDelete('set null');
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
        Schema::dropIfExists('extractos_bancarios');
    }
}
