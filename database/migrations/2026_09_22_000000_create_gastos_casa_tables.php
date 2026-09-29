<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateGastosCasaTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabla gh_personas
        Schema::create('gh_personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->decimal('porcentaje_participacion', 5, 2)->default(33.33);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed initial personas (Julián, Óscar, Diego)
        DB::table('gh_personas')->insert([
            ['nombre' => 'Julián', 'telefono' => null, 'email' => null, 'porcentaje_participacion' => 33.34, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Óscar', 'telefono' => null, 'email' => null, 'porcentaje_participacion' => 33.33, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Diego', 'telefono' => null, 'email' => null, 'porcentaje_participacion' => 33.33, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Tabla gh_categorias
        Schema::create('gh_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['gasto', 'ingreso', 'reserva'])->default('gasto');
            $table->timestamps();
        });

        // Seed initial categories
        DB::table('gh_categorias')->insert([
            ['nombre' => 'Arreglos y Remodelación', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Servicios Públicos', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Impuesto Predial', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Mantenimiento General', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Materiales de Construcción', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Mano de Obra', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otros Gastos', 'tipo' => 'gasto', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Tabla gh_gastos
        Schema::create('gh_gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->string('origen_pago', 50)->default('persona');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->date('fecha');
            $table->text('descripcion');
            $table->decimal('valor', 12, 2);
            $table->string('soporte_path')->nullable();
            $table->string('soporte_nombre_orig')->nullable();
            $table->string('comprobante_path')->nullable();
            $table->string('comprobante_nombre_orig')->nullable();
            $table->enum('estado', ['pendiente', 'reembolsado_parcial', 'reembolsado_total', 'anulado'])->default('pendiente');
            $table->decimal('saldo_pendiente', 12, 2);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('persona_id')->references('id')->on('gh_personas')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('gh_categorias')->onDelete('set null');
        });

        // 4. Tabla gh_ingresos
        Schema::create('gh_ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_ingreso', 50)->default('arriendo');
            $table->date('fecha');
            $table->string('periodo_mes')->nullable(); // ej. "2026-10" o "Octubre 2026"
            $table->string('inquilino_nombre')->nullable();
            $table->string('descripcion');
            $table->decimal('valor_total', 12, 2);
            $table->string('comprobante_path')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        // 5. Tabla gh_distribuciones
        Schema::create('gh_distribuciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ingreso_id');
            $table->enum('tipo_destino', ['reembolso_persona', 'reserva_predial', 'reserva_arreglos', 'reserva_otra']);
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->unsignedBigInteger('gasto_id')->nullable();
            $table->decimal('valor', 12, 2);
            $table->string('observaciones')->nullable();
            $table->date('fecha');
            $table->timestamps();

            $table->foreign('ingreso_id')->references('id')->on('gh_ingresos')->onDelete('cascade');
            $table->foreign('persona_id')->references('id')->on('gh_personas')->onDelete('cascade');
            $table->foreign('gasto_id')->references('id')->on('gh_gastos')->onDelete('set null');
        });

        // 6. Tabla gh_reservas
        Schema::create('gh_reservas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_reserva', ['predial', 'arreglos', 'otro'])->default('predial');
            $table->enum('tipo_movimiento', ['ingreso', 'egreso'])->default('ingreso');
            $table->unsignedBigInteger('distribucion_id')->nullable();
            $table->date('fecha');
            $table->string('concepto');
            $table->decimal('valor', 12, 2);
            $table->string('soporte_path')->nullable();
            $table->timestamps();

            $table->foreign('distribucion_id')->references('id')->on('gh_distribuciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gh_reservas');
        Schema::dropIfExists('gh_distribuciones');
        Schema::dropIfExists('gh_ingresos');
        Schema::dropIfExists('gh_gastos');
        Schema::dropIfExists('gh_categorias');
        Schema::dropIfExists('gh_personas');
    }
}
