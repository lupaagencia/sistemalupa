<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtributosTiendaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atributos_tienda', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo'); // Tinta, Papel, Acabado, Terminado, etc.
            $table->string('nombre'); // 4 Tintas, Propalcote 300g, etc.
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('atributos_tienda');
    }
}
