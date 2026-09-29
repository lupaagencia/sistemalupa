<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AjustesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sqlPath = base_path('database/ajustes_data.sql');
        if (file_exists($sqlPath)) {
            DB::unprepared(file_get_contents($sqlPath));
        }
    }
}
