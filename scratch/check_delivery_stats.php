<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ordentrabajo;
use App\Entrega;
use App\Articulo;
use Illuminate\Support\Facades\DB;

echo "--- DISTINCT DETALLETRABAJO TITLES ---\n";
$titles = \App\Detalletrabajo::select('titulo', DB::raw('count(*) as total'))
    ->groupBy('titulo')
    ->orderBy('total', 'desc')
    ->get();
foreach ($titles as $t) {
    echo "Title: {$t->titulo} | Total: {$t->total}\n";
}

echo "\n--- SAMPLE DETALLETRABAJO FOR PAPER/INK/COLOR ---\n";
$samples = \App\Detalletrabajo::whereIn('titulo', ['Papel', 'Tinta', 'Plancha', 'Color', 'Material'])
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();
foreach ($samples as $s) {
    echo "Order: {$s->ordentrabajo_id} | Title: {$s->titulo} | Desc: {$s->descripcion} | Valor: {$s->valor}\n";
}




// Check if we can calculate delivery lead times (time between ordentrabajo.created_at and entrega.fecha/created_at)
echo "\n--- Delivery Lead Time Stats (Sample calculation) ---\n";
$leadTimes = Entrega::join('ordentrabajos', 'entregas.ordentrabajo_id', '=', 'ordentrabajos.id')
    ->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')
    ->select(
        'articulos.nombre as producto',
        'ordentrabajos.id as orden_id',
        'ordentrabajos.created_at as orden_creada',
        'entregas.created_at as entrega_fecha',
        DB::raw('TIMESTAMPDIFF(DAY, ordentrabajos.created_at, entregas.created_at) as dias_demora')
    )
    ->limit(5)
    ->get();

foreach ($leadTimes as $lt) {
    echo "Orden #{$lt->orden_id} | Producto: {$lt->producto} | Creada: {$lt->orden_creada} | Entregada: {$lt->entrega_fecha} | Demora: {$lt->dias_demora} dias\n";
}
