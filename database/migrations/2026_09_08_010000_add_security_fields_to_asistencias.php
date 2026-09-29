<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecurityFieldsToAsistencias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Agregar campos de geolocalización a la tabla asistencias
        Schema::table('asistencias', function (Blueprint $table) {
            if (!Schema::hasColumn('asistencias', 'latitud')) {
                $table->decimal('latitud', 10, 8)->nullable()->after('hora_marcada');
            }
            if (!Schema::hasColumn('asistencias', 'longitud')) {
                $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
            }
            if (!Schema::hasColumn('asistencias', 'distancia_metros')) {
                $table->integer('distancia_metros')->nullable()->after('longitud');
            }
        });

        // 2. Crear tabla configuracion_asistencia para parámetros de GPS y PIN de Kiosco
        if (!Schema::hasTable('configuracion_asistencia')) {
            Schema::create('configuracion_asistencia', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->decimal('latitud_empresa', 10, 8)->nullable();
                $table->decimal('longitud_empresa', 11, 8)->nullable();
                $table->integer('radio_maximo_metros')->default(100);
                $table->boolean('requerir_gps')->default(false);
                $table->string('pin_kiosco')->default('1234');
                $table->timestamps();
            });

            // Insertar configuración por defecto inicial
            DB::table('configuracion_asistencia')->insert([
                'latitud_empresa' => null,
                'longitud_empresa' => null,
                'radio_maximo_metros' => 100,
                'requerir_gps' => false,
                'pin_kiosco' => '1234',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracion_asistencia');

        Schema::table('asistencias', function (Blueprint $table) {
            if (Schema::hasColumn('asistencias', 'latitud')) {
                $table->dropColumn('latitud');
            }
            if (Schema::hasColumn('asistencias', 'longitud')) {
                $table->dropColumn('longitud');
            }
            if (Schema::hasColumn('asistencias', 'distancia_metros')) {
                $table->dropColumn('distancia_metros');
            }
        });
    }
}
