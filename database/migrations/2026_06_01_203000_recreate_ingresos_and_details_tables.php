<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateIngresosAndDetailsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('detalle_ingresos');
        Schema::dropIfExists('ingresos');

        Schema::create('ingresos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idproveedor')->unsigned();
            $table->foreign('idproveedor')->references('id')->on('proveedores')->onDelete('cascade');
            $table->integer('idusuario')->unsigned();
            $table->foreign('idusuario')->references('id')->on('users')->onDelete('cascade');
            $table->string('tipo_comprobante', 20);
            $table->string('serie_comprobante', 7)->nullable();
            $table->string('num_comprobante', 10);
            $table->dateTime('fecha_hora');
            $table->decimal('impuesto', 4, 2)->default(0);
            $table->decimal('total', 11, 2);
            $table->string('estado', 20)->default('Registrado');
            
            // Credit and payment terms
            $table->string('forma_pago', 20)->default('Contado'); // 'Contado', 'Crédito'
            $table->integer('dias_credito')->nullable(); // Number of credit days
            
            $table->timestamps();
        });

        Schema::create('detalle_ingresos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idingreso')->unsigned();
            $table->foreign('idingreso')->references('id')->on('ingresos')->onDelete('cascade');
            
            // We reference articulos table
            $table->integer('idarticulo')->unsigned();
            $table->foreign('idarticulo')->references('id')->on('articulos')->onDelete('cascade');
            
            $table->integer('cantidad');
            $table->decimal('precio', 11, 2);
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
        Schema::dropIfExists('detalle_ingresos');
        Schema::dropIfExists('ingresos');
    }
}
