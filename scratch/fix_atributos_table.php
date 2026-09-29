<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('atributos_tienda', 'etiquetas')) {
    Schema::table('atributos_tienda', function (Blueprint $table) {
        $table->text('etiquetas')->nullable()->after('nombre');
    });
    echo "Added etiquetas\n";
}

if (!Schema::hasColumn('atributos_tienda', 'imagen')) {
    Schema::table('atributos_tienda', function (Blueprint $table) {
        $table->string('imagen')->nullable()->after('etiquetas');
    });
    echo "Added imagen\n";
}

if (!Schema::hasColumn('atributos_tienda', 'mostrar_en_producto')) {
    Schema::table('atributos_tienda', function (Blueprint $table) {
        $table->boolean('mostrar_en_producto')->default(true)->after('es_buscable');
    });
    echo "Added mostrar_en_producto\n";
}

echo "Table check complete\n";
