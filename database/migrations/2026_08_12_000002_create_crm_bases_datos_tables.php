<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmBasesDatosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabla de Bases de Datos / Listados de Prospección
        if (!Schema::hasTable('crm_bases_datos')) {
            Schema::create('crm_bases_datos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id');
                $table->string('nombre');
                $table->string('sector')->nullable(); // Restaurantes, Laboratorios, Alimentos, etc.
                $table->string('origen')->nullable(); // Cámara de Comercio, Redes, Web, Directo
                $table->text('descripcion')->nullable();
                $table->string('estado')->default('Activa'); // Activa, En Gestión, Finalizada, Archivada
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 2. Tabla de Contactos de las Bases de Datos
        if (!Schema::hasTable('crm_contactos_base')) {
            Schema::create('crm_contactos_base', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('base_datos_id');
                $table->unsignedInteger('user_id');
                $table->string('empresa');
                $table->string('contacto_nombre')->nullable();
                $table->string('cargo')->nullable();
                $table->string('sector')->nullable();
                $table->string('telefono')->nullable();
                $table->string('email')->nullable();
                $table->string('ciudad')->nullable();
                $table->string('direccion')->nullable();
                $table->string('origen_detalle')->nullable(); // Ej: Matrícula CCB 2026

                // Estado de gestión outbound
                $table->string('estado_gestion')->default('Sin Contactar');
                // Sin Contactar, Contactado, Portafolio Enviado, Información Enviada, Interesado, Convertido a Prospecto, Descartado

                // Seguimiento de Portafolio
                $table->boolean('portafolio_enviado')->default(false);
                $table->dateTime('fecha_envio_portafolio')->nullable();
                $table->string('metodo_envio')->nullable(); // WhatsApp, Email, Presencial, etc.
                $table->text('resultado_gestion')->nullable();
                $table->dateTime('fecha_ultimo_contacto')->nullable();

                // Enlace con Prospecto si se convierte
                $table->unsignedBigInteger('prospecto_id')->nullable();

                $table->timestamps();

                $table->foreign('base_datos_id')->references('id')->on('crm_bases_datos')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('prospecto_id')->references('id')->on('crm_prospectos')->onDelete('set null');
            });
        }

        // 3. Tabla de Log / Bitácora de Gestión por Contacto
        if (!Schema::hasTable('crm_gestion_contactos_log')) {
            Schema::create('crm_gestion_contactos_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('contacto_id');
                $table->unsignedInteger('user_id');
                $table->string('tipo_accion'); // Envío de Portafolio, Llamada, Correo, Cambio de Estado, Nota
                $table->text('detalle')->nullable();
                $table->timestamps();

                $table->foreign('contacto_id')->references('id')->on('crm_contactos_base')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('crm_gestion_contactos_log');
        Schema::dropIfExists('crm_contactos_base');
        Schema::dropIfExists('crm_bases_datos');
    }
}
