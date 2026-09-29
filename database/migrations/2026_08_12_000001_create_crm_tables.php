<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCrmTables extends Migration
{
    public function up()
    {
        // 1. Prospectos / Leads
        if (!Schema::hasTable('crm_prospectos')) {
            Schema::create('crm_prospectos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nombre');
                $table->string('empresa')->nullable();
                $table->string('cargo')->nullable();
                $table->string('email')->nullable();
                $table->string('telefono')->nullable();
                $table->string('celular')->nullable();
                $table->string('direccion')->nullable();
                $table->string('ciudad')->nullable();
                $table->string('origen')->default('Web'); // Web, Referido, Redes, Llamada, Evento, Directo
                $table->string('estado')->default('Nuevo'); // Nuevo, Contactado, Calificado, Convertido, Descartado
                $table->unsignedInteger('user_id'); // Vendedor / Usuario asignado
                $table->unsignedInteger('cliente_id')->nullable(); // Id de persona/cliente cuando se convierte
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 2. Etapas del Embudo de Ventas (Pipeline Stages)
        if (!Schema::hasTable('crm_etapas')) {
            Schema::create('crm_etapas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nombre');
                $table->integer('orden')->default(0);
                $table->integer('probabilidad')->default(0); // Porcentaje 0-100
                $table->string('color')->default('#3b82f6');
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            // Insertar etapas por defecto
            DB::table('crm_etapas')->insert([
                ['nombre' => 'Prospecto / Calificación', 'orden' => 1, 'probabilidad' => 10, 'color' => '#64748b', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Contactado / Diagnóstico', 'orden' => 2, 'probabilidad' => 25, 'color' => '#0284c7', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Propuesta / Cotización Enviada', 'orden' => 3, 'probabilidad' => 50, 'color' => '#eab308', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Negociación', 'orden' => 4, 'probabilidad' => 75, 'color' => '#f97316', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Ganado (Cierre)', 'orden' => 5, 'probabilidad' => 100, 'color' => '#22c55e', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Perdido', 'orden' => 6, 'probabilidad' => 0, 'color' => '#ef4444', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Oportunidades de Negocio
        if (!Schema::hasTable('crm_oportunidades')) {
            Schema::create('crm_oportunidades', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo')->unique();
                $table->string('nombre');
                $table->unsignedBigInteger('prospecto_id')->nullable();
                $table->unsignedInteger('cliente_id')->nullable();
                $table->unsignedInteger('user_id');
                $table->unsignedBigInteger('etapa_id');
                $table->decimal('monto_estimado', 15, 2)->default(0);
                $table->integer('probabilidad')->default(50);
                $table->date('fecha_cierre_estimada')->nullable();
                $table->string('estado')->default('Abierta'); // Abierta, Ganada, Perdida
                $table->text('motivo_perdida')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('prospecto_id')->references('id')->on('crm_prospectos')->onDelete('set null');
                $table->foreign('etapa_id')->references('id')->on('crm_etapas')->onDelete('cascade');
            });
        }

        // 4. Cotizaciones
        if (!Schema::hasTable('crm_cotizaciones')) {
            Schema::create('crm_cotizaciones', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('numero_cotizacion')->unique();
                $table->unsignedBigInteger('prospecto_id')->nullable();
                $table->unsignedInteger('cliente_id')->nullable();
                $table->unsignedBigInteger('oportunidad_id')->nullable();
                $table->unsignedInteger('user_id');
                $table->date('fecha_emision');
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('descuento', 15, 2)->default(0);
                $table->decimal('iva', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('estado')->default('Borrador'); // Borrador, Enviada, Aprobada, Rechazada, Convertida
                $table->string('condiciones_pago')->nullable();
                $table->text('observaciones')->nullable();
                $table->unsignedInteger('pedido_id')->nullable(); // Id de pedido/comprobante si fue convertida
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('prospecto_id')->references('id')->on('crm_prospectos')->onDelete('set null');
                $table->foreign('oportunidad_id')->references('id')->on('crm_oportunidades')->onDelete('set null');
            });
        }

        // 5. Detalles de Cotización
        if (!Schema::hasTable('crm_cotizacion_detalles')) {
            Schema::create('crm_cotizacion_detalles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('cotizacion_id');
                $table->unsignedInteger('articulo_id')->nullable();
                $table->string('concepto');
                $table->text('descripcion')->nullable();
                $table->decimal('cantidad', 12, 2)->default(1);
                $table->decimal('precio_unitario', 15, 2)->default(0);
                $table->decimal('descuento_porcentaje', 5, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('iva', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('cotizacion_id')->references('id')->on('crm_cotizaciones')->onDelete('cascade');
            });
        }

        // 6. Actividades y Seguimiento
        if (!Schema::hasTable('crm_actividades')) {
            Schema::create('crm_actividades', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('prospecto_id')->nullable();
                $table->unsignedBigInteger('oportunidad_id')->nullable();
                $table->unsignedBigInteger('cotizacion_id')->nullable();
                $table->unsignedInteger('user_id');
                $table->string('tipo'); // Llamada, Reunion, Correo, Tarea, Nota
                $table->string('asunto');
                $table->text('descripcion')->nullable();
                $table->dateTime('fecha_vencimiento')->nullable();
                $table->boolean('completada')->default(false);
                $table->dateTime('fecha_completada')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('prospecto_id')->references('id')->on('crm_prospectos')->onDelete('cascade');
                $table->foreign('oportunidad_id')->references('id')->on('crm_oportunidades')->onDelete('cascade');
                $table->foreign('cotizacion_id')->references('id')->on('crm_cotizaciones')->onDelete('cascade');
            });
        }

        // 7. Metas de Ventas por Vendedor
        if (!Schema::hasTable('crm_metas_ventas')) {
            Schema::create('crm_metas_ventas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id');
                $table->integer('mes');
                $table->integer('anio');
                $table->decimal('monto_meta', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id', 'mes', 'anio']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('crm_metas_ventas');
        Schema::dropIfExists('crm_actividades');
        Schema::dropIfExists('crm_cotizacion_detalles');
        Schema::dropIfExists('crm_cotizaciones');
        Schema::dropIfExists('crm_oportunidades');
        Schema::dropIfExists('crm_etapas');
        Schema::dropIfExists('crm_prospectos');
    }
}
