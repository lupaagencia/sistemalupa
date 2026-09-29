<?php

namespace App\Http\Controllers;

use App\Egresos;
use App\CajaMenorMovimiento;
use App\Comprobante;
use App\CuentaPorPagar;
use App\AbonoCuentaPorPagar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class EgresosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $tipo_egreso = $request->input('tipo_egreso');
        $metodo_pago = $request->input('metodo_pago');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $perPage = $request->input('per_page', 15);

        $query = Egresos::with(['user', 'cuenta', 'cuentaPorPagar.proveedor']);

        if (!empty($tipo_egreso)) {
            $query->where('tipo_egreso', $tipo_egreso);
        }

        if (!empty($metodo_pago)) {
            $query->where('metodo_pago', $metodo_pago);
        }

        if (!empty($fecha_desde) && !empty($fecha_hasta)) {
            $query->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('concepto', 'like', '%' . $buscar . '%')
                  ->orWhere('beneficiario', 'like', '%' . $buscar . '%');
            });
        }

        $egresos = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return [
            'pagination' => [
                'total'        => $egresos->total(),
                'current_page' => $egresos->currentPage(),
                'per_page'     => $egresos->perPage(),
                'last_page'    => $egresos->lastPage(),
                'from'         => $egresos->firstItem(),
                'to'           => $egresos->lastItem(),
            ],
            'egresos' => $egresos
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_egreso' => 'required|string|max:50',
            'concepto' => 'required|string',
            'valor' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|string|max:50',
            'beneficiario' => 'nullable|string|max:255',
            'soporte' => 'nullable|file|max:5120', // Max 5MB
            'cuenta_id' => 'nullable|exists:cuentas,id',
            'iva' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $egreso = new Egresos();
            $egreso->tipo_egreso = $request->tipo_egreso;
            $egreso->concepto = $request->concepto;
            $egreso->valor = $request->valor;
            $egreso->fecha = $request->fecha;
            $egreso->metodo_pago = $request->metodo_pago;
            $egreso->beneficiario = $request->beneficiario;
            $egreso->cuenta_id = $request->cuenta_id ?: null;
            $egreso->iva = $request->iva ?: 0.00;
            $egreso->user_id = $this->getValidUserId();

            // File upload
            if ($request->hasFile('soporte')) {
                $file = $request->file('soporte');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $ruta = public_path('uploads/egresos');
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                $file->move($ruta, $filename);
                $egreso->soporte = $filename;
            }

            // Check petty cash balance if paid via Caja Menor
            if ($egreso->metodo_pago === 'Caja Menor') {
                $ultimoMov = CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
                $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;
                
                if ($saldoActual < (float)$egreso->valor) {
                    return response()->json([
                        'error' => 'Saldo insuficiente en Caja Menor (Saldo actual: $' . number_format($saldoActual, 2) . ')'
                    ], 422);
                }

                $egreso->save(); // Save first to get the egreso id

                $cajaMov = new CajaMenorMovimiento();
                $cajaMov->tipo = 'Egreso';
                $cajaMov->monto = $egreso->valor;
                $cajaMov->saldo_resultante = $saldoActual - (float)$egreso->valor;
                $cajaMov->fecha = $egreso->fecha;
                $cajaMov->descripcion = $egreso->concepto;
                $cajaMov->soporte = $egreso->soporte;
                $cajaMov->egreso_id = $egreso->id;
                $cajaMov->user_id = $egreso->user_id;
                $cajaMov->save();
            } else {
                $egreso->save();
            }

            // Contabilizar si no es un abono de cuenta por pagar
            if (!$egreso->abono_id) {
                $this->contabilizarEgreso($egreso);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Egreso registrado correctamente.',
                'egreso' => $egreso
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al registrar el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar egresos.'], 403);
        }
        try {
            DB::beginTransaction();

            $egreso = Egresos::findOrFail($id);

            // Delete support file if exists
            if (!empty($egreso->soporte)) {
                $folder = $egreso->abono_id ? 'abonos_cuentas_por_pagar' : 'egresos';
                $file_path = public_path("uploads/{$folder}/" . $egreso->soporte);
                if (File::exists($file_path)) {
                    File::delete($file_path);
                }
            }

            $comp = $egreso->comprobante;
            $egreso->delete();

            if ($comp) {
                $comp->delete();
            }

            // Recalculate all running balances since egreso deletion triggers cascade delete of movements
            $this->recalculateCajaMenorBalances();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Egreso eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the current balance of Caja Menor.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCajaMenorStatus()
    {
        $ultimoMov = CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
        $saldo = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;
        return response()->json([
            'saldo' => $saldo
        ]);
    }

    /**
     * List Caja Menor Movements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function listCajaMenorMovimientos(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $perPage = $request->input('per_page', 15);

        $query = CajaMenorMovimiento::with(['user', 'egreso']);

        if (!empty($fecha_desde) && !empty($fecha_hasta)) {
            $query->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);
        }

        $movimientos = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return [
            'pagination' => [
                'total'        => $movimientos->total(),
                'current_page' => $movimientos->currentPage(),
                'per_page'     => $movimientos->perPage(),
                'last_page'    => $movimientos->lastPage(),
                'from'         => $movimientos->firstItem(),
                'to'           => $movimientos->lastItem(),
            ],
            'movimientos' => $movimientos
        ];
    }

    /**
     * Register a Petty Cash income/reload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarRecargaCajaMenor(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'soporte' => 'nullable|file|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $ultimoMov = CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
            $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;

            $mov = new CajaMenorMovimiento();
            $mov->tipo = 'Ingreso';
            $mov->monto = $request->monto;
            $mov->saldo_resultante = $saldoActual + (float)$request->monto;
            $mov->fecha = $request->fecha;
            $mov->descripcion = $request->descripcion;
            $mov->user_id = $this->getValidUserId();

            if ($request->hasFile('soporte')) {
                $file = $request->file('soporte');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $ruta = public_path('uploads/caja_menor');
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                $file->move($ruta, $filename);
                $mov->soporte = $filename;
            }

            $mov->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caja Menor recargada correctamente.',
                'movimiento' => $mov
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al registrar recarga: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download PDF for Egreso voucher.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function descargarPDFEgreso($id)
    {
        $egreso = Egresos::with(['user'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.comprobante_egreso', compact('egreso'));
        return $pdf->stream('comprobante_egreso_' . str_pad($egreso->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    /**
     * Get the financial consolidation / P&L statement data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConsolidadoResultados(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde', date('Y-m-01'));
        $fecha_hasta = $request->input('fecha_hasta', date('Y-m-t'));

        // 1. Total Ventas (Pedidos con estado > 0)
        $ventasTotal = (float)Comprobante::where('tipo', 'pedido')
            ->where('estado', '>', 0)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->sum('total');

        // 2. Egresos agrupados por tipo_egreso
        $egresosPorClasificacion = Egresos::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->select('tipo_egreso', DB::raw('SUM(valor) as total'))
            ->groupBy('tipo_egreso')
            ->orderBy('total', 'desc')
            ->get();

        $egresosTotal = (float)Egresos::whereBetween('fecha', [$fecha_desde, $fecha_hasta])->sum('valor');

        // 3. Caja Menor Flow
        $ultimoMovAntes = CajaMenorMovimiento::where('fecha', '<', $fecha_desde)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $cajaSaldoInicial = $ultimoMovAntes ? (float)$ultimoMovAntes->saldo_resultante : 0.0;

        $cajaRecargas = (float)CajaMenorMovimiento::where('tipo', 'Ingreso')
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->sum('monto');

        $cajaEgresos = (float)CajaMenorMovimiento::where('tipo', 'Egreso')
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->sum('monto');

        $ultimoMovFin = CajaMenorMovimiento::where('fecha', '<=', $fecha_hasta)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $cajaSaldoFinal = $ultimoMovFin ? (float)$ultimoMovFin->saldo_resultante : 0.0;

        // 4. Cuentas por Pagar Flow (Debt created vs paid abonos)
        $cxpDeudaCreada = (float)CuentaPorPagar::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->sum('monto');

        $cxpAbonosPagados = (float)AbonoCuentaPorPagar::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->sum('monto');

        return response()->json([
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'ventas_total' => $ventasTotal,
            'egresos_total' => $egresosTotal,
            'egresos_detallados' => $egresosPorClasificacion,
            'caja_menor' => [
                'saldo_inicial' => $cajaSaldoInicial,
                'recargas' => $cajaRecargas,
                'egresos' => $cajaEgresos,
                'saldo_final' => $cajaSaldoFinal,
            ],
            'cuentas_por_pagar' => [
                'deuda_creada' => $cxpDeudaCreada,
                'abonos_pagados' => $cxpAbonosPagados,
            ]
        ]);
    }

    /**
     * Recalculates running balances of Caja Menor.
     *
     * @return void
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

    private function getValidUserId()
    {
        $id = \Illuminate\Support\Facades\Auth::id();
        if ($id && \App\User::where('id', $id)->exists()) {
            return $id;
        }
        $firstUser = \App\User::first();
        return $firstUser ? $firstUser->id : null;
    }

    private function contabilizarEgreso($egreso)
    {
        $comp = $egreso->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Egreso')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Egreso';
            $comp->numero = $ultimoNumero + 1;
        }
        $comp->fecha = $egreso->fecha;
        $comp->descripcion = "Egreso No. " . $egreso->id . " [" . $egreso->tipo_egreso . "] - " . $egreso->concepto;
        $comp->user_id = $this->getValidUserId();
        $comp->save();

        $egreso->comprobante_id = $comp->id;
        $egreso->saveQuietly();

        $comp->detalles()->delete();

        // Resolve third party
        $terceroId = null;
        if ($egreso->beneficiario) {
            $persona = \App\Persona::firstOrCreate(
                ['nombre' => $egreso->beneficiario],
                ['tipo_documento' => 'CC', 'num_documento' => '00000000']
            );
            $terceroId = $persona->id;
        }

        // Mapping Debit Account
        $tipoLower = strtolower($egreso->tipo_egreso);
        if (strpos($tipoLower, 'servicio') !== false || strpos($tipoLower, 'publico') !== false) {
            $cuentaDebito = \App\Cuenta::where('codigo', '513505')->first();
        } elseif (strpos($tipoLower, 'sueldo') !== false || strpos($tipoLower, 'nomina') !== false || strpos($tipoLower, 'honorario') !== false) {
            $cuentaDebito = \App\Cuenta::where('codigo', '510506')->first();
        } elseif (strpos($tipoLower, 'transporte') !== false) {
            $cuentaDebito = \App\Cuenta::where('codigo', '510527')->first();
        } else {
            $cuentaDebito = \App\Cuenta::where('codigo', '519595')->first();
        }

        if ($egreso->cuenta_id) {
            $dynamicDebit = \App\Cuenta::find($egreso->cuenta_id);
            if ($dynamicDebit) {
                $cuentaDebito = $dynamicDebit;
            }
        }

        // Mapping Credit Account
        if ($egreso->metodo_pago === 'Caja Menor') {
            $cuentaCredito = \App\Cuenta::where('codigo', '110510')->first();
        } else {
            $cuentaCredito = \App\Cuenta::where('codigo', '111005')->first();
        }

        if ($cuentaDebito && $cuentaCredito) {
            $iva = (float)($egreso->iva ?? 0);
            if ($iva > 0) {
                $subtotal = max(0.00, (float)$egreso->valor - $iva);

                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaDebito->id,
                    'tercero_id' => $terceroId,
                    'debe' => $subtotal,
                    'haber' => 0.00,
                    'referencia' => 'Egreso #' . $egreso->id
                ]);

                $cuentaIva = \App\Cuenta::where('codigo', '240810')->first();
                if (!$cuentaIva) {
                    $cuentaIva = \App\Cuenta::create([
                        'codigo' => '240810',
                        'nombre' => 'Impuesto sobre las Ventas Descontable (IVA)',
                        'tipo' => 'Activo',
                        'naturaleza' => 'Débito',
                        'es_detalle' => 1
                    ]);
                }

                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaIva->id,
                    'tercero_id' => $terceroId,
                    'debe' => $iva,
                    'haber' => 0.00,
                    'referencia' => 'IVA Descontable Egreso #' . $egreso->id
                ]);
            } else {
                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaDebito->id,
                    'tercero_id' => $terceroId,
                    'debe' => $egreso->valor,
                    'haber' => 0.00,
                    'referencia' => 'Egreso #' . $egreso->id
                ]);
            }

            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCredito->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $egreso->valor,
                'referencia' => 'Egreso #' . $egreso->id
            ]);
        }
    }

    public function actualizarRecargaCajaMenor(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'soporte' => 'nullable|file|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $mov = CajaMenorMovimiento::findOrFail($id);
            if ($mov->tipo !== 'Ingreso') {
                return response()->json(['error' => 'Solo se pueden editar recargas (ingresos).'], 422);
            }

            $mov->monto = $request->monto;
            $mov->fecha = $request->fecha;
            $mov->descripcion = $request->descripcion;

            if ($request->hasFile('soporte')) {
                if ($mov->soporte) {
                    $oldPath = public_path('uploads/caja_menor/' . $mov->soporte);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $file = $request->file('soporte');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $ruta = public_path('uploads/caja_menor');
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                $file->move($ruta, $filename);
                $mov->soporte = $filename;
            }

            $mov->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recarga actualizada correctamente.',
                'movimiento' => $mov
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en actualizarRecargaCajaMenor: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error' => 'Error al actualizar la recarga: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarRecargaCajaMenor($id)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar recargas de caja menor.'], 403);
        }
        try {
            DB::beginTransaction();

            $mov = CajaMenorMovimiento::findOrFail($id);
            if ($mov->tipo !== 'Ingreso') {
                return response()->json(['error' => 'Solo se pueden eliminar recargas (ingresos).'], 422);
            }

            if ($mov->soporte) {
                $path = public_path('uploads/caja_menor/' . $mov->soporte);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            $mov->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recarga eliminada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en eliminarRecargaCajaMenor: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error' => 'Error al eliminar la recarga: ' . $e->getMessage()
            ], 500);
        }
    }
}
