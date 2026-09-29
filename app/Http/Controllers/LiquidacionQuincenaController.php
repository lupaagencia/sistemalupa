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
     * Store a newly created quincena (Liquidada y Guardada, pendiente de pago).
     */
    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|integer|exists:empleados,id',
            'fecha_pago' => 'required|date',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'dias_trabajados' => 'required|numeric|min:0.01',
            'salario_base' => 'required|numeric|min:0',
            'sueldo_neto' => 'required|numeric|min:0',
            'auxilio_transporte' => 'required|numeric|min:0',
            'monto_extras' => 'required|numeric|min:0',
            'salud_deduccion' => 'required|numeric|min:0',
            'pension_deduccion' => 'required|numeric|min:0',
            'otras_deducciones' => 'required|numeric|min:0',
            'neto_pagado' => 'required|numeric|min:0',
            'metodo_pago' => 'nullable|string|max:50',
            'horas_extras_json' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $empleado = Empleado::findOrFail($request->empleado_id);

            if ($empleado->tipo_contrato === 'Prestación de servicios') {
                $request->merge([
                    'salud_deduccion' => 0.00,
                    'pension_deduccion' => 0.00,
                    'auxilio_transporte' => 0.00,
                    'monto_extras' => 0.00
                ]);
            }

            // Save Quincena entry as Pendiente (without generating Egreso or accounting voucher yet)
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
            $quincena->egreso_id = null;
            $quincena->estado = 'Pendiente';
            $quincena->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quincena liquidada y guardada correctamente (Pendiente de pago).',
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
     * Mark a pending quincena as Paid and create corresponding Egreso and accounting entries.
     */
    public function pagar(Request $request, $id)
    {
        $request->validate([
            'fecha_pago' => 'nullable|date',
            'metodo_pago' => 'required|string|max:50'
        ]);

        try {
            DB::beginTransaction();

            $quincena = LiquidacionQuincena::with('empleado')->findOrFail($id);

            if ($quincena->estado === 'Pagada' || $quincena->egreso_id !== null) {
                return response()->json([
                    'error' => 'Esta quincena ya se encuentra marcada como pagada.'
                ], 422);
            }

            $empleado = $quincena->empleado;
            if (!$empleado) {
                return response()->json([
                    'error' => 'Empleado no encontrado para esta quincena.'
                ], 422);
            }

            $fechaPago = $request->fecha_pago ?: ($quincena->fecha_pago ?: date('Y-m-d'));
            $metodoPago = $request->metodo_pago ?: 'Banco';

            // 1. Create Egreso entry
            $egreso = new Egresos();
            $egreso->tipo_egreso = 'Nómina';
            $egreso->concepto = "Pago quincena {$quincena->fecha_inicio} al {$quincena->fecha_fin} - {$empleado->nombre} {$empleado->apellido}";
            $egreso->valor = $quincena->neto_pagado;
            $egreso->fecha = $fechaPago;
            $egreso->metodo_pago = $metodoPago;
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

            // 2. Link Egreso and set Status to Pagada
            $quincena->fecha_pago = $fechaPago;
            $quincena->egreso_id = $egreso->id;
            $quincena->estado = 'Pagada';
            $quincena->save();

            // 3. Accounting voucher generation
            $this->contabilizarNomina($quincena);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quincena pagada y contabilizada correctamente.',
                'quincena' => $quincena
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al procesar el pago de la quincena: ' . $e->getMessage()
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
     * Update an existing quincena and its corresponding Egreso.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha_pago' => 'required|date',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'dias_trabajados' => 'required|numeric|min:0.01',
            'salario_base' => 'required|numeric|min:0',
            'sueldo_neto' => 'required|numeric|min:0',
            'auxilio_transporte' => 'required|numeric|min:0',
            'monto_extras' => 'required|numeric|min:0',
            'salud_deduccion' => 'required|numeric|min:0',
            'pension_deduccion' => 'required|numeric|min:0',
            'otras_deducciones' => 'required|numeric|min:0',
            'neto_pagado' => 'required|numeric|min:0',
            'horas_extras_json' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $quincena = LiquidacionQuincena::findOrFail($id);
            $empleado = Empleado::findOrFail($quincena->empleado_id);

            if ($empleado->tipo_contrato === 'Prestación de servicios') {
                $request->merge([
                    'salud_deduccion' => 0.00,
                    'pension_deduccion' => 0.00,
                    'auxilio_transporte' => 0.00,
                    'monto_extras' => 0.00
                ]);
            }

            // Calculate difference if paid via Caja Menor
            $egreso = null;
            if ($quincena->egreso_id) {
                $egreso = Egresos::find($quincena->egreso_id);
            }

            if ($egreso) {
                $diferencia = (float)$request->neto_pagado - (float)$egreso->valor;

                if ($egreso->metodo_pago === 'Caja Menor') {
                    // Check if there's enough balance for the difference
                    $ultimoMov = CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
                    $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;

                    // If we are increasing the payout, check petty cash
                    if ($diferencia > 0 && $saldoActual < $diferencia) {
                        return response()->json([
                            'error' => 'Saldo insuficiente en Caja Menor para cubrir la diferencia (Saldo actual: $' . number_format($saldoActual, 2) . ')'
                        ], 422);
                    }

                    // Update corresponding CajaMenorMovimiento
                    $cajaMov = CajaMenorMovimiento::where('egreso_id', $egreso->id)->first();
                    if ($cajaMov) {
                        $cajaMov->monto = $request->neto_pagado;
                        $cajaMov->fecha = $request->fecha_pago;
                        $cajaMov->descripcion = "Pago quincena {$request->fecha_inicio} al {$request->fecha_fin} - {$empleado->nombre} {$empleado->apellido}";
                        $cajaMov->save();
                    }
                }

                // Update Egreso details
                $egreso->concepto = "Pago quincena {$request->fecha_inicio} al {$request->fecha_fin} - {$empleado->nombre} {$empleado->apellido}";
                $egreso->valor = $request->neto_pagado;
                $egreso->fecha = $request->fecha_pago;
                $egreso->save();

                if ($egreso->metodo_pago === 'Caja Menor') {
                    $this->recalculateCajaMenorBalances();
                }
            }

            // Update Quincena record
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
            $quincena->save();
            if ($quincena->egreso_id) {
                $this->contabilizarNomina($quincena);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Liquidación de quincena actualizada correctamente.',
                'quincena' => $quincena
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar la quincena: ' . $e->getMessage()
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
        $salarioActual = $emp ? ((float)$emp->salario - (float)($emp->auxilio_transporte ?? 0)) : 0.0;

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

        // Get quincenas grouped by employee (excluding contractors under Prestación de servicios)
        $quincenas = LiquidacionQuincena::whereBetween('fecha_pago', [$startDate, $endDate])
            ->whereHas('empleado', function($q) {
                $q->where('tipo_contrato', '!=', 'Prestación de servicios');
            })
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

        $days = ($y2 - $y1) * 360 + ($m2 - $m1) * 30 + ($d2 - $d1);
        return $days + 1;
    }

    private function contabilizarNomina($quincena)
    {
        $egreso = \App\Egresos::find($quincena->egreso_id);
        if (!$egreso) return;

        $comp = $egreso->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Egreso')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Egreso';
            $comp->numero = $ultimoNumero + 1;
        }

        $comp->fecha = $quincena->fecha_pago;
        $comp->descripcion = "Contabilización Nómina - Quincena: " . $quincena->fecha_inicio . " al " . $quincena->fecha_fin . " - Empleado: " . ($quincena->empleado ? ($quincena->empleado->nombre . ' ' . $quincena->empleado->apellido) : 'Desconocido');
        $comp->user_id = \Illuminate\Support\Facades\Auth::id() ?: $egreso->user_id;
        $comp->save();

        $egreso->comprobante_id = $comp->id;
        $egreso->saveQuietly();

        $comp->detalles()->delete();

        $terceroId = null;
        if ($quincena->empleado) {
            $persona = \App\Persona::firstOrCreate(
                ['nombre' => $quincena->empleado->nombre . ' ' . $quincena->empleado->apellido],
                ['tipo_documento' => 'CC', 'num_documento' => $quincena->empleado->num_doc ?: '00000000']
            );
            $terceroId = $persona->id;
        }

        $esContratista = ($quincena->empleado && $quincena->empleado->tipo_contrato === 'Prestación de servicios');

        if ($esContratista) {
            $cuentaSueldo = \App\Cuenta::where('codigo', '513595')->first();
            if (!$cuentaSueldo) {
                $parent = \App\Cuenta::where('codigo', '5135')->first();
                $cuentaSueldo = \App\Cuenta::create([
                    'codigo' => '513595',
                    'nombre' => 'Contratos de Servicios / Honorarios',
                    'tipo' => 'Gasto',
                    'naturaleza' => 'Debito',
                    'es_detalle' => 1,
                    'padre_id' => $parent ? $parent->id : null
                ]);
            }
        } else {
            $cuentaSueldo = \App\Cuenta::where('codigo', '510506')->first();
        }

        if ($cuentaSueldo && $quincena->sueldo_neto > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaSueldo->id,
                'tercero_id' => $terceroId,
                'debe' => $quincena->sueldo_neto,
                'haber' => 0.00,
                'referencia' => $esContratista ? 'Honorarios/Servicios Q' : 'Sueldo Básico Q'
            ]);
        }

        $cuentaAuxTrans = \App\Cuenta::where('codigo', '510527')->first();
        if ($cuentaAuxTrans && $quincena->auxilio_transporte > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaAuxTrans->id,
                'tercero_id' => $terceroId,
                'debe' => $quincena->auxilio_transporte,
                'haber' => 0.00,
                'referencia' => 'Auxilio de Transporte'
            ]);
        }

        $cuentaExtras = \App\Cuenta::where('codigo', '510515')->first();
        if (!$cuentaExtras) {
            $parent = \App\Cuenta::where('codigo', '5105')->first();
            $cuentaExtras = \App\Cuenta::create([
                'codigo' => '510515',
                'nombre' => 'Horas Extras y Recargos',
                'tipo' => 'Gasto',
                'naturaleza' => 'Débito',
                'es_detalle' => 1,
                'padre_id' => $parent ? $parent->id : null
            ]);
        }
        if ($cuentaExtras && $quincena->monto_extras > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaExtras->id,
                'tercero_id' => $terceroId,
                'debe' => $quincena->monto_extras,
                'haber' => 0.00,
                'referencia' => 'Horas Extras y Recargos'
            ]);
        }

        $cuentaSalud = \App\Cuenta::where('codigo', '237005')->first();
        if (!$cuentaSalud) {
            $cuentaSalud = \App\Cuenta::create([
                'codigo' => '237005',
                'nombre' => 'Aportes a Salud (Deducción Empleado)',
                'tipo' => 'Pasivo',
                'naturaleza' => 'Crédito',
                'es_detalle' => 1
            ]);
        }
        if ($cuentaSalud && $quincena->salud_deduccion > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaSalud->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $quincena->salud_deduccion,
                'referencia' => 'Deducción Salud 4%'
            ]);
        }

        $cuentaPension = \App\Cuenta::where('codigo', '238030')->first();
        if (!$cuentaPension) {
            $cuentaPension = \App\Cuenta::create([
                'codigo' => '238030',
                'nombre' => 'Aportes a Pensión (Deducción Empleado)',
                'tipo' => 'Pasivo',
                'naturaleza' => 'Crédito',
                'es_detalle' => 1
            ]);
        }
        if ($cuentaPension && $quincena->pension_deduccion > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaPension->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $quincena->pension_deduccion,
                'referencia' => 'Deducción Pensión 4%'
            ]);
        }

        $cuentaOtrasDed = \App\Cuenta::where('codigo', '233595')->first();
        if (!$cuentaOtrasDed) {
            $cuentaOtrasDed = \App\Cuenta::create([
                'codigo' => '233595',
                'nombre' => 'Otras Deducciones de Nómina',
                'tipo' => 'Pasivo',
                'naturaleza' => 'Crédito',
                'es_detalle' => 1
            ]);
        }
        if ($cuentaOtrasDed && $quincena->otras_deducciones > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaOtrasDed->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $quincena->otras_deducciones,
                'referencia' => 'Otras Deducciones'
            ]);
        }

        if ($egreso->metodo_pago === 'Caja Menor') {
            $cuentaCredito = \App\Cuenta::where('codigo', '110510')->first();
        } else {
            $cuentaCredito = \App\Cuenta::where('codigo', '111005')->first();
        }

        if ($cuentaCredito && $quincena->neto_pagado > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCredito->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $quincena->neto_pagado,
                'referencia' => 'Neto Pagado Nómina'
            ]);
        }
    }

    /**
     * Generate and download pay stub PDF for a given quincena.
     */
    public function descargarPDFQuincena($id)
    {
        $quincena = LiquidacionQuincena::with(['empleado', 'egreso'])->findOrFail($id);

        $extras = [
            'horasHED' => 0,
            'horasHEN' => 0,
            'horasHEDD' => 0,
            'horasHEND' => 0,
            'horasRNO' => 0,
            'horasRDD' => 0,
            'horasRND' => 0
        ];
        if ($quincena->horas_extras_json) {
            try {
                $parsed = json_decode($quincena->horas_extras_json, true);
                if (is_array($parsed)) {
                    $extras = array_merge($extras, $parsed);
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }

        $valorHoraOrdinaria = $quincena->salario_base / 240;

        $configHoras = [
            'factorHED' => 1.25,
            'factorHEN' => 1.75,
            'factorHEDD' => 2.00,
            'factorHEND' => 2.50,
            'factorRNO' => 0.35,
            'factorRDD' => 0.75,
            'factorRND' => 1.10
        ];

        $montos = [
            'montoHED' => $extras['horasHED'] * $valorHoraOrdinaria * $configHoras['factorHED'],
            'montoHEN' => $extras['horasHEN'] * $valorHoraOrdinaria * $configHoras['factorHEN'],
            'montoHEDD' => $extras['horasHEDD'] * $valorHoraOrdinaria * $configHoras['factorHEDD'],
            'montoHEND' => $extras['horasHEND'] * $valorHoraOrdinaria * $configHoras['factorHEND'],
            'montoRNO' => $extras['horasRNO'] * $valorHoraOrdinaria * $configHoras['factorRNO'],
            'montoRDD' => $extras['horasRDD'] * $valorHoraOrdinaria * $configHoras['factorRDD'],
            'montoRND' => $extras['horasRND'] * $valorHoraOrdinaria * $configHoras['factorRND']
        ];

        $totalHorasExtras = array_sum($montos);

        // Convert total to letters
        $letrasObj = new \App\CifrasEnLetras();
        $numeroLetras = $letrasObj->convertirEurosEnLetras(number_format($quincena->neto_pagado, 0)) . ' M/CTE';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.desprendible_quincena', compact('quincena', 'extras', 'valorHoraOrdinaria', 'configHoras', 'montos', 'totalHorasExtras', 'numeroLetras'));
        return $pdf->stream('desprendible_quincena_' . str_pad($quincena->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}
