<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyReciboPagosTableForFlexibleAllocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recibo_pagos', function (Blueprint $table) {
            // Make comprobante_id nullable if it's not already
            $table->unsignedBigInteger('comprobante_id')->nullable()->change();

            // Add a field to track how much of the receipt is still unassigned
            $table->decimal('saldo_recibo', 20, 2)->default(0)->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recibo_pagos', function (Blueprint $table) {
            // In a real rollback, we'd need to decide if we want to revert nullability
            // $table->unsignedBigInteger('comprobante_id')->nullable(false)->change();
            $table->dropColumn('saldo_recibo');
        });
    }
}
