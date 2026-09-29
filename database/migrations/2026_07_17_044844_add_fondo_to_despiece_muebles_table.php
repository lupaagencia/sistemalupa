<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFondoToDespieceMueblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->boolean('tiene_fondo')->default(true)->after('notas');
            $table->string('material_fondo')->default('Durolac Blanco / MDF 3mm')->after('tiene_fondo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('despiece_muebles', function (Blueprint $table) {
            $table->dropColumn(['tiene_fondo', 'material_fondo']);
        });
    }
}
