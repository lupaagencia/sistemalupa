<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordentrabajo_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('cantidad');
            $table->integer('saldo_anterior');
            $table->integer('saldo_restante');
            $table->string('tipo_documento'); // Remision, Cuenta de Cobro, Ninguno
            $table->string('consecutivo')->nullable();
            $table->unsignedBigInteger('comprobante_id')->nullable();
            $table->timestamps();

            $table->foreign('ordentrabajo_id')->references('id')->on('ordentrabajos');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('comprobante_id')->references('id')->on('comprobantes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entregas');
    }
}
