<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Articulo;
use App\InventariosMateriaPrima;
use App\Ordentrabajo;

echo "=== ARTICULOS ===\n";
$articulos = Articulo::orderBy('id', 'desc')->take(5)->get();
foreach ($articulos as $art) {
    echo "ID: {$art->id} | Nombre: {$art->nombre} | Codigo: {$art->codigo} | Cabidas: " . json_encode($art->cabidas_materiales) . "\n";
}

echo "\n=== PLANCHAS (tipo = Plancha) ===\n";
$planchas = InventariosMateriaPrima::where('tipo', 'Plancha')->take(10)->get();
foreach ($planchas as $pl) {
    echo "ID: {$pl->id} | Cliente ID (asignado_id): {$pl->asignado_id} | Referencia: {$pl->referencia} | Detalles: {$pl->detalles}\n";
}

echo "\n=== RECENT WORK ORDERS ===\n";
$ordenes = Ordentrabajo::orderBy('id', 'desc')->take(5)->get();
foreach ($ordenes as $o) {
    echo "ID: {$o->id} | Cliente ID: {$o->cliente_id} | Articulo ID: {$o->articulo_id} | Plancha: {$o->plancha} | Cabida: {$o->cabida} | Medida Material: {$o->medida_material} | Sobrante: {$o->carpeta_cliente}\n";
}
