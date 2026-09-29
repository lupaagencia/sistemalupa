<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiquidacionPrimasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liquidacion_primas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empleado_id'); // Signed int(11) to match empleados.id
            $table->integer('anio');
            $table->integer('periodo'); // 1 = Primer semestre, 2 = Segundo semestre
            $table->date('fecha_pago');
            $table->integer('dias_trabajados');
            $table->decimal('salario_base', 12, 2);
            $table->decimal('promedio_extras', 12, 2)->default(0.00);
            $table->decimal('valor_prima', 12, 2);
            $table->integer('egreso_id')->unsigned()->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
            $table->foreign('egreso_id')->references('id')->on('egresos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liquidacion_primas');
    }
}
