<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Comprobante;
use App\Cliente;
use App\Articulo;
use Carbon\Carbon;

class EstadisticasVentasController extends Controller
{
    /**
     * Retorna resumen de ventas, clientes top, productos top e inactivos
     */
    public function getResumen(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', null); // null = todo el año
        $fechaInicio = $request->get('fecha_inicio', null);
        $fechaFin = $request->get('fecha_fin', null);

        // Construir filtro de fecha base para comprobantes
        $queryBase = DB::table('comprobantes')
            ->whereIn('tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
            ->where('estado', '!=', 0); // No anulados

        if ($fechaInicio && $fechaFin) {
            $queryBase->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        } elseif ($month) {
            $queryBase->whereYear('fecha', $year)->whereMonth('fecha', $month);
        } else {
            $queryBase->whereYear('fecha', $year);
        }

        // 1. Totales Generales
        $totalVentas = (float) (clone $queryBase)->sum('total');
        $totalSubtotal = (float) (clone $queryBase)->sum('subtotal');
        $totalPedidos = (int) (clone $queryBase)->count('id');
        $ticketPromedio = $totalPedidos > 0 ? ($totalVentas / $totalPedidos) : 0;

        // 2. Comparativa con Período Anterior
        $ventasAnteriores = 0;
        if ($month) {
            $prevDate = Carbon::createFromDate($year, $month, 1)->subMonth();
            $ventasAnteriores = (float) DB::table('comprobantes')
                ->whereIn('tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
                ->where('estado', '!=', 0)
                ->whereYear('fecha', $prevDate->year)
                ->whereMonth('fecha', $prevDate->month)
                ->sum('total');
        } else {
            $ventasAnteriores = (float) DB::table('comprobantes')
                ->whereIn('tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
                ->where('estado', '!=', 0)
                ->whereYear('fecha', $year - 1)
                ->sum('total');
        }
        $variacionPorcentaje = $ventasAnteriores > 0 
            ? (($totalVentas - $ventasAnteriores) / $ventasAnteriores) * 100 
            : 0;

        // 3. Top Clientes (Monto $ y % de Aportación)
        $clientesTopQuery = DB::table('comprobantes as c')
            ->leftJoin('clientes as cl', 'c.cliente_id', '=', 'cl.id')
            ->leftJoin('personas as p', 'cl.id', '=', 'p.id')
            ->whereIn('c.tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
            ->where('c.estado', '!=', 0);

        if ($fechaInicio && $fechaFin) {
            $clientesTopQuery->whereBetween('c.fecha', [$fechaInicio, $fechaFin]);
        } elseif ($month) {
            $clientesTopQuery->whereYear('c.fecha', $year)->whereMonth('c.fecha', $month);
        } else {
            $clientesTopQuery->whereYear('c.fecha', $year);
        }

        $clientesTopRaw = $clientesTopQuery
            ->select(
                'c.cliente_id',
                DB::raw("COALESCE(NULLIF(cl.razonsocial, ''), NULLIF(p.nombre, ''), 'Cliente no registrado') as nombre_cliente"),
                DB::raw("COUNT(c.id) as total_pedidos"),
                DB::raw("SUM(c.total) as total_comprado"),
                DB::raw("MAX(c.fecha) as ultima_compra")
            )
            ->groupBy('c.cliente_id', 'nombre_cliente')
            ->orderByDesc('total_comprado')
            ->get();

        $clientesTop = $clientesTopRaw->map(function ($item) use ($totalVentas) {
            $monto = (float) $item->total_comprado;
            $item->total_comprado = $monto;
            $item->porcentaje_aportacion = $totalVentas > 0 ? round(($monto / $totalVentas) * 100, 2) : 0;
            $item->dias_desde_ultima = $item->ultima_compra ? Carbon::parse($item->ultima_compra)->diffInDays(Carbon::now()) : null;
            return $item;
        });

        // 4. Top Productos por Volumen y Aportación Financiera
        $productosTopQuery = DB::table('linea_comprobantes as lc')
            ->join('comprobantes as c', 'lc.comprobante_id', '=', 'c.id')
            ->leftJoin('articulos as a', 'lc.articulo_id', '=', 'a.id')
            ->whereIn('c.tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
            ->where('c.estado', '!=', 0);

        if ($fechaInicio && $fechaFin) {
            $productosTopQuery->whereBetween('c.fecha', [$fechaInicio, $fechaFin]);
        } elseif ($month) {
            $productosTopQuery->whereYear('c.fecha', $year)->whereMonth('c.fecha', $month);
        } else {
            $productosTopQuery->whereYear('c.fecha', $year);
        }

        $productosTopRaw = $productosTopQuery
            ->select(
                'lc.articulo_id',
                DB::raw("COALESCE(a.nombre, 'Servicio / Referencia Personalizada') as nombre_producto"),
                DB::raw("COALESCE(a.codigo, 'N/A') as codigo_referencia"),
                DB::raw("SUM(lc.cantidad) as cantidad_total"),
                DB::raw("SUM(COALESCE(lc.valor_total, lc.subtotal, 0)) as ingresos_totales"),
                DB::raw("AVG(lc.valor_unitario) as precio_promedio")
            )
            ->groupBy('lc.articulo_id', 'nombre_producto', 'codigo_referencia')
            ->get();

        $totalIngresosProductos = (float) $productosTopRaw->sum('ingresos_totales');

        $productosProcesados = $productosTopRaw->map(function ($p) use ($totalIngresosProductos) {
            $ingresos = (float) $p->ingresos_totales;
            $p->cantidad_total = (float) $p->cantidad_total;
            $p->ingresos_totales = $ingresos;
            $p->precio_promedio = (float) $p->precio_promedio;
            $p->porcentaje_aportacion = $totalIngresosProductos > 0 ? round(($ingresos / $totalIngresosProductos) * 100, 2) : 0;
            return $p;
        });

        // Top por Unidades
        $productosPorVolumen = $productosProcesados->sortByDesc('cantidad_total')->values()->take(15);
        // Top por Ingresos ($)
        $productosPorIngreso = $productosProcesados->sortByDesc('ingresos_totales')->values()->take(15);

        // 5. Análisis de Frecuencia y Clientes Inactivos (General / Histórico)
        $hoy = Carbon::now();
        $inactivosQuery = DB::table('comprobantes as c')
            ->leftJoin('clientes as cl', 'c.cliente_id', '=', 'cl.id')
            ->leftJoin('personas as p', 'cl.id', '=', 'p.id')
            ->whereIn('c.tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
            ->where('c.estado', '!=', 0)
            ->select(
                'c.cliente_id',
                DB::raw("COALESCE(NULLIF(cl.razonsocial, ''), NULLIF(p.nombre, ''), 'Cliente no registrado') as nombre_cliente"),
                DB::raw("COUNT(c.id) as total_pedidos_historico"),
                DB::raw("SUM(c.total) as total_historico"),
                DB::raw("MAX(c.fecha) as ultima_compra_fecha"),
                DB::raw("MIN(c.fecha) as primera_compra_fecha")
            )
            ->groupBy('c.cliente_id', 'nombre_cliente')
            ->get();

        $clientesAnalisisFrecuencia = $inactivosQuery->map(function ($cl) use ($hoy) {
            $ultima = Carbon::parse($cl->ultima_compra_fecha);
            $primera = Carbon::parse($cl->primera_compra_fecha);
            $diasInactivo = $ultima->diffInDays($hoy);
            
            $diasRango = $primera->diffInDays($ultima);
            $frecuenciaPromedioDias = ($cl->total_pedidos_historico > 1 && $diasRango > 0)
                ? round($diasRango / ($cl->total_pedidos_historico - 1), 1)
                : 0;

            $estado = 'Activo';
            if ($diasInactivo > 90) {
                $estado = 'Inactivo (>90 días)';
            } elseif ($diasInactivo > 60) {
                $estado = 'Riesgo Alto (60-90 días)';
            } elseif ($diasInactivo > 30) {
                $estado = 'Riesgo Moderado (30-60 días)';
            }

            return [
                'cliente_id' => $cl->cliente_id,
                'nombre_cliente' => $cl->nombre_cliente,
                'total_pedidos' => (int) $cl->total_pedidos_historico,
                'total_historico' => (float) $cl->total_historico,
                'ultima_compra' => $cl->ultima_compra_fecha,
                'dias_inactivo' => $diasInactivo,
                'frecuencia_promedio_dias' => $frecuenciaPromedioDias,
                'estado_fidelidad' => $estado
            ];
        });

        $clientesInactivos = $clientesAnalisisFrecuencia
            ->filter(function ($item) {
                return $item['dias_inactivo'] > 30;
            })
            ->sortByDesc('dias_inactivo')
            ->values();

        // 6. Evolución Mensual del Año Seleccionado (Línea de Tendencia)
        $tendenciaMensualRaw = DB::table('comprobantes')
            ->whereIn('tipo', ['factura', 'pedido', 'cotizacion_facturada', 'venta'])
            ->where('estado', '!=', 0)
            ->whereYear('fecha', $year)
            ->select(
                DB::raw("MONTH(fecha) as mes"),
                DB::raw("SUM(total) as total_mes"),
                DB::raw("COUNT(id) as pedidos_mes")
            )
            ->groupBy(DB::raw("MONTH(fecha)"))
            ->orderBy('mes')
            ->get();

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $tendenciaMensual = [];
        for ($m = 1; $m <= 12; $m++) {
            $found = $tendenciaMensualRaw->firstWhere('mes', $m);
            $tendenciaMensual[] = [
                'mes_num' => $m,
                'mes_nombre' => $mesesNombres[$m],
                'total' => $found ? (float) $found->total_mes : 0,
                'pedidos' => $found ? (int) $found->pedidos_mes : 0
            ];
        }

        return response()->json([
            'periodo' => [
                'year' => $year,
                'month' => $month,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'totales' => [
                'total_ventas' => $totalVentas,
                'total_subtotal' => $totalSubtotal,
                'total_pedidos' => $totalPedidos,
                'ticket_promedio' => round($ticketPromedio, 2),
                'ventas_anteriores' => $ventasAnteriores,
                'variacion_porcentaje' => round($variacionPorcentaje, 2)
            ],
            'clientes_top' => $clientesTop->values(),
            'productos_por_volumen' => $productosPorVolumen,
            'productos_por_ingreso' => $productosPorIngreso,
            'clientes_inactivos' => $clientesInactivos,
            'frecuencia_clientes' => $clientesAnalisisFrecuencia->sortByDesc('total_historico')->take(30)->values(),
            'tendencia_mensual' => $tendenciaMensual
        ]);
    }
}
