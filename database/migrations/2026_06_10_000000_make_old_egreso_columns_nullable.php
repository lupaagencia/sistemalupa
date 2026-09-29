<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeOldEgresoColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Safe check if egresos table exists
        if (!Schema::hasTable('egresos')) {
            return;
        }

        $columns = Schema::getColumnListing('egresos');

        // Modify columns to be nullable to prevent insertion failures on older schemas
        if (in_array('cuenta_contable', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `cuenta_contable` varchar(100) NULL");
        }
        if (in_array('tipo_documento', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `tipo_documento` varchar(20) NULL");
        }
        if (in_array('forma_pago', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `forma_pago` varchar(20) NULL");
        }
        if (in_array('subtotal', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `subtotal` decimal(20,2) NULL");
        }
        if (in_array('total', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `total` decimal(4,2) NULL");
        }
        if (in_array('iva', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `iva` decimal(4,2) NULL");
        }
        if (in_array('estado', $columns)) {
            DB::statement("ALTER TABLE `egresos` MODIFY COLUMN `estado` varchar(20) NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Non-destructive migration, no rollback necessary
    }
}
