<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeValorColumnToTextInAjustesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ajustes')) {
            DB::statement("ALTER TABLE `ajustes` MODIFY COLUMN `valor` TEXT NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('ajustes')) {
            DB::statement("ALTER TABLE `ajustes` MODIFY COLUMN `valor` VARCHAR(200) NOT NULL");
        }
    }
}
