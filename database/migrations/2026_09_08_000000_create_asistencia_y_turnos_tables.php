<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAsistenciaYTurnosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Crear tabla turnos
        if (!Schema::hasTable('turnos')) {
            Schema::create('turnos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nombre');
                $table->time('hora_entrada')->default('08:00:00');
                $table->integer('tolerancia_minutos')->default(10);
                $table->time('hora_salida_receso')->nullable();
                $table->time('hora_entrada_receso')->nullable();
                $table->time('hora_salida_almuerzo')->nullable();
                $table->time('hora_entrada_almuerzo')->nullable();
                $table->time('hora_salida')->default('17:00:00');
                $table->string('estado')->default('Activo');
                $table->timestamps();
            });
        }

        // 2. Modificar tabla empleados para incluir codigo_qr y turno_id
        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'codigo_qr')) {
                $table->string('codigo_qr')->nullable()->unique()->after('cargo');
            }
            if (!Schema::hasColumn('empleados', 'turno_id')) {
                $table->unsignedBigInteger('turno_id')->nullable()->after('codigo_qr');
            }
        });

        // 3. Crear tabla asistencias
        if (!Schema::hasTable('asistencias')) {
            Schema::create('asistencias', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('empleado_id'); // Match int(11) in empleados.id
                $table->unsignedBigInteger('turno_id')->nullable();
                $table->date('fecha');
                $table->string('tipo_evento'); // entrada_manana, salida_receso, entrada_receso, salida_almuerzo, entrada_almuerzo, salida_empresa
                $table->dateTime('hora_marcada');
                $table->string('estado_llegada')->default('a_tiempo'); // a_tiempo, llegada_tarde, salida_anticipada, normal
                $table->integer('minutos_tardanza')->default(0);
                $table->integer('minutos_extras')->default(0);
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asistencias');

        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'codigo_qr')) {
                $table->dropColumn('codigo_qr');
            }
            if (Schema::hasColumn('empleados', 'turno_id')) {
                $table->dropColumn('turno_id');
            }
        });

        Schema::dropIfExists('turnos');
    }
}
