<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::table('tipo_producto')->updateOrInsert(
        ['nombre' => 'Caja Tipo L (Estándar)'],
        [
            'valor' => 0,
            'formula_ancho' => '(W*2) + (H*2) + P1 + P2',
            'formula_largo' => 'L + (H*2) + (PL*2)',
            'descripcion' => 'Caja estándar con dos pestañas y pestaña lateral',
            'tamano' => 1,
            'area' => 0,
            'cabida' => 1,
            'sobrante' => 50,
            'impresiones' => 1,
            'estado' => 1,
            'tipo_cantidad' => '{"tipo_cantidad":1,"valores":[1,1000]}',
            'rangos' => '[]',
            'imagen' => '',
            'orden' => 1
        ]
    );
    echo "Caja L creada.\n";

    DB::table('tipo_producto')->updateOrInsert(
        ['nombre' => 'Caja Tipo LS (Trapezoidal)'],
        [
            'valor' => 0,
            'formula_ancho' => '(W1 + W2) + (H*2) + P1 + P2',
            'formula_largo' => 'L1 + (H*2) + (PL*2)',
            'descripcion' => 'Caja trapezoidal con base y tapa desiguales',
            'tamano' => 1,
            'area' => 0,
            'cabida' => 1,
            'sobrante' => 50,
            'impresiones' => 1,
            'estado' => 1,
            'tipo_cantidad' => '{"tipo_cantidad":1,"valores":[1,1000]}',
            'rangos' => '[]',
            'imagen' => '',
            'orden' => 1
        ]
    );
    echo "Caja LS creada.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
