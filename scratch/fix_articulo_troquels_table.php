<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('articulo_troquels', function (Blueprint $table) {
    if (!Schema::hasColumn('articulo_troquels', 'tamano')) {
        $table->string('tamano')->nullable()->after('largo_impresion');
    }
    if (!Schema::hasColumn('articulo_troquels', 'mostrar')) {
        $table->boolean('mostrar')->default(true)->after('tamano');
    }
});

echo "Table articulo_troquels updated successfully\n";
