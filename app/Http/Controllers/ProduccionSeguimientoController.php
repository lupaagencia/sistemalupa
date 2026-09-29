<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ordentrabajo;
use App\Entrega;
use App\Articulo;
use App\statusProduccion;
use App\Detalletrabajo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProduccionSeguimientoController extends Controller
{
    /**
     * Get delivery statistics including product lead times and delivery distribution.
     */
    public function getDeliveryStats(Request $request)
    {
        $startDate = $request->fecha_inicio ?: '2024-01-01';
        $endDate = $request->fecha_fin ?: Carbon::now()->toDateString();
        $minOrders = $request->min_pedidos ?: 3;

        // Query product lead times (completed orders only, filtering out outliers)
        $productStats = DB::table('ordentrabajos as ot')
            ->join('articulos as art', 'ot.articulo_id', '=', 'art.id')
            ->leftJoin(DB::raw('(SELECT ordentrabajo_id, MAX(created_at) as fecha_entrega_real FROM entregas GROUP BY ordentrabajo_id) as e'), 'ot.id', '=', 'e.ordentrabajo_id')
            ->where('ot.produccion', 'T')
            ->where('ot.created_at', '>=', $startDate)
            ->where('ot.created_at', '<=', $endDate . ' 23:59:59')
            ->where(DB::raw('COALESCE(e.fecha_entrega_real, ot.updated_at)'), '>=', '2024-01-01')
            ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '>=', 0)
            ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '<=', 180)
            ->select(
                'art.id as articulo_id',
                'art.nombre as producto',
                DB::raw('COUNT(ot.id) as total_pedidos'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))), 1) as promedio_dias'),
                DB::raw('MAX(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))) as maximo_dias'),
                DB::raw('MIN(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))) as minimo_dias')
            )
            ->groupBy('art.id', 'art.nombre')
            ->havingRaw('COUNT(ot.id) >= ?', [$minOrders])
            ->orderBy('promedio_dias', 'desc')
            ->get();

        // Query overall delivery distribution
        $distribution = DB::table('ordentrabajos as ot')
            ->leftJoin(DB::raw('(SELECT ordentrabajo_id, MAX(created_at) as fecha_entrega_real FROM entregas GROUP BY ordentrabajo_id) as e'), 'ot.id', '=', 'e.ordentrabajo_id')
            ->where('ot.produccion', 'T')
            ->where('ot.created_at', '>=', $startDate)
            ->where('ot.created_at', '<=', $endDate . ' 23:59:59')
            ->where(DB::raw('COALESCE(e.fecha_entrega_real, ot.updated_at)'), '>=', '2024-01-01')
            ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '>=', 0)
            ->where(DB::raw('TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))'), '<=', 180)
            ->select(
                DB::raw('COUNT(ot.id) as total_pedidos'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at))), 1) as promedio_general'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) <= 7 THEN 1 ELSE 0 END) as en_1_semana'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) > 7 AND TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) <= 15 THEN 1 ELSE 0 END) as en_2_semanas'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) > 15 AND TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) <= 30 THEN 1 ELSE 0 END) as en_un_mes'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(DAY, ot.created_at, COALESCE(e.fecha_entrega_real, ot.updated_at)) > 30 THEN 1 ELSE 0 END) as mas_de_un_mes')
            )
            ->first();

        return response()->json([
            'products' => $productStats,
            'distribution' => $distribution
        ]);
    }

    /**
     * Get active orders grouped by their current production stage (pipeline).
     */
    public function getActiveProgress(Request $request)
    {
        // Get active orders
        $activeOrders = Ordentrabajo::whereIn('produccion', ['ENP', 'EP', 'P', 'D', 'EM', 'A'])
            ->with(['articulo', 'cliente', 'status' => function($q) {
                $q->orderBy('id', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $stages = [
            'Sin iniciar' => [],
            'Planchas' => [],
            'Corte material' => [],
            'Impresión' => [],
            'Plastificado' => [],
            'Troquelado' => [],
            'Espera Terminado' => [],
            'Terminado' => [],
            'Para entregar' => [],
            'Otros' => []
        ];

        foreach ($activeOrders as $o) {
            // Get current stage from most recent statusProduccion
            $status = $o->status->first();
            $stageName = $status ? trim($status->estado) : 'Sin iniciar';

            if (!array_key_exists($stageName, $stages)) {
                // Fallback / mapping for variations in stage names
                if (stripos($stageName, 'plancha') !== false) {
                    $stageName = 'Planchas';
                } elseif (stripos($stageName, 'corte') !== false) {
                    $stageName = 'Corte material';
                } elseif (stripos($stageName, 'impre') !== false) {
                    $stageName = 'Impresión';
                } elseif (stripos($stageName, 'plast') !== false) {
                    $stageName = 'Plastificado';
                } elseif (stripos($stageName, 'troque') !== false) {
                    $stageName = 'Troquelado';
                } elseif (stripos($stageName, 'espera') !== false) {
                    $stageName = 'Espera Terminado';
                } elseif (stripos($stageName, 'entrega') !== false) {
                    $stageName = 'Para entregar';
                } else {
                    $stageName = 'Otros';
                }
            }

            // Calculate age in days
            $ageDays = Carbon::parse($o->created_at)->diffInDays(Carbon::now());

            $stages[$stageName][] = [
                'id' => $o->id,
                'producto' => $o->articulo ? $o->articulo->nombre : 'N/A',
                'cliente' => $o->cliente ? $o->cliente->razonsocial : 'N/A',
                'cantidad' => $o->cantidad,
                'prioridad' => $o->prioridad ?: 1, // 1 = Baja, 2 = Media, 3 = Alta
                'fecha_orden' => $o->fecha,
                'fecha_entrega_prometida' => $o->fecha_entrega,
                'dias_activo' => $ageDays,
                'status_observaciones' => $status ? $status->observaciones : ''
            ];
        }

        return response()->json($stages);
    }

    /**
     * Calculate print optimization gang-run suggestions and priority scheduling.
     */
    public function getPrintOptimization(Request $request)
    {
        // Get active orders that are in production / setup phases
        $orders = Ordentrabajo::whereIn('produccion', ['ENP', 'EP', 'P', 'D'])
            ->with(['articulo', 'cliente', 'detalles'])
            ->get();

        $activeJobs = [];

        foreach ($orders as $o) {
            // Find paper type details
            $paperDetail = $o->detalles->first(function($d) {
                return strcasecmp($d->titulo, 'Papel') === 0 || strcasecmp($d->titulo, 'Material') === 0;
            });
            $paper = $paperDetail ? $paperDetail->valor : 'No especificado';

            // Find ink details
            $inkDetail = $o->detalles->first(function($d) {
                return strcasecmp($d->titulo, 'Tinta') === 0;
            });
            
            $inks = [];
            if ($inkDetail && $inkDetail->valor) {
                $decoded = json_decode($inkDetail->valor, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $inkItem) {
                        if (isset($inkItem['pantone'])) {
                            $inks[] = $inkItem['pantone'];
                        }
                    }
                } else {
                    $inks[] = $inkDetail->valor;
                }
            }

            // Extract plancha/machine details
            // Order has plancha (which references InventariosMateriaPrima plancha)
            $planchaName = 'No asignada';
            if ($o->plancha) {
                $plancha = \App\InventariosMateriaPrima::find($o->plancha);
                if ($plancha) {
                    $planchaName = $plancha->referencia ?: 'Plancha #' . $o->plancha;
                }
            }

            $activeJobs[] = [
                'id' => $o->id,
                'producto' => $o->articulo ? $o->articulo->nombre : 'N/A',
                'cliente' => $o->cliente ? $o->cliente->razonsocial : 'N/A',
                'cantidad' => (int) $o->cantidad,
                'cabida' => (int) ($o->cabida ?: 1),
                'tamano' => $o->tamano,
                'medida_final' => $o->medida_final,
                'medida_material' => $o->medida_material,
                'prioridad' => (int) ($o->prioridad ?: 1),
                'fecha_entrega' => $o->fecha_entrega,
                'papel' => $paper,
                'plancha' => $planchaName,
                'tintas' => $inks,
                'hojas_requeridas' => ceil((int)$o->cantidad / max((int)$o->cabida, 1))
            ];
        }

        // Grouping algorithm for print optimization (gang-run printing)
        // Group by Plancha and Paper Type
        $suggestions = [];
        $setupMinutesSaved = 0;
        $sheetsSaved = 0;
        $groupCount = 0;

        $grouped = collect($activeJobs)->groupBy(function($item) {
            // Group key: plancha_name + '|' + paper_type
            // Standardize paper name to avoid minor casing differences
            $cleanPaper = strtolower(trim($item['papel']));
            $cleanPlancha = strtolower(trim($item['plancha']));
            return $cleanPlancha . '|' . $cleanPaper;
        });

        foreach ($grouped as $key => $group) {
            if ($group->count() > 1) {
                // If more than 1 job, co-printing is possible!
                list($plancha, $paper) = explode('|', $key);
                $groupCount++;

                // Setup savings: 35 mins per grouped order avoided
                $setupSaved = ($group->count() - 1) * 35;
                $setupMinutesSaved += $setupSaved;

                // Sheet savings: 50 startup sheets saved per grouped order
                $sheets = ($group->count() - 1) * 50;
                $sheetsSaved += $sheets;

                $suggestions[] = [
                    'id' => $groupCount,
                    'plancha' => ucwords($plancha),
                    'papel' => ucwords($paper),
                    'total_trabajos' => $group->count(),
                    'trabajos' => $group->toArray(),
                    'max_hojas_run' => $group->max('hojas_requeridas'),
                    'total_cantidad_grupo' => $group->sum('cantidad'),
                    'tiempo_setup_ahorrado' => $setupSaved,
                    'hojas_merma_ahorradas' => $sheets
                ];
            }
        }

        // Priority sequence for general printing scheduler (all jobs sorted)
        $scheduledJobs = collect($activeJobs)->sort(function($a, $b) {
            // 1. High priority first (prioridad = 3 is high, 1 is low)
            if ($a['prioridad'] !== $b['prioridad']) {
                return $b['prioridad'] <=> $a['prioridad'];
            }
            // 2. Earliest delivery date first
            if ($a['fecha_entrega'] && $b['fecha_entrega']) {
                return strcmp($a['fecha_entrega'], $b['fecha_entrega']);
            }
            // 3. Fallback to order ID
            return $a['id'] <=> $b['id'];
        })->values()->toArray();

        return response()->json([
            'suggestions' => $suggestions,
            'scheduled_jobs' => $scheduledJobs,
            'summary' => [
                'total_suggestions' => count($suggestions),
                'total_jobs_optimized' => collect($suggestions)->sum('total_trabajos'),
                'total_setup_minutes_saved' => $setupMinutesSaved,
                'total_setup_hours_saved' => round($setupMinutesSaved / 60, 1),
                'total_sheets_saved' => $sheetsSaved
            ]
        ]);
    }
}
