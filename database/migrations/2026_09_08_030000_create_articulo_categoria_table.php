<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateArticuloCategoriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('articulo_categoria')) {
            Schema::create('articulo_categoria', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('articulo_id');
                $table->unsignedInteger('categoria_id');
                $table->timestamps();

                $table->foreign('articulo_id')->references('id')->on('articulos')->onDelete('cascade');
                $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
                
                $table->unique(['articulo_id', 'categoria_id']);
            });

            // Migrar datos existentes de articulos.idcategoria -> articulo_categoria
            try {
                $articulos = DB::table('articulos')
                    ->whereNotNull('idcategoria')
                    ->where('idcategoria', '>', 0)
                    ->select('id', 'idcategoria')
                    ->get();

                $inserts = [];
                $now = date('Y-m-d H:i:s');
                foreach ($articulos as $art) {
                    // Verificar si existe la categoría en categorias para evitar error de FK
                    $existeCat = DB::table('categorias')->where('id', $art->idcategoria)->exists();
                    if ($existeCat) {
                        $inserts[] = [
                            'articulo_id' => $art->id,
                            'categoria_id' => $art->idcategoria,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }

                if (!empty($inserts)) {
                    DB::table('articulo_categoria')->insertOrIgnore($inserts);
                }
            } catch (\Exception $e) {
                \Log::error('Error migrando datos a articulo_categoria: ' . $e->getMessage());
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
        Schema::dropIfExists('articulo_categoria');
    }
}
