<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('costois', function (Blueprint $table) {
    if (!Schema::hasColumn('costois', 'descripcion')) {
        $table->text('descripcion')->nullable()->after('nombre');
    }
});

echo "Table costois updated successfully\n";
