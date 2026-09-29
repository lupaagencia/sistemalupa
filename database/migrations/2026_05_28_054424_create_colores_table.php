<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('pantone');
            $table->string('hex', 255);
            $table->string('paleta', 10);
            $table->timestamps();
        });

        // Import existing data if files exist
        foreach (['p', 'c', 'u'] as $paleta) {
            $path = public_path("colors{$paleta}.json");
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $colors = json_decode($content, true);
                if (is_array($colors)) {
                    $insertData = [];
                    foreach ($colors as $color) {
                        if (isset($color['pantone']) && isset($color['hex'])) {
                            $insertData[] = [
                                'pantone' => $color['pantone'],
                                'hex' => $color['hex'],
                                'paleta' => $paleta,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    if (!empty($insertData)) {
                        foreach (array_chunk($insertData, 500) as $chunk) {
                            \Illuminate\Support\Facades\DB::table('colores')->insert($chunk);
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('colores');
    }
}
