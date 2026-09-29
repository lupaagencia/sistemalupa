<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPapelSpecColumnsToCostosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costos', function (Blueprint $table) {
            if (!Schema::hasColumn('costos', 'medida_material')) {
                $table->string('medida_material', 50)->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('costos', 'tamano')) {
                $table->string('tamano', 50)->nullable()->after('medida_material');
            }
            if (!Schema::hasColumn('costos', 'medida_final')) {
                $table->string('medida_final', 50)->nullable()->after('tamano');
            }
            if (!Schema::hasColumn('costos', 'cabida')) {
                $table->string('cabida', 50)->nullable()->after('medida_final');
            }
            if (!Schema::hasColumn('costos', 'sobrante')) {
                $table->string('sobrante', 50)->nullable()->after('cabida');
            }
            if (!Schema::hasColumn('costos', 'componente')) {
                $table->string('componente', 50)->nullable()->after('sobrante');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costos', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('costos', 'medida_material')) $columnsToDrop[] = 'medida_material';
            if (Schema::hasColumn('costos', 'tamano')) $columnsToDrop[] = 'tamano';
            if (Schema::hasColumn('costos', 'medida_final')) $columnsToDrop[] = 'medida_final';
            if (Schema::hasColumn('costos', 'cabida')) $columnsToDrop[] = 'cabida';
            if (Schema::hasColumn('costos', 'sobrante')) $columnsToDrop[] = 'sobrante';
            if (Schema::hasColumn('costos', 'componente')) $columnsToDrop[] = 'componente';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
}
