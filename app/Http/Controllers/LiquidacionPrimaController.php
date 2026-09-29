<?php

namespace App\Http\Controllers;

use App\LiquidacionPrima;
use App\Empleado;
use App\Egresos;
use App\CajaMenorMovimiento;
use App\LiquidacionQuincena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LiquidacionPrimaController extends Controller
{
    /**
     * Display a listing of paid primas.
     */
    public function index(Request $request)
    {
        $empleado_id = $request->input('empleado_id');
        $anio = $request->input('anio');
        $periodo = $request->input('periodo');
        $perPage = $request->input('per_page', 15);

        $query = LiquidacionPrima::with(['empleado', 'egreso']);

        if (!empty($empleado_id)) {
            $query->where('empleado_id', $empleado_id);
        }

        if (!empty($anio)) {
            $query->where('anio', $anio);
        }

        if (!empty($periodo)) {
            $query->where('periodo', $periodo);
        }

        $primas = $query->orderBy('fecha_pago', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'pagination' => [
                'total'        => $primas->total(),
                'current_page' => $primas->currentPage(),
                'per_page'     => $primas->perPage(),
                'last_page'    => $primas->lastPage(),
                'from'         => $primas->firstItem(),
                'to'           => $primas->lastItem(),
            ],
            'primas' => $primas
        ]);
    }

    /**
     * Calculate simulated primas for all employees in a given semester.
     */
    public function calcularPrimas(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'periodo' => 'required|integer|in:1,2'
        ]);

        $anio = $request->anio;
        $periodo = $request->periodo;

        if ($periodo == 1) {
            $semestreInicio = "{$anio}-01-01";
            $semestreFin = "{$anio}-06-30";
        } else {
            $semestreInicio = "{$anio}-07-01";
            $semestreFin = "{$anio}-12-30"; // Using standard 30-day month logic, capped at 180 days
        }

        // Get employees with labor contracts - excluding "Prestación de servicios"
        $empleados = Empleado::where('tipo_contrato', '!=', 'Prestación de servicios')
            ->where(function($query) use ($semestreFin) {
                $query->where('fecha_ingreso', '<=', $semestreFin)
                      ->orWhereNull('fecha_ingreso');
            })
            ->where(function($query) use ($semestreInicio) {
                $query->where('fecha_finalizacion', '>=', $semestreInicio)
                      ->orWhereNull('fecha_finalizacion');
            })
            ->get();

        $resultados = [];
        $salarioMinimo = \App\Ajustes::getSalarioMinimo(1400000);
        $auxilioTransporteDef = \App\Ajustes::getAuxilioTransporte(200000);

        foreach ($empleados as $emp) {
            // Determine intersection of semester dates with employee contract duration
            $fechaIngreso = $emp->fecha_ingreso ?: $semestreInicio;
            $fechaRetiro = $emp->fecha_finalizacion;

            $startInter = max(strtotime($fechaIngreso), strtotime($semestreInicio));
            $endInter = $fechaRetiro ? min(strtotime($fechaRetiro), strtotime($semestreFin)) : strtotime($semestreFin);

            if ($startInter > $endInter) {
                continue;
            }

            $fechaIniStr = date('Y-m-d', $startInter);
            $fechaFinStr = date('Y-m-d', $endInter);

            $diasTrabajados = $this->calcularDiasComerciales($fechaIniStr, $fechaFinStr);
            if ($diasTrabajados > 180) {
                $diasTrabajados = 180;
            }

            if ($diasTrabajados <= 0) {
                continue;
            }

            // Check if already paid
            $primaExistente = LiquidacionPrima::where('empleado_id', $emp->id)
                ->where('anio', $anio)
                ->where('periodo', $periodo)
                ->first();
            $yaPagado = $primaExistente ? true : false;
            $liquidacionPrimaId = $primaExistente ? $primaExistente->id : null;

            // Calculate overtime average during this period
            $sumaExtras = (float)LiquidacionQuincena::where('empleado_id', $emp->id)
                ->whereBetween('fecha_pago', [$semestreInicio, $semestreFin])
                ->sum('monto_extras');

            // Overtime monthly average
            $promedioExtras = ($sumaExtras / $diasTrabajados) * 30;

            // Base salary (excluding transport if stored separately)
            $salarioBase = (float)$emp->salario - (float)($emp->auxilio_transporte ?? 0);
            
            // Check if transport allowance applies
            $aplicaAuxTrans = false;
            if ($emp->auxilio_transporte > 0) {
                $aplicaAuxTrans = true;
            } else {
                $aplicaAuxTrans = $salarioBase <= ($salarioMinimo * 2);
            }

            $auxTransValue = $aplicaAuxTrans ? (float)($emp->auxilio_transporte ?: $auxilioTransporteDef) : 0.0;

            $baseCalculo = $salarioBase + $auxTransValue + $promedioExtras;

            $valorPrima = ($baseCalculo * $diasTrabajados) / 360;

            $resultados[] = [
                'empleado_id' => $emp->id,
                'nombre' => "{$emp->nombre} {$emp->apellido}",
                'num_doc' => $emp->num_doc,
                'tipo_contrato' => $emp->tipo_contrato,
                'fecha_ingreso' => $emp->fecha_ingreso,
                'fecha_finalizacion' => $emp->fecha_finalizacion,
                'dias_trabajados' => $diasTrabajados,
                'salario_base' => round($salarioBase, 2),
                'auxilio_transporte' => round($auxTransValue, 2),
                'promedio_extras' => round($promedioExtras, 2),
                'base_calculo' => round($baseCalculo, 2),
                'valor_prima' => round($valorPrima, 2),
                'ya_pagado' => $yaPagado,
                'liquidacion_prima_id' => $liquidacionPrimaId
            ];
        }

        return response()->json($resultados);
    }

    /**
     * Store premium payments and register related egresos and accounting.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|string|max:50',
            'anio' => 'required|integer',
            'periodo' => 'required|integer|in:1,2',
            'pagos' => 'required|array',
            'pagos.*.empleado_id' => 'required|integer|exists:empleados,id',
            'pagos.*.dias_trabajados' => 'required|numeric|min:0.01',
            'pagos.*.salario_base' => 'required|numeric|min:0',
            'pagos.*.promedio_extras' => 'required|numeric|min:0',
            'pagos.*.valor_prima' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $registrados = [];

            foreach ($request->pagos as $pago) {
                // Avoid duplicates
                $existente = LiquidacionPrima::where('empleado_id', $pago['empleado_id'])
                    ->where('anio', $request->anio)
                    ->where('periodo', $request->periodo)
                    ->first();

                if ($existente) {
                    continue; 
                }

                $empleado = Empleado::findOrFail($pago['empleado_id']);

                // 1. Create Egreso
                $egreso = new Egresos();
                $egreso->tipo_egreso = 'Nómina';
                $egreso->concepto = "Pago Prima de Servicios " . ($request->periodo == 1 ? "I" : "II") . " Semestre {$request->anio} - {$empleado->nombre} {$empleado->apellido}";
                $egreso->valor = $pago['valor_prima'];
                $egreso->fecha = $request->fecha_pago;
                $egreso->metodo_pago = $request->metodo_pago;
                $egreso->beneficiario = "{$empleado->nombre} {$empleado->apellido}";
                $egreso->user_id = Auth::id() ?: 1;

                if ($egreso->metodo_pago === 'Caja Menor') {
                    $ultimoMov = CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
                    $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;

                    if ($saldoActual < (float)$egreso->valor) {
                        throw new \Exception('Saldo insuficiente en Caja Menor para pagar a ' . $empleado->nombre . ' ' . $empleado->apellido);
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

                // 2. Save LiquidacionPrima
                $liquidacion = new LiquidacionPrima();
                $liquidacion->empleado_id = $pago['empleado_id'];
                $liquidacion->anio = $request->anio;
                $liquidacion->periodo = $request->periodo;
                $liquidacion->fecha_pago = $request->fecha_pago;
                $liquidacion->dias_trabajados = $pago['dias_trabajados'];
                $liquidacion->salario_base = $pago['salario_base'];
                $liquidacion->promedio_extras = $pago['promedio_extras'];
                $liquidacion->valor_prima = $pago['valor_prima'];
                $liquidacion->egreso_id = $egreso->id;
                $liquidacion->save();

                // 3. Contabilizar
                $this->contabilizarPrima($liquidacion);

                $registrados[] = $liquidacion;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($registrados) . ' primas registradas correctamente.',
                'registros' => $registrados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al guardar la liquidación de primas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a premium payment.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $prima = LiquidacionPrima::findOrFail($id);

            if ($prima->egreso_id) {
                $egreso = Egresos::find($prima->egreso_id);
                if ($egreso) {
                    if ($egreso->metodo_pago === 'Caja Menor') {
                        $cajaMov = CajaMenorMovimiento::where('egreso_id', $egreso->id)->first();
                        if ($cajaMov) {
                            $cajaMov->delete();
                        }
                    }
                    
                    $comp = $egreso->comprobante;
                    if ($comp) {
                        $comp->detalles()->delete();
                        $comp->delete();
                    }

                    $egreso->delete();
                    $this->recalculateCajaMenorBalances();
                }
            }

            $prima->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Liquidación de prima eliminada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar la liquidación de prima: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accounting integration for Primas.
     */
    private function contabilizarPrima($prima)
    {
        $egreso = \App\Egresos::find($prima->egreso_id);
        if (!$egreso) return;

        $comp = $egreso->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Egreso')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Egreso';
            $comp->numero = $ultimoNumero + 1;
        }

        $comp->fecha = $prima->fecha_pago;
        $comp->descripcion = "Contabilización Prima de Servicios " . ($prima->periodo == 1 ? "I" : "II") . " Semestre " . $prima->anio . " - Empleado: " . ($prima->empleado ? ($prima->empleado->nombre . ' ' . $prima->empleado->apellido) : 'Desconocido');
        $comp->user_id = \Illuminate\Support\Facades\Auth::id() ?: $egreso->user_id;
        $comp->save();

        $egreso->comprobante_id = $comp->id;
        $egreso->saveQuietly();

        $comp->detalles()->delete();

        $terceroId = null;
        if ($prima->empleado) {
            $persona = \App\Persona::firstOrCreate(
                ['nombre' => $prima->empleado->nombre . ' ' . $prima->empleado->apellido],
                ['tipo_documento' => 'CC', 'num_documento' => $prima->empleado->num_doc ?: '00000000']
            );
            $terceroId = $persona->id;
        }

        // Gasto Prima de Servicios: 510536
        $cuentaPrimaGasto = \App\Cuenta::where('codigo', '510536')->first();
        if (!$cuentaPrimaGasto) {
            $parent = \App\Cuenta::where('codigo', '5105')->first();
            $cuentaPrimaGasto = \App\Cuenta::create([
                'codigo' => '510536',
                'nombre' => 'Prima de Servicios (Gasto)',
                'tipo' => 'Gasto',
                'naturaleza' => 'Debito',
                'es_detalle' => 1,
                'padre_id' => $parent ? $parent->id : null
            ]);
        }

        if ($cuentaPrimaGasto && $prima->valor_prima > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaPrimaGasto->id,
                'tercero_id' => $terceroId,
                'debe' => $prima->valor_prima,
                'haber' => 0.00,
                'referencia' => 'Gasto Prima de Servicios'
            ]);
        }

        // Credit to Caja or Banco
        if ($egreso->metodo_pago === 'Caja Menor') {
            $cuentaCredito = \App\Cuenta::where('codigo', '110510')->first();
        } else {
            $cuentaCredito = \App\Cuenta::where('codigo', '111005')->first();
        }

        if ($cuentaCredito && $prima->valor_prima > 0) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCredito->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $prima->valor_prima,
                'referencia' => 'Pago Prima de Servicios'
            ]);
        }
    }

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

        $isEndFebLast = ($m2 === 2 && (($y2 % 4 === 0 && $d2 === 29) || ($y2 % 4 !== 0 && $d2 === 28)));
        if ($isEndFebLast) $d2 = 30;
        $isStartFebLast = ($m1 === 2 && (($y1 % 4 === 0 && $d1 === 29) || ($y1 % 4 !== 0 && $d1 === 28)));
        if ($isStartFebLast) $d1 = 30;

        $days = ($y2 - $y1) * 360 + ($m2 - $m1) * 30 + ($d2 - $d1);
        return $days + 1;
    }

    /**
     * Generate and download a PDF payment voucher for a paid prima.
     */
    public function descargarPDFPrima($id)
    {
        $prima = LiquidacionPrima::with(['empleado', 'egreso'])->findOrFail($id);

        // Convert total to letters
        $letrasObj = new \App\CifrasEnLetras();
        $numeroLetras = $letrasObj->convertirEurosEnLetras(number_format($prima->valor_prima, 0)) . ' M/CTE';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.comprobante_prima', compact('prima', 'numeroLetras'));
        return $pdf->stream('comprobante_prima_' . str_pad($prima->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}
