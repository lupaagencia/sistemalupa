<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasElectronicasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas_electronicas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comprobante_id')->unsigned()->unique();
            $table->string('cufe', 255)->nullable();
            $table->string('uuid_proveedor', 255)->nullable();
            $table->enum('estado_dian', ['Pendiente', 'Aceptado', 'Rechazado'])->default('Pendiente');
            $table->string('xml_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->text('qr_code')->nullable();
            $table->text('dian_response')->nullable();
            $table->timestamp('fecha_transmision')->nullable();
            $table->timestamps();

            // Foreign key to comprobantes (sales invoices)
            $table->foreign('comprobante_id')
                  ->references('id')
                  ->on('comprobantes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facturas_electronicas');
    }
}
