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
        
        if (!DB::table('migrations')->where('migration', $migrationName)->exists()) {
            $content = file_get_contents($migrationsPath . '/' . $file);
            $markAsDone = false;

            // Simple pattern matching for Schema::create('table')
            if (preg_match("/Schema::create\('([^']+)'/", $content, $matches)) {
                if (Schema::hasTable($matches[1])) $markAsDone = true;
            }
            // Simple pattern matching for Schema::table('table') and $table->column('col')
            elseif (preg_match("/Schema::table\('([^']+)'/", $content, $matches)) {
                $tableName = $matches[1];
                // Check for column additions
                if (preg_match("/\\\$table->[a-z]+\('([^']+)'/", $content, $colMatches)) {
                    if (Schema::hasColumn($tableName, $colMatches[1])) $markAsDone = true;
                }
            }

            if ($markAsDone) {
                DB::table('migrations')->insert(['migration' => $migrationName, 'batch' => $batch]);
                echo "Synced: $migrationName\n";
            }
        }
    }
}
