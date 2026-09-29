<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('articulos', function (Blueprint $table) {
    if (!Schema::hasColumn('articulos', 'ancho_final')) {
        $table->decimal('ancho_final', 10, 2)->nullable()->after('medida_final');
    }
    if (!Schema::hasColumn('articulos', 'largo_final')) {
        $table->decimal('largo_final', 10, 2)->nullable()->after('ancho_final');
    }
    if (!Schema::hasColumn('articulos', 'ancho')) {
        $table->decimal('ancho', 12, 2)->nullable()->after('etiquetas');
    }
    if (!Schema::hasColumn('articulos', 'largo')) {
        $table->decimal('largo', 12, 2)->nullable()->after('ancho');
    }
    if (!Schema::hasColumn('articulos', 'alto')) {
        $table->decimal('alto', 12, 2)->nullable()->after('largo');
    }
    if (!Schema::hasColumn('articulos', 'volumen')) {
        $table->decimal('volumen', 12, 2)->nullable()->after('alto');
    }
});

echo "Table articulos updated successfully\n";
