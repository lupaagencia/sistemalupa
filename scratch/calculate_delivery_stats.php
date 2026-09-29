<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ordentrabajo;
use App\Entrega;
use App\Articulo;
use Illuminate\Support\Facades\DB;

echo "--- RUNNING PRODUCT DELIVERY TIME CALCULATIONS ---\n";

// We want to calculate the delivery time for completed orders (produccion = 'T')
// We can use:
// 1. The timestamp of the last Entrega record for that order if it exists.
// 2. Otherwise, the updated_at timestamp of the Ordentrabajo when produccion = 'T'.
// Let's build a query that combines both, joining with Articulo to group by product.

$query = DB::table('ordentrabajos as ot')
    ->join('articulos as art', 'ot.articulo_id', '=', 'art.id')
    ->leftJoin(DB::raw('(SELECT ordentrabajo_id, MAX(created_at) as fecha_entrega_real FROM entregas GROUP BY ordentrabajo_id) as e'), 'ot.id', '=', 'e.ordentrabajo_id')
    ->where('ot.produccion', 'T')
    ->where('ot.created_at', '>=', '2024-01-01')
    ->where(DB::raw('COALESCE(e.fecha_entrega_real, ot.updated_at)'), '>=', '2024-01-01')
    ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '>=', 0)
    ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '<=', 180) // filter out outliers > 6 months
    ->select(
        'art.id as articulo_id',
        'art.nombre as producto',
        DB::raw('COUNT(ot.id) as total_pedidos'),
        DB::raw('AVG(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))) as promedio_dias'),
        DB::raw('MAX(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))) as maximo_dias'),
        DB::raw('MIN(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))) as minimo_dias')
    )
    ->groupBy('art.id', 'art.nombre')
    ->havingRaw('COUNT(ot.id) >= 3')
    ->orderBy('promedio_dias', 'desc')
    ->limit(10);

$results = $query->get();

echo "\nTOP 10 SLOWEST PRODUCTS TO DELIVER (with at least 3 orders):\n";
foreach ($results as $row) {
    printf(
        "%-30s | Pedidos: %3d | Promedio: %5.1f dias | Min: %2d dias | Max: %3d dias\n",
        $row->producto,
        $row->total_pedidos,
        $row->promedio_dias,
        $row->minimo_dias,
        $row->maximo_dias
    );
}

// Let's also do a general distribution of delivery times for all orders
$distribution = DB::table('ordentrabajos as ot')
    ->leftJoin(DB::raw('(SELECT ordentrabajo_id, MAX(created_at) as fecha_entrega_real FROM entregas GROUP BY ordentrabajo_id) as e'), 'ot.id', '=', 'e.ordentrabajo_id')
    ->where('ot.produccion', 'T')
    ->where('ot.created_at', '>=', '2024-01-01')
    ->where(DB::raw('COALESCE(e.fecha_entrega_real, ot.updated_at)'), '>=', '2024-01-01')
    ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '>=', 0)
    ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '<=', 180)
    ->select(
        DB::raw('COUNT(ot.id) as total_pedidos'),
        DB::raw('AVG(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))) as promedio_general'),
        DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) <= 7 THEN 1 ELSE 0 END) as en_1_semana'),
        DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) > 7 AND TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) <= 15 THEN 1 ELSE 0 END) as en_2_semanas'),
        DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) > 15 AND TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) <= 30 THEN 1 ELSE 0 END) as en_un_mes'),
        DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) > 30 THEN 1 ELSE 0 END) as mas_de_un_mes')
    )
    ->first();

echo "\nGENERAL DELIVERY TIME DISTRIBUTION:\n";
echo "Total Completed Orders: {$distribution->total_pedidos}\n";
echo "Average Delivery Lead Time: " . round($distribution->promedio_general, 1) . " days\n";
echo "Delivered in <= 7 days: {$distribution->en_1_semana} (" . round(($distribution->en_1_semana / $distribution->total_pedidos) * 100, 1) . "%)\n";
echo "Delivered in 8-15 days: {$distribution->en_2_semanas} (" . round(($distribution->en_2_semanas / $distribution->total_pedidos) * 100, 1) . "%)\n";
echo "Delivered in 16-30 days: {$distribution->en_un_mes} (" . round(($distribution->en_un_mes / $distribution->total_pedidos) * 100, 1) . "%)\n";
echo "Delivered in > 30 days: {$distribution->mas_de_un_mes} (" . round(($distribution->mas_de_un_mes / $distribution->total_pedidos) * 100, 1) . "%)\n";
