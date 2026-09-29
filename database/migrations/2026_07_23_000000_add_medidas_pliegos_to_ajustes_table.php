<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMedidasPliegosToAjustesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $medidas = [
            // Pliego 70x100
            ['tipo' => 'medidas_pliego', 'detalle' => '1/1 Pliego Entero (70x100 cm)', 'valor' => '70x100', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/2 Medio Pliego (50x70 cm)', 'valor' => '50x70', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/3 Tercio Pliego Horizontal (33.3x70 cm)', 'valor' => '33.3x70', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/3 Tercio Pliego Vertical (23.3x100 cm)', 'valor' => '23.3x100', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/4 Cuarto Pliego (35x50 cm)', 'valor' => '35x50', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/5 Quinto Pliego (30x40 cm)', 'valor' => '30x40', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/6 Sexto Pliego (33.3x35 cm)', 'valor' => '33.3x35', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/6 Sexto Pliego Alargado (23.3x50 cm)', 'valor' => '23.3x50', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/7 Séptimo Pliego (25x40 cm)', 'valor' => '25x40', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/8 Octavo Pliego (25x35 cm)', 'valor' => '25x35', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/9 Noveno Pliego (23.3x33.3 cm)', 'valor' => '23.3x33.3', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/10 Décimo Pliego (20x35 cm)', 'valor' => '20x35', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/12 Doceavo Pliego (23.3x25 cm)', 'valor' => '23.3x25', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/12 Doceavo Pliego Alargado (11.6x50 cm)', 'valor' => '11.6x50', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/14 Catorceavo Pliego (20x25 cm)', 'valor' => '20x25', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/15 Quinceavo Pliego (20x23.3 cm)', 'valor' => '20x23.3', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/16 Dieciseisavo Pliego (17.5x25 cm)', 'valor' => '17.5x25', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/18 Dieciochoavo Pliego (16.6x23.3 cm)', 'valor' => '16.6x23.3', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/20 Veinteavo Pliego (14x25 cm)', 'valor' => '14x25', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/20 Veinteavo Pliego Cuadrado (17.5x20 cm)', 'valor' => '17.5x20', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/24 Veinticuatroavo Pliego (11.6x25 cm)', 'valor' => '11.6x25', 'categoria' => '70x100'],
            ['tipo' => 'medidas_pliego', 'detalle' => '1/32 Treintaidosavo Pliego (12.5x17.5 cm)', 'valor' => '12.5x17.5', 'categoria' => '70x100'],        ];

        foreach ($medidas as $medida) {
            DB::table('ajustes')->updateOrInsert(
                [
                    'tipo' => $medida['tipo'],
                    'detalle' => $medida['detalle'],
                    'categoria' => $medida['categoria']
                ],
                [
                    'valor' => $medida['valor']
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('ajustes')->where('tipo', 'medidas_pliego')->delete();
    }
}
