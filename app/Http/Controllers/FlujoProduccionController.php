<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FlujoProduccion;
use Illuminate\Support\Facades\DB;

class FlujoProduccionController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;
        $terminado = $request->terminado;

        $query = FlujoProduccion::with(['ordenTrabajo.cliente', 'ordenTrabajo.articulo', 'ordenTrabajo.linea.pedido', 'ordenTrabajo.entregas'])
            ->select('*', DB::raw("
                IF(fecha_termina IS NOT NULL, 
                    TIMESTAMPDIFF(MINUTE, 
                        CONCAT(fecha_inicia, ' ', hora_inicia), 
                        CONCAT(fecha_termina, ' ', hora_termina)
                    ), 
                    NULL
                ) as duracion_minutos
            "));

        if (!empty($fecha_inicio) || !empty($fecha_fin)) {
            $query->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                    $q->whereBetween('fecha_inicia', [$fecha_inicio, $fecha_fin])
                        ->orWhereBetween('fecha_termina', [$fecha_inicio, $fecha_fin]);
                } elseif (!empty($fecha_inicio)) {
                    $q->where('fecha_inicia', '>=', $fecha_inicio)
                        ->orWhere('fecha_termina', '>=', $fecha_inicio);
                } else {
                    // Si solo pone fecha fin, interpretamos que busca EXACTAMENTE ese día
                    $q->where('fecha_inicia', $fecha_fin)
                        ->orWhere('fecha_termina', $fecha_fin);
                }
            });
        }

        if ($terminado == 'true' || $terminado == 1) {
            $query->whereNotNull('fecha_termina');
        }

        if (!empty($buscar)) {
            if ($criterio == 'proceso') {
                $query->where('proceso', 'LIKE', '%' . $buscar . '%');
            } elseif ($criterio == 'usuario') {
                $query->where('usuario', 'LIKE', '%' . $buscar . '%');
            } elseif ($criterio == 'orden') {
                $query->where('orden_trabajo_id', $buscar);
            }
        }

        $flujo = $query->orderBy('id', 'desc')->paginate(50);

        return [
            'pagination' => [
                'total' => $flujo->total(),
                'current_page' => $flujo->currentPage(),
                'per_page' => $flujo->perPage(),
                'last_page' => $flujo->lastPage(),
                'from' => $flujo->firstItem(),
                'to' => $flujo->lastItem(),
            ],
            'flujo' => $flujo
        ];
    }

    public function stats(Request $request)
    {
        $fecha_inicio = $request->fecha_inicio ?: date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = $request->fecha_fin ?: date('Y-m-d');

        // Productividad por Usuario (cantidad total terminada y promedio de tiempo)
        $statsByUser = FlujoProduccion::select(
            'usuario',
            DB::raw('SUM(cantidad) as total_cantidad'),
            DB::raw('COUNT(*) as total_procesos'),
            DB::raw("ROUND(AVG(TIMESTAMPDIFF(MINUTE, CONCAT(fecha_inicia, ' ', hora_inicia), CONCAT(fecha_termina, ' ', hora_termina))), 2) as promedio_minutos")
        )
            ->whereNotNull('fecha_termina')
            ->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                $q->whereBetween('fecha_inicia', [$fecha_inicio, $fecha_fin])
                    ->orWhereBetween('fecha_termina', [$fecha_inicio, $fecha_fin]);
            })
            ->groupBy('usuario')
            ->get();

        // Promedio de tiempo por Proceso
        $statsByProcess = FlujoProduccion::select(
            'proceso',
            DB::raw('COUNT(*) as total'),
            DB::raw("ROUND(AVG(TIMESTAMPDIFF(MINUTE, CONCAT(fecha_inicia, ' ', hora_inicia), CONCAT(fecha_termina, ' ', hora_termina))), 2) as promedio_minutos")
        )
            ->whereNotNull('fecha_termina')
            ->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                $q->whereBetween('fecha_inicia', [$fecha_inicio, $fecha_fin])
                    ->orWhereBetween('fecha_termina', [$fecha_inicio, $fecha_fin]);
            })
            ->groupBy('proceso')
            ->get();

        // Productividad por dia (procesos terminados y total)
        $statsByDay = FlujoProduccion::select(
            'fecha_inicia',
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(fecha_termina) as terminados"),
            DB::raw("ROUND(AVG(TIMESTAMPDIFF(MINUTE, CONCAT(fecha_inicia, ' ', hora_inicia), CONCAT(fecha_termina, ' ', hora_termina))), 2) as promedio_minutos")
        )
            ->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                $q->whereBetween('fecha_inicia', [$fecha_inicio, $fecha_fin])
                    ->orWhereBetween('fecha_termina', [$fecha_inicio, $fecha_fin]);
            })
            ->groupBy('fecha_inicia')
            ->orderBy('fecha_inicia')
            ->get();

        // Cuellos de Botella (Procesos con más órdenes pendientes/sin terminar)
        $bottlenecks = FlujoProduccion::select(
            'proceso',
            DB::raw('COUNT(*) as total_pendientes')
        )
            ->whereNull('fecha_termina')
            ->groupBy('proceso')
            ->orderBy('total_pendientes', 'desc')
            ->get();


        return [
            'byUser' => $statsByUser,
            'byProcess' => $statsByProcess,
            'byDay' => $statsByDay,
            'bottlenecks' => $bottlenecks
        ];
    }

    public function getProcesos()
    {
        $procesos = FlujoProduccion::select('proceso')
            ->groupBy('proceso')
            ->get();
        return ['procesos' => $procesos];
    }

    public function flujoDelDia(Request $request)
    {
        $fecha = $request->fecha ?: date('Y-m-d');
        $flujo = FlujoProduccion::with(['ordenTrabajo.cliente', 'ordenTrabajo.articulo'])
            ->where('fecha_inicia', $fecha)
            ->orWhere('fecha_termina', $fecha)
            ->orderBy('id', 'desc')
            ->get();

        return ['flujo' => $flujo];
    }
}
