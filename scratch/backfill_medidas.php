<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$articulos = App\Articulo::whereNotNull('medida_final')
    ->where(function($q) {
        $q->whereNull('ancho_final')->orWhere('ancho_final', 0);
    })->get();

$count = 0;
foreach ($articulos as $art) {
    if (stripos($art->medida_final, 'x') !== false) {
        $parts = explode('x', strtolower($art->medida_final));
        $art->ancho_final = (float)trim($parts[0]);
        $art->largo_final = (float)trim($parts[1]);
        if ($art->ancho_final > 0 && $art->largo_final > 0) {
            $art->save();
            $count++;
        }
    }
}

echo "Procesados $count artículos.";
