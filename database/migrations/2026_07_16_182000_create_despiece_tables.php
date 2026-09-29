<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDespieceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('despiece_proyectos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cliente')->nullable();
            $table->string('descripcion')->nullable();
            $table->date('fecha');
            $table->timestamps();
        });

        Schema::create('despiece_muebles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('proyecto_id')->unsigned();
            $table->string('nombre');
            $table->string('tipo_mueble'); // Bajo, Alto, Cajonera
            $table->integer('ancho'); // mm
            $table->integer('alto'); // mm
            $table->integer('profundidad'); // mm
            $table->integer('espesor_material')->default(15); // mm
            $table->string('material')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('proyecto_id')->references('id')->on('despiece_proyectos')->onDelete('cascade');
        });

        Schema::create('despiece_piezas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mueble_id')->unsigned();
            $table->string('nombre_pieza');
            $table->integer('cantidad');
            $table->decimal('largo', 8, 2); // mm
            $table->decimal('ancho', 8, 2); // mm
            $table->boolean('canto_l1')->default(false);
            $table->boolean('canto_l2')->default(false);
            $table->boolean('canto_a1')->default(false);
            $table->boolean('canto_a2')->default(false);
            $table->timestamps();

            $table->foreign('mueble_id')->references('id')->on('despiece_muebles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('despiece_piezas');
        Schema::dropIfExists('despiece_muebles');
        Schema::dropIfExists('despiece_proyectos');
    }
}
