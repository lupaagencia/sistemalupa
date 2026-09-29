<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cuenta;
use App\AsientoDetalle;
use App\ComprobanteContable;
use Illuminate\Support\Facades\DB;

class ReporteContableController extends Controller
{
    /**
     * Reporte: Balance General (Balance Sheet)
     * Activo, Pasivo, Patrimonio al corte de fecha_fin
     */
    public function balanceGeneral(Request $request)
    {
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));

        // 1. Obtener todas las cuentas registradas en el PUC
        $cuentas = Cuenta::orderBy('codigo', 'asc')->get()->keyBy('id');

        // Inicializar balances en 0
        foreach ($cuentas as $cuenta) {
            $cuenta->balance = 0.00;
        }

        // 2. Consultar sumas acumuladas de débitos y créditos para cuentas de detalle
        $balancesDetalle = DB::table('asientos_detalles')
            ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
            ->select(
                'asientos_detalles.cuenta_id',
                DB::raw('SUM(asientos_detalles.debe) as total_debe'),
                DB::raw('SUM(asientos_detalles.haber) as total_haber')
            )
            ->where('comprobantes_contables.fecha', '<=', $fechaFin)
            ->groupBy('asientos_detalles.cuenta_id')
            ->get();

        // 3. Asignar balances a cuentas de detalle según su naturaleza
        foreach ($balancesDetalle as $bal) {
            if (isset($cuentas[$bal->cuenta_id])) {
                $cuenta = $cuentas[$bal->cuenta_id];
                if ($cuenta->naturaleza === 'Debito') {
                    $cuenta->balance = (float)$bal->total_debe - (float)$bal->total_haber;
                } else {
                    $cuenta->balance = (float)$bal->total_haber - (float)$bal->total_debe;
                }
            }
        }

        // 4. Calcular Utilidad o Pérdida del Ejercicio hasta fecha_fin (Ingresos - Gastos - Costos)
        $balancesResultados = DB::table('asientos_detalles')
            ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
            ->join('cuentas', 'asientos_detalles.cuenta_id', '=', 'cuentas.id')
            ->select(
                'cuentas.tipo',
                DB::raw('SUM(asientos_detalles.debe) as total_debe'),
                DB::raw('SUM(asientos_detalles.haber) as total_haber')
            )
            ->where('comprobantes_contables.fecha', '<=', $fechaFin)
            ->whereIn('cuentas.tipo', ['Ingreso', 'Gasto', 'Costo'])
            ->groupBy('cuentas.tipo')
            ->get()
            ->keyBy('tipo');

        $totalIngresos = isset($balancesResultados['Ingreso']) ? ((float)$balancesResultados['Ingreso']->total_haber - (float)$balancesResultados['Ingreso']->total_debe) : 0.00;
        $totalGastos = isset($balancesResultados['Gasto']) ? ((float)$balancesResultados['Gasto']->total_debe - (float)$balancesResultados['Gasto']->total_haber) : 0.00;
        $totalCostos = isset($balancesResultados['Costo']) ? ((float)$balancesResultados['Costo']->total_debe - (float)$balancesResultados['Costo']->total_haber) : 0.00;
        $utilidadEjercicio = $totalIngresos - ($totalGastos + $totalCostos);

        // 5. Mapear Utilidad a cuenta de Patrimonio (360505 - Utilidad del Ejercicio)
        $cuentaUtilidad = Cuenta::where('codigo', 'like', '3605%')->first();
        if ($cuentaUtilidad && isset($cuentas[$cuentaUtilidad->id])) {
            $cuentas[$cuentaUtilidad->id]->balance += $utilidadEjercicio;
        } else {
            // Si no existe la cuenta en la DB, creamos una cuenta virtual para balancear el reporte
            $cuentaVirtual = new Cuenta();
            $cuentaVirtual->id = 999999;
            $cuentaVirtual->codigo = '360505';
            $cuentaVirtual->nombre = 'Utilidad o Pérdida del Ejercicio (Calculada)';
            $cuentaVirtual->tipo = 'Patrimonio';
            $cuentaVirtual->naturaleza = 'Credito';
            $cuentaVirtual->es_detalle = true;
            $cuentaVirtual->balance = $utilidadEjercicio;
            
            $padre3 = Cuenta::where('codigo', '3')->first();
            $cuentaVirtual->padre_id = $padre3 ? $padre3->id : null;
            $cuentas->put($cuentaVirtual->id, $cuentaVirtual);
        }

        // 6. Acumular saldos de abajo hacia arriba en la jerarquía (de códigos más largos a más cortos)
        $cuentasOrdenadas = $cuentas->sortByDesc(function ($c) {
            return strlen($c->codigo);
        });

        foreach ($cuentasOrdenadas as $c) {
            if ($c->padre_id && isset($cuentas[$c->padre_id])) {
                $cuentas[$c->padre_id]->balance += $c->balance;
            }
        }

        // 7. Filtrar cuentas de balance (Activo, Pasivo, Patrimonio) y descartar las de balance cero para limpieza visual
        $reporteCuentas = $cuentas->filter(function ($c) {
            return in_array($c->tipo, ['Activo', 'Pasivo', 'Patrimonio']) && abs($c->balance) >= 0.01;
        })->values();

        // Calcular totales globales por clase para verificar la ecuación patrimonial
        $totales = [
            'activo' => 0.00,
            'pasivo' => 0.00,
            'patrimonio' => 0.00
        ];

        foreach ($cuentas as $c) {
            if ($c->codigo === '1') {
                $totales['activo'] = $c->balance;
            } elseif ($c->codigo === '2') {
                $totales['pasivo'] = $c->balance;
            } elseif ($c->codigo === '3') {
                $totales['patrimonio'] = $c->balance;
            }
        }

        return response()->json([
            'fecha_fin' => $fechaFin,
            'cuentas' => $reporteCuentas,
            'totales' => $totales,
            'diferencia' => round($totales['activo'] - ($totales['pasivo'] + $totales['patrimonio']), 2),
            'utilidad_ejercicio' => $utilidadEjercicio
        ]);
    }

    /**
     * Reporte: Estado de Resultados (Income Statement / P&L)
     * Ingresos, Gastos, Costos entre fecha_inicio y fecha_fin
     */
    public function estadoResultados(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', date('Y-01-01'));
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));

        // 1. Obtener todas las cuentas del PUC
        $cuentas = Cuenta::orderBy('codigo', 'asc')->get()->keyBy('id');

        // Inicializar balances en 0
        foreach ($cuentas as $cuenta) {
            $cuenta->balance = 0.00;
        }

        // 2. Consultar sumas acumuladas de débitos y créditos en el período
        $balancesDetalle = DB::table('asientos_detalles')
            ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
            ->select(
                'asientos_detalles.cuenta_id',
                DB::raw('SUM(asientos_detalles.debe) as total_debe'),
                DB::raw('SUM(asientos_detalles.haber) as total_haber')
            )
            ->whereBetween('comprobantes_contables.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('asientos_detalles.cuenta_id')
            ->get();

        // 3. Asignar balances a cuentas de detalle de resultados según su tipo/naturaleza
        foreach ($balancesDetalle as $bal) {
            if (isset($cuentas[$bal->cuenta_id])) {
                $cuenta = $cuentas[$bal->cuenta_id];
                if (in_array($cuenta->tipo, ['Ingreso', 'Gasto', 'Costo'])) {
                    if ($cuenta->tipo === 'Ingreso') {
                        $cuenta->balance = (float)$bal->total_haber - (float)$bal->total_debe;
                    } else {
                        // Gastos y Costos
                        $cuenta->balance = (float)$bal->total_debe - (float)$bal->total_haber;
                    }
                }
            }
        }

        // 4. Acumular saldos de abajo hacia arriba en la jerarquía
        $cuentasOrdenadas = $cuentas->sortByDesc(function ($c) {
            return strlen($c->codigo);
        });

        foreach ($cuentasOrdenadas as $c) {
            if ($c->padre_id && isset($cuentas[$c->padre_id])) {
                $cuentas[$c->padre_id]->balance += $c->balance;
            }
        }

        // 5. Filtrar cuentas de resultados (Ingreso, Gasto, Costo) con balance distinto de cero
        $reporteCuentas = $cuentas->filter(function ($c) {
            return in_array($c->tipo, ['Ingreso', 'Gasto', 'Costo']) && abs($c->balance) >= 0.01;
        })->values();

        // Calcular sumatorias finales por clase
        $totales = [
            'ingresos' => 0.00,
            'costos' => 0.00,
            'gastos' => 0.00
        ];

        foreach ($cuentas as $c) {
            if ($c->codigo === '4') {
                $totales['ingresos'] = $c->balance;
            } elseif ($c->codigo === '6') {
                $totales['costos'] = $c->balance;
            } elseif ($c->codigo === '5') {
                $totales['gastos'] = $c->balance;
            }
        }

        $utilidadBruta = $totales['ingresos'] - $totales['costos'];
        $utilidadNeta = $utilidadBruta - $totales['gastos'];

        return response()->json([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cuentas' => $reporteCuentas,
            'totales' => $totales,
            'utilidad_bruta' => $utilidadBruta,
            'utilidad_neta' => $utilidadNeta
        ]);
    }

    /**
     * Reporte: Balanza de Comprobación (Trial Balance)
     * Saldo Anterior, Movimientos (Debe / Haber), y Nuevo Saldo para cuentas auxiliares
     */
    public function balanzaComprobacion(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', date('Y-m-01'));
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));

        // 1. Obtener cuentas de detalle (donde se asientan transacciones)
        $cuentas = Cuenta::where('es_detalle', true)->orderBy('codigo', 'asc')->get();

        $resultado = [];
        $totalesGenerales = [
            'saldo_anterior_debe' => 0.00,
            'saldo_anterior_haber' => 0.00,
            'debe_periodo' => 0.00,
            'haber_periodo' => 0.00,
            'saldo_final_debe' => 0.00,
            'saldo_final_haber' => 0.00
        ];

        foreach ($cuentas as $cuenta) {
            // A. Calcular movimientos anteriores (para Saldo Anterior)
            $prev = DB::table('asientos_detalles')
                ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
                ->select(
                    DB::raw('SUM(asientos_detalles.debe) as total_debe'),
                    DB::raw('SUM(asientos_detalles.haber) as total_haber')
                )
                ->where('asientos_detalles.cuenta_id', $cuenta->id)
                ->where('comprobantes_contables.fecha', '<', $fechaInicio)
                ->first();

            $prevDebeSum = $prev ? (float)$prev->total_debe : 0.00;
            $prevHaberSum = $prev ? (float)$prev->total_haber : 0.00;

            if ($cuenta->naturaleza === 'Debito') {
                $saldoAnterior = $prevDebeSum - $prevHaberSum;
            } else {
                $saldoAnterior = $prevHaberSum - $prevDebeSum;
            }

            // B. Calcular movimientos del período
            $curr = DB::table('asientos_detalles')
                ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
                ->select(
                    DB::raw('SUM(asientos_detalles.debe) as total_debe'),
                    DB::raw('SUM(asientos_detalles.haber) as total_haber')
                )
                ->where('asientos_detalles.cuenta_id', $cuenta->id)
                ->whereBetween('comprobantes_contables.fecha', [$fechaInicio, $fechaFin])
                ->first();

            $debePeriodo = $curr ? (float)$curr->total_debe : 0.00;
            $haberPeriodo = $curr ? (float)$curr->total_haber : 0.00;

            // C. Calcular saldo final
            if ($cuenta->naturaleza === 'Debito') {
                $saldoFinal = $saldoAnterior + $debePeriodo - $haberPeriodo;
            } else {
                $saldoFinal = $saldoAnterior + $haberPeriodo - $debePeriodo;
            }

            // Omitir si no tiene saldo anterior ni movimientos en el período
            if (abs($saldoAnterior) < 0.01 && $debePeriodo < 0.01 && $haberPeriodo < 0.01) {
                continue;
            }

            // D. Formatear en columnas débito y crédito
            $saldoAntDebe = 0.00;
            $saldoAntHaber = 0.00;
            if ($saldoAnterior > 0) {
                if ($cuenta->naturaleza === 'Debito') $saldoAntDebe = $saldoAnterior;
                else $saldoAntHaber = $saldoAnterior;
            } elseif ($saldoAnterior < 0) {
                if ($cuenta->naturaleza === 'Debito') $saldoAntHaber = abs($saldoAnterior);
                else $saldoAntDebe = abs($saldoAnterior);
            }

            $saldoFinDebe = 0.00;
            $saldoFinHaber = 0.00;
            if ($saldoFinal > 0) {
                if ($cuenta->naturaleza === 'Debito') $saldoFinDebe = $saldoFinal;
                else $saldoFinHaber = $saldoFinal;
            } elseif ($saldoFinal < 0) {
                if ($cuenta->naturaleza === 'Debito') $saldoFinHaber = abs($saldoFinal);
                else $saldoFinDebe = abs($saldoFinal);
            }

            // Sumar a totales generales
            $totalesGenerales['saldo_anterior_debe'] += $saldoAntDebe;
            $totalesGenerales['saldo_anterior_haber'] += $saldoAntHaber;
            $totalesGenerales['debe_periodo'] += $debePeriodo;
            $totalesGenerales['haber_periodo'] += $haberPeriodo;
            $totalesGenerales['saldo_final_debe'] += $saldoFinDebe;
            $totalesGenerales['saldo_final_haber'] += $saldoFinHaber;

            $resultado[] = [
                'id' => $cuenta->id,
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'naturaleza' => $cuenta->naturaleza,
                'saldo_anterior_debe' => $saldoAntDebe,
                'saldo_anterior_haber' => $saldoAntHaber,
                'debe_periodo' => $debePeriodo,
                'haber_periodo' => $haberPeriodo,
                'saldo_final_debe' => $saldoFinDebe,
                'saldo_final_haber' => $saldoFinHaber
            ];
        }

        return response()->json([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cuentas' => $resultado,
            'totales' => $totalesGenerales
        ]);
    }

    /**
     * Reporte: Auxiliar por Cuenta
     * Listado detallado cronológico de movimientos para una cuenta específica
     */
    public function auxiliarCuenta(Request $request)
    {
        $request->validate([
            'cuenta_id' => 'required|exists:cuentas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        $cuentaId = $request->cuenta_id;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $cuenta = Cuenta::findOrFail($cuentaId);

        // 1. Calcular Saldo Anterior (antes de fecha_inicio)
        $prev = DB::table('asientos_detalles')
            ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
            ->select(
                DB::raw('SUM(asientos_detalles.debe) as total_debe'),
                DB::raw('SUM(asientos_detalles.haber) as total_haber')
            )
            ->where('asientos_detalles.cuenta_id', $cuentaId)
            ->where('comprobantes_contables.fecha', '<', $fechaInicio)
            ->first();

        $prevDebe = $prev ? (float)$prev->total_debe : 0.00;
        $prevHaber = $prev ? (float)$prev->total_haber : 0.00;

        if ($cuenta->naturaleza === 'Debito') {
            $saldoAnterior = $prevDebe - $prevHaber;
        } else {
            $saldoAnterior = $prevHaber - $prevDebe;
        }

        // 2. Consultar movimientos del período ordenados cronológicamente
        $movements = DB::table('asientos_detalles')
            ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
            ->leftJoin('personas', 'asientos_detalles.tercero_id', '=', 'personas.id')
            ->select(
                'comprobantes_contables.fecha',
                'comprobantes_contables.tipo as comprobante_tipo',
                'comprobantes_contables.numero as comprobante_numero',
                'comprobantes_contables.descripcion as comprobante_descripcion',
                'personas.nombre as tercero_nombre',
                'asientos_detalles.debe',
                'asientos_detalles.haber',
                'asientos_detalles.referencia'
            )
            ->where('asientos_detalles.cuenta_id', $cuentaId)
            ->whereBetween('comprobantes_contables.fecha', [$fechaInicio, $fechaFin])
            ->orderBy('comprobantes_contables.fecha', 'asc')
            ->orderBy('comprobantes_contables.id', 'asc')
            ->orderBy('asientos_detalles.id', 'asc')
            ->get();

        // 3. Recorrer y calcular saldo corriente fila por fila
        $runningBalance = $saldoAnterior;
        $movementsWithBalance = [];
        $totalDebe = 0.00;
        $totalHaber = 0.00;

        foreach ($movements as $mov) {
            $debe = (float)$mov->debe;
            $haber = (float)$mov->haber;

            $totalDebe += $debe;
            $totalHaber += $haber;

            if ($cuenta->naturaleza === 'Debito') {
                $runningBalance += ($debe - $haber);
            } else {
                $runningBalance += ($haber - $debe);
            }

            $movementsWithBalance[] = [
                'fecha' => $mov->fecha,
                'comprobante_tipo' => $mov->comprobante_tipo,
                'comprobante_numero' => $mov->comprobante_numero,
                'comprobante_descripcion' => $mov->comprobante_descripcion,
                'tercero_nombre' => $mov->tercero_nombre ?: 'Sin Tercero',
                'debe' => $debe,
                'haber' => $haber,
                'referencia' => $mov->referencia,
                'saldo' => $runningBalance
            ];
        }

        return response()->json([
            'cuenta' => [
                'id' => $cuenta->id,
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'naturaleza' => $cuenta->naturaleza
            ],
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'saldo_anterior' => $saldoAnterior,
            'movimientos' => $movementsWithBalance,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'saldo_final' => $runningBalance
        ]);
    }

    /**
     * Reporte: Auxiliar de Saldos por Tercero (Exógena)
     * Agrupa saldos y movimientos por NIT/Cédula y Cuenta contable auxiliar
     */
    public function auxiliarTerceros(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', date('Y-01-01'));
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));
        $cuentaId = $request->input('cuenta_id');
        $terceroId = $request->input('tercero_id');

        // 1. Fetch details of all matching accounts and personas
        $queryDetails = DB::table('asientos_detalles')
            ->join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
            ->join('cuentas', 'asientos_detalles.cuenta_id', '=', 'cuentas.id')
            ->leftJoin('personas', 'asientos_detalles.tercero_id', '=', 'personas.id')
            ->select(
                'asientos_detalles.tercero_id',
                'personas.nombre as tercero_nombre',
                'personas.num_documento as tercero_documento',
                'personas.tipo_documento as tercero_tipo_doc',
                'asientos_detalles.cuenta_id',
                'cuentas.codigo as cuenta_codigo',
                'cuentas.nombre as cuenta_nombre',
                'cuentas.naturaleza as cuenta_naturaleza',
                // Sums before period
                DB::raw('SUM(CASE WHEN comprobantes_contables.fecha < ' . DB::getPdo()->quote($fechaInicio) . ' THEN asientos_detalles.debe ELSE 0 END) as prev_debe'),
                DB::raw('SUM(CASE WHEN comprobantes_contables.fecha < ' . DB::getPdo()->quote($fechaInicio) . ' THEN asientos_detalles.haber ELSE 0 END) as prev_haber'),
                // Sums inside period
                DB::raw('SUM(CASE WHEN comprobantes_contables.fecha BETWEEN ' . DB::getPdo()->quote($fechaInicio) . ' AND ' . DB::getPdo()->quote($fechaFin) . ' THEN asientos_detalles.debe ELSE 0 END) as debe_periodo'),
                DB::raw('SUM(CASE WHEN comprobantes_contables.fecha BETWEEN ' . DB::getPdo()->quote($fechaInicio) . ' AND ' . DB::getPdo()->quote($fechaFin) . ' THEN asientos_detalles.haber ELSE 0 END) as haber_periodo')
            );

        if (!empty($cuentaId)) {
            $queryDetails->where('asientos_detalles.cuenta_id', $cuentaId);
        }

        if (!empty($terceroId)) {
            $queryDetails->where('asientos_detalles.tercero_id', $terceroId);
        }

        $records = $queryDetails->groupBy(
            'asientos_detalles.tercero_id',
            'personas.nombre',
            'personas.num_documento',
            'personas.tipo_documento',
            'asientos_detalles.cuenta_id',
            'cuentas.codigo',
            'cuentas.nombre',
            'cuentas.naturaleza'
        )
        ->orderBy('cuenta_codigo', 'asc')
        ->orderBy('tercero_nombre', 'asc')
        ->get();

        $resultado = [];

        foreach ($records as $rec) {
            $prevDebe = (float)$rec->prev_debe;
            $prevHaber = (float)$rec->prev_haber;
            $debePeriodo = (float)$rec->debe_periodo;
            $haberPeriodo = (float)$rec->haber_periodo;

            if ($rec->cuenta_naturaleza === 'Debito') {
                $saldoAnterior = $prevDebe - $prevHaber;
                $saldoFinal = $saldoAnterior + $debePeriodo - $haberPeriodo;
            } else {
                $saldoAnterior = $prevHaber - $prevDebe;
                $saldoFinal = $saldoAnterior + $haberPeriodo - $debePeriodo;
            }

            // Omit rows that have absolutely no balances or activity
            if (abs($saldoAnterior) < 0.01 && $debePeriodo < 0.01 && $haberPeriodo < 0.01 && abs($saldoFinal) < 0.01) {
                continue;
            }

            $resultado[] = [
                'tercero_id' => $rec->tercero_id,
                'tercero_nombre' => $rec->tercero_nombre ?: 'Sin Tercero / Varios',
                'tercero_documento' => $rec->tercero_documento ?: '00000000',
                'tercero_tipo_doc' => $rec->tercero_tipo_doc ?: 'CC',
                'cuenta_id' => $rec->cuenta_id,
                'cuenta_codigo' => $rec->cuenta_codigo,
                'cuenta_nombre' => $rec->cuenta_nombre,
                'saldo_anterior' => $saldoAnterior,
                'debe' => $debePeriodo,
                'haber' => $haberPeriodo,
                'saldo_final' => $saldoFinal
            ];
        }

        return response()->json([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'reporte' => $resultado
        ]);
    }
}
