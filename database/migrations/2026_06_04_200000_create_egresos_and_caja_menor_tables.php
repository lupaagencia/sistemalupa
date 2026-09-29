<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEgresosAndCajaMenorTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Drop existing egresos table
        Schema::dropIfExists('egresos');

        // 2. Create egresos table
        Schema::create('egresos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo_egreso', 50); // 'Gasto', 'Nomina', 'Insumos', 'Materia Prima', 'Servicios', 'Otros'
            $table->text('concepto');
            $table->decimal('valor', 15, 2);
            $table->date('fecha');
            $table->string('metodo_pago', 50); // 'Caja Menor', 'Banco', 'Caja Mayor', 'Otros'
            $table->string('beneficiario', 255)->nullable();
            $table->string('soporte', 255)->nullable();
            $table->integer('cuenta_por_pagar_id')->unsigned()->nullable();
            $table->foreign('cuenta_por_pagar_id')->references('id')->on('cuentas_por_pagar')->onDelete('cascade');
            $table->integer('abono_id')->unsigned()->nullable();
            $table->foreign('abono_id')->references('id')->on('abonos_cuentas_por_pagar')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. Create caja_menor_movimientos table
        Schema::create('caja_menor_movimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo', 20); // 'Ingreso' o 'Egreso'
            $table->decimal('monto', 15, 2);
            $table->decimal('saldo_resultante', 15, 2);
            $table->date('fecha');
            $table->text('descripcion');
            $table->string('soporte', 255)->nullable();
            $table->integer('egreso_id')->unsigned()->nullable();
            $table->foreign('egreso_id')->references('id')->on('egresos')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // 4. Add metodo_pago to abonos_cuentas_por_pagar
        Schema::table('abonos_cuentas_por_pagar', function (Blueprint $table) {
            $table->string('metodo_pago', 50)->nullable()->after('soporte');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abonos_cuentas_por_pagar', function (Blueprint $table) {
            if (Schema::hasColumn('abonos_cuentas_por_pagar', 'metodo_pago')) {
                $table->dropColumn('metodo_pago');
            }
        });

        Schema::dropIfExists('caja_menor_movimientos');
        Schema::dropIfExists('egresos');

        Schema::create('egresos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_documento')->nullable();
            $table->integer('id_persona')->unsigned()->nullable();
            $table->string('cuenta_contable',100)->nullable();
            $table->string('tipo_documento', 20)->nullable();
            $table->string('tipo_egreso', 20)->nullable();
            $table->string('forma_pago', 20)->nullable();
            $table->decimal('subtotal',20,2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->decimal('iva',4,2)->nullable();
            $table->string('estado', 20)->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();
        });
    }
}
