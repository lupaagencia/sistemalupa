<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateClasificacionesEgresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clasificaciones_egresos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        // Seed default classifications
        $defaults = [
            ['nombre' => 'Gasto', 'descripcion' => 'Gastos operativos y generales del negocio', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nómina', 'descripcion' => 'Pagos de salarios y honorarios de operarias y personal', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Insumos', 'descripcion' => 'Insumos indirectos de producción y oficina', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Materia Prima', 'descripcion' => 'Materia prima directa de producción', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Servicios', 'descripcion' => 'Servicios públicos, arriendo y mantenimiento', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otros', 'descripcion' => 'Otros egresos y abonos misceláneos', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('clasificaciones_egresos')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clasificaciones_egresos');
    }
}
