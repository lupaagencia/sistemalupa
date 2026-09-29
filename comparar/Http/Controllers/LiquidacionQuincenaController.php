<?php

namespace App\Http\Controllers;

use App\LiquidacionQuincena;
use App\Empleado;
use App\Egresos;
use App\CajaMenorMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LiquidacionQuincenaController extends Controller
{
    /**
     * Display a listing of quincenas.
     */
    public function index(Request $request)
    {
        $empleado_id = $request->input('empleado_id');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $perPage = $request->input('per_page', 15);

        $query = LiquidacionQuincena::with(['empleado', 'egreso']);

        if (!empty($empleado_id)) {
            $query->where('empleado_id', $empleado_id);
        }

        if (!empty($fecha_desde) && !empty($fecha_hasta)) {
            $query->whereBetween('fecha_pago', [$fecha_desde, $fecha_hasta]);
        }

        $quincenas = $query->orderBy('fecha_pago', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'pagination' => [
                'total'        => $quincenas->total(),
                'current_page' => $quincenas->currentPage(),
                'per_page'     => $quincenas->perPage(),
                'last_page'    => $quincenas->lastPage(),
                'from'         => $quincenas->firstItem(),
                'to'           => $quincenas->lastItem(),
            ],
            'quincenas' => $quincenas
        ]);
    }

    /**
     * Store a newly created quincena and create the corresponding Egreso.
     */
    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|integer|exists:empleados,id',
            'fecha_pago' => 'required|date',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'dias_trabajados' => 'required|integer|min:1',
            'salario_base' => 'required|numeric|min:0',
            'sueldo_neto' => 'required|numeric|min:0',
            'auxilio_transporte' => 'required|numeric|min:0',
            'monto_extras' => 'required|numeric|min:0',
            'salud_deduccion' => 'required|numeric|min:0',
            'pension_deduccion' => 'required|numeric|min:0',
            'otras_deducciones' => 'required|numeric|min:0',
            'neto_pagado' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string|max:50',
            'horas_extras_json' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $empleado = Empleado::findOrFail($request->empleado_id);

            // 1. Create Egreso entry
            $egreso = new Egresos();
            $egreso->tipo_egreso = 'Nómina';
            $egreso->concepto = "Pago quincena {$request->fecha_inicio} al {$request->fecha_fin} - {$empleado->nombre} {$empleado->apellido}";
            $egreso->valor = $request->neto_pagado;
            $egreso->fecha = $request->fecha_pago;
            $egreso->metodo_pago = $request->metodo_pago;
            $egreso->beneficiario = "{$empleado->nombre} {$empleado->apellido}";
            $egreso->user_id = Auth::id() ?: 1;

            // Handle Caja Menor flow if paid via Caja Menor
            if ($egreso->metodo_pago === 'Caja Menor') {
                $ultimoMov = CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
                $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;

                if ($saldoActual < (float)$egreso->valor) {
                    return response()->json([
                        'error' => 'Saldo insuficiente en Caja Menor (Saldo actual: $' . number_format($saldoActual, 2) . ')'
                    ], 422);
                }

                $egreso->save();

                $cajaMov = new CajaMenorMovimiento();
                $cajaMov->tipo = 'Egreso';
                $cajaMov->monto = $egreso->valor;
                $cajaMov->saldo_resultante = $saldoActual - (float)$egreso->valor;
                $cajaMov->fecha = $egreso->fecha;
                $cajaMov->descripcion = $egreso->concepto;
                $cajaMov->egreso_id = $egreso->id;
                $cajaMov->user_id = $egreso->user_id;
                $cajaMov->save();
            } else {
                $egreso->save();
            }

            // 2. Save Quincena entry
            $quincena = new LiquidacionQuincena();
            $quincena->empleado_id = $request->empleado_id;
            $quincena->fecha_pago = $request->fecha_pago;
            $quincena->fecha_inicio = $request->fecha_inicio;
            $quincena->fecha_fin = $request->fecha_fin;
            $quincena->dias_trabajados = $request->dias_trabajados;
            $quincena->salario_base = $request->salario_base;
            $quincena->sueldo_neto = $request->sueldo_neto;
            $quincena->auxilio_transporte = $request->auxilio_transporte;
            $quincena->horas_extras_json = $request->horas_extras_json;
            $quincena->monto_extras = $request->monto_extras;
            $quincena->salud_deduccion = $request->salud_deduccion;
            $quincena->pension_deduccion = $request->pension_deduccion;
            $quincena->otras_deducciones = $request->otras_deducciones;
            $quincena->neto_pagado = $request->neto_pagado;
            $quincena->egreso_id = $egreso->id;
            $quincena->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quincena y egreso registrados correctamente.',
                'quincena' => $quincena
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al guardar la quincena: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a quincena and its associated egreso.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $quincena = LiquidacionQuincena::findOrFail($id);

            if ($quincena->egreso_id) {
                $egreso = Egresos::find($quincena->egreso_id);
                if ($egreso) {
                    // Delete associated support file if exists
                    if (!empty($egreso->soporte)) {
                        $file_path = public_path('uploads/egresos/' . $egreso->soporte);
                        if (\Illuminate\Support\Facades\File::exists($file_path)) {
                            \Illuminate\Support\Facades\File::delete($file_path);
                        }
                    }
                    $egreso->delete();
                    $this->recalculateCajaMenorBalances();
                }
            }

            $quincena->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Liquidación de quincena eliminada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar la quincena: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate average overtime and base salary for definitive contract liquidation.
     */
    public function getPromedioExtras(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|integer',
            'fecha_desde_prima' => 'required|date',
            'fecha_desde_cesantias' => 'required|date',
            'fecha_desde_vacaciones' => 'required|date',
            'fecha_retiro' => 'required|date'
        ]);

        $empId = $request->empleado_id;
        $retiro = $request->fecha_retiro;

        // Averages calculation helper
        $calcAvg = function($desde, $hasta) use ($empId) {
            $dias = $this->calcularDiasComerciales($desde, $hasta);
            if ($dias <= 0) return 0;

            // Sum up all overtime paid in this range
            $sumaExtras = (float)LiquidacionQuincena::where('empleado_id', $empId)
                ->whereBetween('fecha_pago', [$desde, $hasta])
                ->sum('monto_extras');

            // Calculate monthly average: (suma / dias_totales) * 30
            return ($sumaExtras / $dias) * 30;
        };

        // Base salary average helper (for vacations)
        $calcAvgSueldo = function($desde, $hasta) use ($empId) {
            $dias = $this->calcularDiasComerciales($desde, $hasta);
            if ($dias <= 0) return 0;

            // Sum up basic salary paid (sueldo_neto) in this range
            $sumaBase = (float)LiquidacionQuincena::where('empleado_id', $empId)
                ->whereBetween('fecha_pago', [$desde, $hasta])
                ->sum('sueldo_neto');

            // Calculate monthly average: (suma / dias_totales) * 30
            return ($sumaBase / $dias) * 30;
        };

        $promedioExtrasPrima = $calcAvg($request->fecha_desde_prima, $retiro);
        $promedioExtrasCesantias = $calcAvg($request->fecha_desde_cesantias, $retiro);
        $promedioExtrasVacaciones = $calcAvg($request->fecha_desde_vacaciones, $retiro);
        $salarioBasePromedio = $calcAvgSueldo($request->fecha_desde_vacaciones, $retiro);

        // Fetch employee current base salary as fallback if no quincenas exist
        $emp = Empleado::find($empId);
        $salarioActual = $emp ? (float)$emp->salario : 0.0;

        return response()->json([
            'promedio_extras_prima' => round($promedioExtrasPrima, 2),
            'promedio_extras_cesantias' => round($promedioExtrasCesantias, 2),
            'promedio_extras_vacaciones' => round($promedioExtrasVacaciones, 2),
            'salario_base_promedio' => $salarioBasePromedio > 0 ? round($salarioBasePromedio, 2) : $salarioActual,
            'tiene_registros' => LiquidacionQuincena::where('empleado_id', $empId)->exists()
        ]);
    }

    /**
     * Get monthly consolidated social security report.
     */
    public function getReporteSeguridadSocial(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'mes' => 'required|integer|between:1,12'
        ]);

        $anio = $request->anio;
        $mes = str_pad($request->mes, 2, '0', STR_PAD_LEFT);
        $startDate = "{$anio}-{$mes}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get quincenas grouped by employee
        $quincenas = LiquidacionQuincena::whereBetween('fecha_pago', [$startDate, $endDate])
            ->get();

        $reporte = [];
        $grupos = $quincenas->groupBy('empleado_id');

        foreach ($grupos as $empId => $empQuincenas) {
            $emp = Empleado::find($empId);
            if (!$emp) continue;

            $diasTrabajados = $empQuincenas->sum('dias_trabajados');
            $sueldoTotal = $empQuincenas->sum('sueldo_neto');
            $extrasTotal = $empQuincenas->sum('monto_extras');
            
            // IBC (Ingreso Base de Cotización) is regular sueldo + overtime value
            $ibc = $sueldoTotal + $extrasTotal;

            // Standard Colombian Social Security rules:
            // Salud: Employee 4%, Employer 8.5%, Total 12.5%
            $saludEmpleado = $ibc * 0.04;
            $saludEmpleador = $ibc * 0.085;
            $saludTotal = $ibc * 0.125;

            // Pensión: Employee 4%, Employer 12%, Total 16%
            $pensionEmpleado = $ibc * 0.04;
            $pensionEmpleador = $ibc * 0.12;
            $pensionTotal = $ibc * 0.16;

            // ARL: Employer (Standard Risk 1: 0.522%)
            $arl = $ibc * 0.00522;

            $reporte[] = [
                'empleado_id' => $empId,
                'nombre' => "{$emp->nombre} {$emp->apellido}",
                'identificacion' => $emp->num_doc,
                'dias_trabajados' => $diasTrabajados,
                'sueldo_neto' => round($sueldoTotal, 2),
                'monto_extras' => round($extrasTotal, 2),
                'ibc' => round($ibc, 2),
                'salud' => [
                    'empleado' => round($saludEmpleado, 2),
                    'empleador' => round($saludEmpleador, 2),
                    'total' => round($saludTotal, 2),
                ],
                'pension' => [
                    'empleado' => round($pensionEmpleado, 2),
                    'empleador' => round($pensionEmpleador, 2),
                    'total' => round($pensionTotal, 2),
                ],
                'arl' => round($arl, 2),
                'total_seguridad_social' => round($saludTotal + $pensionTotal + $arl, 2)
            ];
        }

        return response()->json($reporte);
    }

    /**
     * Recalculates Caja Menor balances.
     */
    private function recalculateCajaMenorBalances()
    {
        $movimientos = CajaMenorMovimiento::orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();
        $saldo = 0.0;
        foreach ($movimientos as $mov) {
            if ($mov->tipo === 'Ingreso') {
                $saldo += (float)$mov->monto;
            } else {
                $saldo -= (float)$mov->monto;
            }
            $mov->saldo_resultante = $saldo;
            $mov->save();
        }
    }

    /**
     * Helpers commercial days calculation
     */
    private function calcularDiasComerciales($fechaIni, $fechaFin) {
        if (!$fechaIni || !$fechaFin) return 0;
        $start = new \DateTime($fechaIni);
        $end = new \DateTime($fechaFin);
        if ($end < $start) return 0;

        $y1 = (int)$start->format('Y');
        $m1 = (int)$start->format('m');
        $d1 = (int)$start->format('d');

        $y2 = (int)$end->format('Y');
        $m2 = (int)$end->format('m');
        $d2 = (int)$end->format('d');

        if ($d1 === 31) $d1 = 30;
        if ($d2 === 31) $d2 = 30;

        // Feb adjustments
        $isEndFebLast = ($m2 === 2 && (($y2 % 4 === 0 && $d2 === 29) || ($y2 % 4 !== 0 && $d2 === 28)));
        if ($isEndFebLast) $d2 = 30;
        $isStartFebLast = ($m1 === 2 && (($y1 % 4 === 0 && $d1 === 29) || ($y1 % 4 !== 0 && $d1 === 28)));
        if ($isStartFebLast) $d1 = 30;

        $days = ($y2 - $y1) * 360 + ($m2 - m1) * 30 + ($d2 - $d1);
        return $days + 1;
    }
}
