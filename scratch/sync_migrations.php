<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$migrationsPath = 'database/migrations';
$files = scandir($migrationsPath);
$batch = DB::table('migrations')->max('batch') ?? 0;
$batch++;

foreach ($files as $file) {
    if (strpos($file, '.php') !== false) {
        $migrationName = str_replace('.php', '', $file);
        
        // Check if already in migrations table
        $existsInTable = DB::table('migrations')->where('migration', $migrationName)->exists();
        
        if (!$existsInTable) {
            // Check if the table or column it creates/adds already exists
            // This is a bit heuristic but safer than just running it.
            // For now, let's just mark the ones we know are problematic as done.
            
            $markAsDone = false;
            
            if (strpos($migrationName, 'create_password_resets_table') !== false && Schema::hasTable('password_resets')) $markAsDone = true;
            if (strpos($migrationName, 'create_categorias_table') !== false && Schema::hasTable('categorias')) $markAsDone = true;
            if (strpos($migrationName, 'create_articulos_table') !== false && Schema::hasTable('articulos')) $markAsDone = true;
            if (strpos($migrationName, 'create_personas_table') !== false && Schema::hasTable('personas')) $markAsDone = true;
            if (strpos($migrationName, 'create_proveedores_table') !== false && Schema::hasTable('proveedores')) $markAsDone = true;
            if (strpos($migrationName, 'create_roles_table') !== false && Schema::hasTable('roles')) $markAsDone = true;
            if (strpos($migrationName, 'create_users_table') !== false && Schema::hasTable('users')) $markAsDone = true;
            if (strpos($migrationName, 'create_ingresos_table') !== false && Schema::hasTable('ingresos')) $markAsDone = true;
            if (strpos($migrationName, 'create_detalle_ingresos_table') !== false && Schema::hasTable('detalle_ingresos')) $markAsDone = true;
            if (strpos($migrationName, 'create_costoproduccions_table') !== false && Schema::hasTable('costoproduccions')) $markAsDone = true;
            if (strpos($migrationName, 'create_costois_table') !== false && Schema::hasTable('costois')) $markAsDone = true;
            if (strpos($migrationName, 'create_costos_table') !== false && Schema::hasTable('costos')) $markAsDone = true;
            if (strpos($migrationName, 'create_detalletrabajos_table') !== false && Schema::hasTable('detalletrabajos')) $markAsDone = true;
            if (strpos($migrationName, 'create_clientes_table') !== false && Schema::hasTable('clientes')) $markAsDone = true;
            if (strpos($migrationName, 'create_ordentrabajos_table') !== false && Schema::hasTable('ordentrabajos')) $markAsDone = true;
            
            // May columns
            if (strpos($migrationName, 'add_descripcion_to_costois_table') !== false && Schema::hasColumn('costois', 'descripcion')) $markAsDone = true;
            if (strpos($migrationName, 'add_etiquetas_to_atributos_tienda_table') !== false && Schema::hasColumn('atributos_tienda', 'etiquetas')) $markAsDone = true;
            if (strpos($migrationName, 'create_tipo_producto_atributo_tienda_table') !== false && Schema::hasTable('tipo_producto_atributo_tienda')) $markAsDone = true;
            if (strpos($migrationName, 'create_articulo_troquels_table') !== false && Schema::hasTable('articulo_troquels')) $markAsDone = true;
            if (strpos($migrationName, 'add_mostrar_en_producto_to_atributo_tienda_table') !== false && Schema::hasColumn('atributos_tienda', 'mostrar_en_producto')) $markAsDone = true;
            
            if ($markAsDone) {
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch
                ]);
                echo "Marked as done: $migrationName\n";
            }
        }
    }
}
