<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('tipo_producto', function (Blueprint $table) {
    if (!Schema::hasColumn('tipo_producto', 'formula_ancho')) {
        $table->string('formula_ancho')->nullable()->after('descripcion');
    }
    if (!Schema::hasColumn('tipo_producto', 'formula_largo')) {
        $table->string('formula_largo')->nullable()->after('formula_ancho');
    }
    if (!Schema::hasColumn('tipo_producto', 'piezas_por_pliego')) {
        $table->integer('piezas_por_pliego')->default(0)->after('formula_largo');
    }
    if (!Schema::hasColumn('tipo_producto', 'gastos_fijos')) {
        $table->decimal('gastos_fijos', 10, 2)->default(0)->after('piezas_por_pliego');
    }
    if (!Schema::hasColumn('tipo_producto', 'rentabilidad')) {
        $table->decimal('rentabilidad', 10, 2)->default(0)->after('gastos_fijos');
    }
    if (!Schema::hasColumn('tipo_producto', 'permite_troquelado')) {
        $table->boolean('permite_troquelado')->default(false)->after('rentabilidad');
    }
});

echo "Table tipo_producto updated successfully\n";
