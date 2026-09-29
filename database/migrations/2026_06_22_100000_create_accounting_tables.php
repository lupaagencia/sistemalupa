<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAccountingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create cuentas table
        Schema::create('cuentas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('tipo', ['Activo', 'Pasivo', 'Patrimonio', 'Ingreso', 'Gasto', 'Costo']);
            $table->enum('naturaleza', ['Debito', 'Credito']);
            $table->boolean('es_detalle')->default(true);
            $table->integer('padre_id')->unsigned()->nullable();
            $table->timestamps();
            
            $table->foreign('padre_id')->references('id')->on('cuentas')->onDelete('set null');
        });

        // 2. Create comprobantes_contables table
        Schema::create('comprobantes_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('tipo', ['Ingreso', 'Egreso', 'Diario', 'Traspaso']);
            $table->integer('numero')->unsigned();
            $table->date('fecha');
            $table->text('descripcion');
            $table->integer('user_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // 3. Create asientos_detalles table
        Schema::create('asientos_detalles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comprobante_id')->unsigned();
            $table->integer('cuenta_id')->unsigned();
            $table->integer('tercero_id')->unsigned()->nullable();
            $table->decimal('debe', 15, 2)->default(0.00);
            $table->decimal('haber', 15, 2)->default(0.00);
            $table->string('referencia', 100)->nullable();
            $table->timestamps();

            $table->foreign('comprobante_id')->references('id')->on('comprobantes_contables')->onDelete('cascade');
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('restrict');
            $table->foreign('tercero_id')->references('id')->on('personas')->onDelete('set null');
        });

        // 4. Seed basic PUC (Plan Único de Cuentas)
        $this->seedBasicPUC();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asientos_detalles');
        Schema::dropIfExists('comprobantes_contables');
        Schema::dropIfExists('cuentas');
    }

    private function seedBasicPUC()
    {
        // Define hierarchical PUC accounts
        // We will insert them level by level so we can resolve parent ids.
        $levels = [
            // Level 1: Classes
            1 => [
                ['codigo' => '1', 'nombre' => 'Activos', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => null],
                ['codigo' => '2', 'nombre' => 'Pasivos', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => null],
                ['codigo' => '3', 'nombre' => 'Patrimonio', 'tipo' => 'Patrimonio', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => null],
                ['codigo' => '4', 'nombre' => 'Ingresos', 'tipo' => 'Ingreso', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => null],
                ['codigo' => '5', 'nombre' => 'Gastos', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => null],
                ['codigo' => '6', 'nombre' => 'Costos de Ventas', 'tipo' => 'Costo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => null],
            ],
            // Level 2: Groups
            2 => [
                // Activos
                ['codigo' => '11', 'nombre' => 'Disponible', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '1'],
                ['codigo' => '13', 'nombre' => 'Deudores', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '1'],
                // Pasivos
                ['codigo' => '22', 'nombre' => 'Proveedores', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '2'],
                ['codigo' => '23', 'nombre' => 'Cuentas por Pagar', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '2'],
                // Patrimonio
                ['codigo' => '31', 'nombre' => 'Capital Social', 'tipo' => 'Patrimonio', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '3'],
                // Ingresos
                ['codigo' => '41', 'nombre' => 'Operacionales', 'tipo' => 'Ingreso', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '4'],
                // Gastos
                ['codigo' => '51', 'nombre' => 'Operacionales de Administración', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '5'],
                // Costos
                ['codigo' => '61', 'nombre' => 'Operacionales', 'tipo' => 'Costo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '6'],
            ],
            // Level 3: Accounts
            3 => [
                // Disponible
                ['codigo' => '1105', 'nombre' => 'Caja', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '11'],
                ['codigo' => '1110', 'nombre' => 'Bancos', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '11'],
                // Deudores
                ['codigo' => '1305', 'nombre' => 'Clientes', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '13'],
                // Proveedores
                ['codigo' => '2205', 'nombre' => 'Proveedores Nacionales', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '22'],
                // Cuentas por Pagar
                ['codigo' => '2335', 'nombre' => 'Costos y Gastos por Pagar', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '23'],
                // Capital Social
                ['codigo' => '3105', 'nombre' => 'Capital Suscrito y Pagado', 'tipo' => 'Patrimonio', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '31'],
                // Operacionales Ingresos
                ['codigo' => '4135', 'nombre' => 'Comercio al por mayor y al por menor', 'tipo' => 'Ingreso', 'naturaleza' => 'Credito', 'es_detalle' => false, 'padre_codigo' => '41'],
                // Gastos
                ['codigo' => '5105', 'nombre' => 'Gastos de Personal', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '51'],
                ['codigo' => '5135', 'nombre' => 'Servicios', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '51'],
                ['codigo' => '5195', 'nombre' => 'Diversos', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '51'],
                // Costos
                ['codigo' => '6135', 'nombre' => 'Comercio al por mayor y al por menor', 'tipo' => 'Costo', 'naturaleza' => 'Debito', 'es_detalle' => false, 'padre_codigo' => '61'],
            ],
            // Level 4: Subaccounts / Detail (Detail = true)
            4 => [
                // Caja
                ['codigo' => '110505', 'nombre' => 'Caja General', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '1105'],
                ['codigo' => '110510', 'nombre' => 'Caja Menor', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '1105'],
                // Bancos
                ['codigo' => '111005', 'nombre' => 'Bancos Nacionales (Moneda Local)', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '1110'],
                // Clientes
                ['codigo' => '130505', 'nombre' => 'Clientes Nacionales', 'tipo' => 'Activo', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '1305'],
                // Proveedores
                ['codigo' => '220505', 'nombre' => 'Proveedores Nacionales Detalle', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => true, 'padre_codigo' => '2205'],
                // Cuentas por Pagar
                ['codigo' => '233505', 'nombre' => 'Gastos Financieros por Pagar', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => true, 'padre_codigo' => '2335'],
                ['codigo' => '233550', 'nombre' => 'Servicios Públicos por Pagar', 'tipo' => 'Pasivo', 'naturaleza' => 'Credito', 'es_detalle' => true, 'padre_codigo' => '2335'],
                // Capital
                ['codigo' => '310505', 'nombre' => 'Capital Autorizado', 'tipo' => 'Patrimonio', 'naturaleza' => 'Credito', 'es_detalle' => true, 'padre_codigo' => '3105'],
                // Ventas
                ['codigo' => '413505', 'nombre' => 'Ventas de Productos / Empaques', 'tipo' => 'Ingreso', 'naturaleza' => 'Credito', 'es_detalle' => true, 'padre_codigo' => '4135'],
                // Gastos de Personal
                ['codigo' => '510506', 'nombre' => 'Sueldos', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '5105'],
                ['codigo' => '510527', 'nombre' => 'Auxilio de Transporte', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '5105'],
                // Gastos de Servicios
                ['codigo' => '513505', 'nombre' => 'Servicios de Acueducto, Energía y Teléfono', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '5135'],
                // Gastos Diversos
                ['codigo' => '519595', 'nombre' => 'Gastos Diversos / Insumos', 'tipo' => 'Gasto', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '5195'],
                // Costo de Ventas
                ['codigo' => '613505', 'nombre' => 'Costo de Materia Prima e Insumos Directos', 'tipo' => 'Costo', 'naturaleza' => 'Debito', 'es_detalle' => true, 'padre_codigo' => '6135'],
            ]
        ];

        foreach ($levels as $levelNum => $accounts) {
            foreach ($accounts as $acc) {
                $padreId = null;
                if (!empty($acc['padre_codigo'])) {
                    $parent = DB::table('cuentas')->where('codigo', $acc['padre_codigo'])->first();
                    if ($parent) {
                        $padreId = $parent->id;
                    }
                }

                DB::table('cuentas')->insert([
                    'codigo' => $acc['codigo'],
                    'nombre' => $acc['nombre'],
                    'tipo' => $acc['tipo'],
                    'naturaleza' => $acc['naturaleza'],
                    'es_detalle' => $acc['es_detalle'],
                    'padre_id' => $padreId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
