<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCantidadOriginalToOrdentrabajos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ordentrabajos', function (Blueprint $blueprint) {
            $blueprint->integer('cantidad_original')->nullable()->after('cantidad');
        });

        // Inicializar cantidad_original con el valor actual de cantidad
        DB::table('ordentrabajos')->update([
            'cantidad_original' => DB::raw('cantidad')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ordentrabajos', function (Blueprint $blueprint) {
            $blueprint->dropColumn('cantidad_original');
        });
    }
}
