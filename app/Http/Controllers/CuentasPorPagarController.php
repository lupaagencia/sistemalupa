<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CuentaPorPagar;
use App\AbonoCuentaPorPagar;
use App\Activo;
use App\Proveedor;
use Illuminate\Support\Facades\DB;

class CuentasPorPagarController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $estado = $request->estado;
        $activo_id = $request->activo_id;
        $proveedor_id = $request->proveedor_id;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;
        $perPage = $request->input('per_page', 15);

        $query = CuentaPorPagar::with(['activo', 'proveedor', 'orden.cliente', 'orden.articulo', 'abonos', 'cuenta']);

        if (!empty($activo_id)) {
            $query->where('activo_id', $activo_id);
        }

        if (!empty($proveedor_id)) {
            $query->where('proveedor_id', $proveedor_id);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $query->whereBetween('fecha', [$fecha_inicio, $fecha_fin]);
        }

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhereHas('activo', function($qa) use ($buscar) {
                      $qa->where('activo', 'like', '%' . $buscar . '%');
                  })
                  ->orWhereHas('proveedor', function($qp) use ($buscar) {
                      $qp->where('nombre', 'like', '%' . $buscar . '%');
                  });
            });
        }

        $cuentas = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return [
            'pagination' => [
                'total'        => $cuentas->total(),
                'current_page' => $cuentas->currentPage(),
                'per_page'     => $cuentas->perPage(),
                'last_page'    => $cuentas->lastPage(),
                'from'         => $cuentas->firstItem(),
                'to'           => $cuentas->lastItem(),
            ],
            'cuentas' => $cuentas
        ];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'activo_id' => 'nullable|exists:activos,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'beneficiario' => 'nullable|string|max:191',
            'numero_factura' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'cantidad' => 'nullable|integer|min:1',
            'valor_unitario' => 'nullable|numeric|min:0',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'cuenta_id' => 'nullable|exists:cuentas,id',
            'iva' => 'nullable|numeric|min:0'
        ]);

        if (empty($request->activo_id) && empty($request->proveedor_id) && empty($request->beneficiario)) {
            return response()->json(['error' => 'Debe seleccionar una operaria, un proveedor o indicar un beneficiario.'], 422);
        }

        $monto = $request->monto;

        // Validar cupo de crédito si es un proveedor y no se aceptó sobrecupo
        if ($request->proveedor_id && !$request->input('aceptar_sobrecupo')) {
            $proveedor = \App\Proveedor::findOrFail($request->proveedor_id);
            if ($proveedor->cupo_credito > 0) {
                $saldo_pendiente = CuentaPorPagar::where('proveedor_id', $request->proveedor_id)
                    ->where('estado', '!=', 'Pagado')
                    ->sum('saldo');
                if (($saldo_pendiente + $monto) > $proveedor->cupo_credito) {
                    return response()->json([
                        'error' => 'Cupo de crédito excedido. El saldo pendiente del proveedor ($' . number_format($saldo_pendiente, 2) . ') más esta cuenta ($' . number_format($monto, 2) . ') supera el cupo de crédito asignado ($' . number_format($proveedor->cupo_credito, 2) . ').'
                    ], 422);
                }
            }
        }

        // Set default values if not provided
        $numeroFactura = $request->numero_factura;
        $descripcion = $request->descripcion;
        if (empty($descripcion)) {
            if ($request->proveedor_id) {
                $descripcion = $numeroFactura ? 'Factura #' . $numeroFactura : 'Factura de Compra';
            } else {
                $descripcion = 'Cuenta de Cobro - Operaria';
            }
        }

        DB::beginTransaction();
        try {
            $cuenta = new CuentaPorPagar();
            $cuenta->activo_id = $request->activo_id;
            $cuenta->proveedor_id = $request->proveedor_id;
            $cuenta->beneficiario = $request->beneficiario;
            $cuenta->numero_factura = $numeroFactura;
            $cuenta->descripcion = $descripcion;
            $cuenta->cantidad = $request->cantidad ?: 1;
            $cuenta->valor_unitario = $request->valor_unitario ?: $monto;
            $cuenta->monto = $monto;
            $cuenta->saldo = $monto;
            $cuenta->iva = $request->iva ?: 0.00;
            $cuenta->fecha = $request->fecha;
            $cuenta->fecha_vencimiento = $request->fecha_vencimiento;
            $cuenta->estado = 'Pendiente';
            $cuenta->cuenta_id = $request->cuenta_id ?: null;

            if ($request->hasFile('soporte_file')) {
                $file = $request->file('soporte_file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = public_path('uploads/cuentas_por_pagar');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file->move($path, $filename);
                $cuenta->soporte = $filename;
            }

            $cuenta->save();

            // Contabilizar
            $this->contabilizarCuenta($cuenta);

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar la cuenta por pagar: ' . $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'activo_id' => 'nullable|exists:activos,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'beneficiario' => 'nullable|string|max:191',
            'numero_factura' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'cantidad' => 'nullable|integer|min:1',
            'valor_unitario' => 'nullable|numeric|min:0',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'estado' => 'nullable|string|in:Pendiente,Abonado,Pagado,En Espera',
            'cuenta_id' => 'nullable|exists:cuentas,id',
            'iva' => 'nullable|numeric|min:0'
        ]);

        if (empty($request->activo_id) && empty($request->proveedor_id) && empty($request->beneficiario)) {
            return response()->json(['error' => 'Debe seleccionar una operaria, un proveedor o indicar un beneficiario.'], 422);
        }

        $cuenta = CuentaPorPagar::findOrFail($id);
        $monto = $request->monto;

        // Set default values if not provided
        $numeroFactura = $request->numero_factura;
        $descripcion = $request->descripcion;
        if (empty($descripcion)) {
            if ($request->proveedor_id) {
                $descripcion = $numeroFactura ? 'Factura #' . $numeroFactura : 'Factura de Compra';
            } else {
                $descripcion = 'Cuenta de Cobro - Operaria';
            }
        }

        // File upload helper
        if ($request->hasFile('soporte_file')) {
            // Delete old file if exists
            if ($cuenta->soporte) {
                $oldPath = public_path('uploads/cuentas_por_pagar/' . $cuenta->soporte);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('soporte_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/cuentas_por_pagar');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
            $cuenta->soporte = $filename;
        }

        DB::beginTransaction();
        try {
            if ($cuenta->estado === 'Pagado' || $cuenta->abonos()->count() > 0) {
                // Allow updating details, monto, description, dates or relations
                $cuenta->descripcion = $descripcion;
                $cuenta->monto = $monto;
                $cuenta->fecha = $request->fecha;
                $cuenta->fecha_vencimiento = $request->fecha_vencimiento ?: null;
                $cuenta->numero_factura = $numeroFactura ?: null;
                $cuenta->activo_id = $request->activo_id ?: null;
                $cuenta->proveedor_id = $request->proveedor_id ?: null;
                $cuenta->beneficiario = $request->beneficiario ?: null;
                $cuenta->cuenta_id = $request->cuenta_id ?: null;
                $cuenta->iva = $request->iva ?: 0.00;

                if ($request->has('estado')) {
                    $nuevoEstado = $request->estado;
                    $cuenta->estado = $nuevoEstado;
                    if ($nuevoEstado === 'Pagado') {
                        $cuenta->saldo = 0;
                    } else {
                        $totalAbonos = $cuenta->abonos()->sum('monto');
                        $cuenta->saldo = max(0, $cuenta->monto - $totalAbonos);
                        if ($nuevoEstado === 'Pendiente' && $totalAbonos > 0) {
                            $cuenta->estado = 'Abonado';
                        }
                    }
                } else {
                    if ($cuenta->estado === 'Pagado') {
                        $cuenta->saldo = 0;
                    } else {
                        $totalAbonos = $cuenta->abonos()->sum('monto');
                        $cuenta->saldo = max(0, $cuenta->monto - $totalAbonos);
                    }
                }

                $cuenta->save();
            } else {
                // Validar cupo de crédito si es un proveedor y no se aceptó sobrecupo
                if ($request->proveedor_id && !$request->input('aceptar_sobrecupo')) {
                    $proveedor = \App\Proveedor::findOrFail($request->proveedor_id);
                    if ($proveedor->cupo_credito > 0) {
                        $saldo_pendiente = CuentaPorPagar::where('proveedor_id', $request->proveedor_id)
                            ->where('id', '!=', $id)
                            ->where('estado', '!=', 'Pagado')
                            ->sum('saldo');
                        if (($saldo_pendiente + $monto) > $proveedor->cupo_credito) {
                            return response()->json([
                                	'error' => 'Cupo de crédito excedido. El saldo pendiente del proveedor ($' . number_format($saldo_pendiente, 2) . ') más esta cuenta ($' . number_format($monto, 2) . ') supera el cupo de crédito asignado ($' . number_format($proveedor->cupo_credito, 2) . ').'
                            ], 422);
                        }
                    }
                }

                $cuenta->activo_id = $request->activo_id ?: null;
                $cuenta->proveedor_id = $request->proveedor_id ?: null;
                $cuenta->numero_factura = $numeroFactura ?: null;
                $cuenta->descripcion = $descripcion;
                $cuenta->cantidad = $request->cantidad ?: 1;
                $cuenta->valor_unitario = $request->valor_unitario ?: $monto;
                $cuenta->monto = $monto;
                $cuenta->saldo = $monto;
                $cuenta->iva = $request->iva ?: 0.00;
                $cuenta->fecha = $request->fecha;
                $cuenta->fecha_vencimiento = $request->fecha_vencimiento ?: null;
                $cuenta->cuenta_id = $request->cuenta_id ?: null;

                if ($request->has('estado')) {
                    $nuevoEstado = $request->estado;
                    $cuenta->estado = $nuevoEstado;
                    if ($nuevoEstado === 'Pagado') {
                        $cuenta->saldo = 0;
                    } else {
                        $totalAbonos = $cuenta->abonos()->sum('monto');
                        $cuenta->saldo = $cuenta->monto - $totalAbonos;
                        if ($nuevoEstado === 'Pendiente' && $totalAbonos > 0) {
                            $cuenta->estado = 'Abonado';
                        }
                    }
                }

                $cuenta->save();
            }

            // Contabilizar
            $this->contabilizarCuenta($cuenta);

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar la cuenta por pagar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar cuentas por pagar.'], 403);
        }
        $cuenta = CuentaPorPagar::findOrFail($id);
        
        if ($cuenta->abonos()->count() > 0) {
            return response()->json(['error' => 'No se puede eliminar una cuenta que tiene abonos registrados.'], 422);
        }

        DB::beginTransaction();
        try {
            // Delete support file if exists
            if ($cuenta->soporte) {
                $path = public_path('uploads/cuentas_por_pagar/' . $cuenta->soporte);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            $comp = $cuenta->comprobante;
            $cuenta->delete();
            if ($comp) {
                $comp->delete();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la cuenta por pagar: ' . $e->getMessage()], 500);
        }
    }

    public function registrarAbono(Request $request)
    {
        $this->validate($request, [
            'cuenta_por_pagar_id' => 'required|exists:cuentas_por_pagar,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'metodo_pago' => 'nullable|string|max:50'
        ]);

        $cuenta = CuentaPorPagar::findOrFail($request->cuenta_por_pagar_id);

        if ($cuenta->estado === 'En Espera') {
            if ($cuenta->cantidad_entregada <= 0) {
                return response()->json(['error' => 'No se puede registrar un abono en una cuenta "En Espera" hasta que se defina la cantidad final o se registren entregas parciales.'], 422);
            }
        }

        $metodo_pago = $request->input('metodo_pago', 'Banco');

        // Check Caja Menor balance
        $saldoActual = 0.0;
        if ($metodo_pago === 'Caja Menor') {
            $ultimoMov = \App\CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
            $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;
            if ($saldoActual < (float)$request->monto) {
                return response()->json([
                    'error' => 'Saldo insuficiente en Caja Menor (Saldo actual: $' . number_format($saldoActual, 2) . ')'
                ], 422);
            }
        }

        $filename = null;
        if ($request->hasFile('soporte_file')) {
            $file = $request->file('soporte_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/abonos_cuentas_por_pagar');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
        }

        DB::beginTransaction();
        try {
            $abono = new AbonoCuentaPorPagar();
            $abono->cuenta_por_pagar_id = $request->cuenta_por_pagar_id;
            $abono->monto = $request->monto;
            $abono->fecha = $request->fecha;
            $abono->observaciones = $request->observaciones;
            if ($filename) {
                $abono->soporte = $filename;
            }
            $abono->metodo_pago = $metodo_pago;
            $abono->save();

            // Contabilizar abono
            $this->contabilizarAbono($abono);

            $beneficiarioNombre = 'Varios';
            if ($cuenta->proveedor) {
                $beneficiarioNombre = $cuenta->proveedor->nombre;
            } elseif ($cuenta->activo) {
                $beneficiarioNombre = $cuenta->activo->activo;
            }

            // Create Egreso
            $egreso = new \App\Egresos();
            $egreso->tipo_egreso = 'Otros';
            $egreso->concepto = "Abono a Cuenta por Pagar No. " . $cuenta->id . " (Factura: " . ($cuenta->numero_factura ?: 'N/A') . ") - Detalle: " . $cuenta->descripcion;
            $egreso->valor = $request->monto;
            $egreso->fecha = $request->fecha;
            $egreso->metodo_pago = $metodo_pago;
            $egreso->beneficiario = $beneficiarioNombre;
            $egreso->soporte = $filename;
            $egreso->cuenta_por_pagar_id = $cuenta->id;
            $egreso->abono_id = $abono->id;
            $egreso->user_id = $this->getValidUserId();
            $egreso->save();

            // Create Caja Menor movement if needed
            if ($metodo_pago === 'Caja Menor') {
                $cajaMov = new \App\CajaMenorMovimiento();
                $cajaMov->tipo = 'Egreso';
                $cajaMov->monto = $request->monto;
                $cajaMov->saldo_resultante = $saldoActual - (float)$request->monto;
                $cajaMov->fecha = $request->fecha;
                $cajaMov->descripcion = $egreso->concepto;
                $cajaMov->soporte = $filename;
                $cajaMov->egreso_id = $egreso->id;
                $cajaMov->user_id = $egreso->user_id;
                $cajaMov->save();
            }

            $totalAbonos = (float) $cuenta->abonos()->sum('monto');
            $cantEnt = (int) ($cuenta->cantidad_entregada ?? 0);
            $cantTot = (int) ($cuenta->cantidad ?? 0);
            $valUnit = (float) ($cuenta->valor_unitario ?? 0);

            if ($cantEnt > 0 && $cantEnt < $cantTot) {
                $montoLiquidado = $cantEnt * $valUnit;
                $cuenta->monto = $montoLiquidado;
                $cuenta->saldo = max(0.0, $montoLiquidado - $totalAbonos);
            } else {
                $montoTotal = $cantTot * $valUnit;
                if ($montoTotal > 0) {
                    $cuenta->monto = $montoTotal;
                }
                $cuenta->saldo = max(0.0, $cuenta->monto - $totalAbonos);
            }

            if ($cuenta->saldo <= 0 && ($cantTot == 0 || $cantEnt >= $cantTot)) {
                $cuenta->estado = 'Pagado';
            } elseif ($totalAbonos > 0) {
                $cuenta->estado = 'Abonado';
            }
            $cuenta->save();

            DB::commit();
            return response()->json(['success' => true, 'abono' => $abono, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filename) {
                $path = public_path('uploads/abonos_cuentas_por_pagar/' . $filename);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            \Log::error('Error en registrarAbono: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error al registrar el abono: ' . $e->getMessage()], 500);
        }
    }

    public function eliminarAbono($id)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar abonos.'], 403);
        }
        $abono = AbonoCuentaPorPagar::findOrFail($id);
        $cuenta = CuentaPorPagar::findOrFail($abono->cuenta_por_pagar_id);

        DB::beginTransaction();
        try {
            // Delete support file if exists and no other abono is using it
            if ($abono->soporte) {
                $otherAbonosWithSameSoporte = AbonoCuentaPorPagar::where('soporte', $abono->soporte)
                    ->where('id', '!=', $abono->id)
                    ->exists();
                if (!$otherAbonosWithSameSoporte) {
                    $path = public_path('uploads/abonos_cuentas_por_pagar/' . $abono->soporte);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $comp = $abono->comprobante;
            $abono->delete();
            if ($comp) {
                $comp->delete();
            }

            // Recalculate account balance and status from remaining real & valid crossing abonos
            if ($cuenta->monto == 0 || $cuenta->numero_factura === 'ANTICIPO') {
                $realAbonos = $cuenta->abonos()
                    ->whereRaw('LOWER(metodo_pago) != ?', ['saldo a favor'])
                    ->sum('monto');
                $obsPattern = '%Ref: Anticipo #' . $cuenta->id . '%';
                $cruseAbonos = AbonoCuentaPorPagar::whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                    ->where('observaciones', 'LIKE', $obsPattern)
                    ->sum('monto');
                $disponible = max(0.0, (float)$realAbonos - (float)$cruseAbonos);
                $cuenta->saldo = -$disponible;
                $cuenta->estado = ($disponible > 0) ? 'Abonado' : 'Pagado';
            } else {
                $realAbonos = $cuenta->abonos()
                    ->whereRaw('LOWER(metodo_pago) != ?', ['saldo a favor'])
                    ->sum('monto');
                $cruseAbonos = $cuenta->abonos()
                    ->whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                    ->sum('monto');
                $totalAbonos = (float)$realAbonos + (float)$cruseAbonos;
                $nuevoSaldo = (float)$cuenta->monto - $totalAbonos;
                $cuenta->saldo = $nuevoSaldo;
                if ($cuenta->estado !== 'En Espera') {
                    if ($nuevoSaldo <= 0) {
                        $cuenta->estado = ($nuevoSaldo < 0) ? 'Abonado' : 'Pagado';
                    } elseif ($totalAbonos > 0) {
                        $cuenta->estado = 'Abonado';
                    } else {
                        $cuenta->estado = 'Pendiente';
                    }
                }
            }
            $cuenta->save();

            // Recalculate petty cash balances since Cascade Delete clears the movement
            $this->recalculateCajaMenorBalances();

            DB::commit();

            $cuenta->abonos = $cuenta->abonos()->get();

            return response()->json(['success' => true, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en eliminarAbono: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error al eliminar el abono: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerEstadoCuentaIndividual(Request $request)
    {
        $tipo = $request->tipo; // 'operaria' or 'proveedor'
        $id = $request->id;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        // Auto-cruzar saldos a favor existentes
        CuentaPorPagar::cruzarSaldosAFavor($tipo === 'operaria' ? $id : null, $tipo === 'proveedor' ? $id : null);

        if ($tipo === 'operaria') {
            $beneficiario = Activo::findOrFail($id);
            $nombre = $beneficiario->activo;
            $detalles = "Operaria de Terminado";
        } else {
            $beneficiario = Proveedor::findOrFail($id);
            $nombre = $beneficiario->nombre;
            $detalles = "NIT: " . $beneficiario->num_documento . " | Tel: " . $beneficiario->telefono;
        }

        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $cuentas = CuentaPorPagar::where($tipo === 'operaria' ? 'activo_id' : 'proveedor_id', $id)
                ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
                ->with(['orden.cliente', 'orden.articulo', 'abonos', 'cuenta'])
                ->orderBy('fecha', 'asc')
                ->get();

            $abonos = AbonoCuentaPorPagar::whereHas('cuentaPorPagar', function($q) use ($tipo, $id) {
                    $q->where($tipo === 'operaria' ? 'activo_id' : 'proveedor_id', $id);
                })
                ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
                ->with('cuentaPorPagar')
                ->orderBy('fecha', 'asc')
                ->get();

            $totalGeneral = 0;
            foreach ($cuentas as $c) {
                if ($c->estado === 'En Espera') {
                    $totalGeneral += ($c->cantidad_entregada * $c->valor_unitario);
                } else {
                    $totalGeneral += $c->monto;
                }
            }
            $totalPagado = $abonos->sum('monto');
            $totalPendiente = $totalGeneral - $totalPagado;

            return [
                'nombre' => $nombre,
                'detalles' => $detalles,
                'total_registrado' => (float)$totalGeneral,
                'total_pendiente' => (float)$totalPendiente,
                'total_pagado' => (float)$totalPagado,
                'cupo_credito' => $tipo === 'proveedor' ? (float)$beneficiario->cupo_credito : 0.00,
                'cuentas' => $cuentas,
                'abonos' => $abonos,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ];
        } else {
            $cuentas = CuentaPorPagar::where($tipo === 'operaria' ? 'activo_id' : 'proveedor_id', $id)
                ->where('estado', '!=', 'Pagado')
                ->with(['orden.cliente', 'orden.articulo', 'abonos', 'cuenta'])
                ->orderBy('fecha', 'asc')
                ->get();

            $abonos = AbonoCuentaPorPagar::whereHas('cuentaPorPagar', function($q) use ($tipo, $id) {
                    $q->where($tipo === 'operaria' ? 'activo_id' : 'proveedor_id', $id);
                })
                ->with('cuentaPorPagar')
                ->orderBy('fecha', 'desc')
                ->get();

            $totalGeneral = 0;
            $totalPendiente = 0;
            foreach ($cuentas as $c) {
                if ($c->estado === 'En Espera') {
                    $valReg = $c->cantidad_entregada * $c->valor_unitario;
                    $totalGeneral += $valReg;
                    $totalAbonos = $c->abonos()->sum('monto');
                    $totalPendiente += max(0.0, $valReg - $totalAbonos);
                } else {
                    $totalGeneral += $c->monto;
                    $totalPendiente += $c->saldo;
                }
            }
            $totalPagado = $totalGeneral - $totalPendiente;

            return [
                'nombre' => $nombre,
                'detalles' => $detalles,
                'total_registrado' => (float)$totalGeneral,
                'total_pendiente' => (float)$totalPendiente,
                'total_pagado' => (float)$totalPagado,
                'cupo_credito' => $tipo === 'proveedor' ? (float)$beneficiario->cupo_credito : 0.00,
                'cuentas' => $cuentas,
                'abonos' => $abonos,
                'fecha_inicio' => null,
                'fecha_fin' => null
            ];
        }
    }

    public function registrarAbonoGeneral(Request $request)
    {
        $this->validate($request, [
            'tipo' => 'required|in:operaria,proveedor',
            'id' => 'required|integer',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'metodo_pago' => 'nullable|string|max:50'
        ]);

        $tipo = $request->tipo;
        $id = $request->id;
        $montoRestante = $request->monto;

        // Query pending accounts chronologically (oldest first)
        $query = CuentaPorPagar::where('estado', '!=', 'Pagado')
            ->where(function($q) {
                $q->where('estado', '!=', 'En Espera')
                  ->orWhere(function($q2) {
                      $q2->where('estado', 'En Espera')
                         ->where('cantidad_entregada', '>', 0);
                  });
            });

        if ($tipo === 'operaria') {
            $query->where('activo_id', $id);
        } else {
            $query->where('proveedor_id', $id);
        }
        $cuentas = $query->orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();

        $totalPendiente = 0;
        foreach ($cuentas as $c) {
            if ($c->estado === 'En Espera') {
                $valorEntregado = $c->cantidad_entregada * $c->valor_unitario;
                $totalAbonos = $c->abonos()->sum('monto');
                $totalPendiente += max(0.0, $valorEntregado - $totalAbonos);
            } else {
                $totalPendiente += $c->saldo;
            }
        }

        $metodo_pago = $request->input('metodo_pago', 'Banco');

        // Check Caja Menor balance
        $saldoActual = 0.0;
        if ($metodo_pago === 'Caja Menor') {
            $ultimoMov = \App\CajaMenorMovimiento::orderBy('fecha', 'desc')->orderBy('id', 'desc')->first();
            $saldoActual = $ultimoMov ? (float)$ultimoMov->saldo_resultante : 0.0;
            if ($saldoActual < (float)$request->monto) {
                return response()->json([
                    'error' => 'Saldo insuficiente en Caja Menor (Saldo actual: $' . number_format($saldoActual, 2) . ')'
                ], 422);
            }
        }

        $filename = null;
        if ($request->hasFile('soporte_file')) {
            $file = $request->file('soporte_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/abonos_cuentas_por_pagar');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
        }

        DB::beginTransaction();
        try {
            foreach ($cuentas as $cuenta) {
                if ($montoRestante <= 0) {
                    break;
                }

                $cantEnt = (int) ($cuenta->cantidad_entregada ?? 0);
                $cantTot = (int) ($cuenta->cantidad ?? 0);
                $valUnit = (float) ($cuenta->valor_unitario ?? 0);

                if ($cantEnt > 0 && $cantEnt < $cantTot) {
                    $valorEntregado = $cantEnt * $valUnit;
                    $totalAbonos = (float) $cuenta->abonos()->sum('monto');
                    $saldoPagable = max(0.0, $valorEntregado - $totalAbonos);
                } else {
                    $saldoPagable = $cuenta->saldo;
                }

                if ($saldoPagable <= 0) {
                    continue;
                }

                $abonoMonto = min($montoRestante, $saldoPagable);

                $abono = new AbonoCuentaPorPagar();
                $abono->cuenta_por_pagar_id = $cuenta->id;
                $abono->monto = $abonoMonto;
                $abono->fecha = $request->fecha;
                $abono->observaciones = $request->observaciones ?: 'Abono general';
                if ($filename) {
                    $abono->soporte = $filename;
                }
                $abono->metodo_pago = $metodo_pago;
                $abono->save();

                // Contabilizar abono general
                $this->contabilizarAbono($abono);

                $beneficiarioNombre = 'Varios';
                if ($cuenta->proveedor) {
                    $beneficiarioNombre = $cuenta->proveedor->nombre;
                } elseif ($cuenta->activo) {
                    $beneficiarioNombre = $cuenta->activo->activo;
                }

                // Create Egreso
                $egreso = new \App\Egresos();
                $egreso->tipo_egreso = 'Otros';
                $egreso->concepto = "Abono a Cuenta por Pagar No. " . $cuenta->id . " (Factura: " . ($cuenta->numero_factura ?: 'N/A') . ") - Detalle: " . $cuenta->descripcion;
                $egreso->valor = $abonoMonto;
                $egreso->fecha = $request->fecha;
                $egreso->metodo_pago = $metodo_pago;
                $egreso->beneficiario = $beneficiarioNombre;
                $egreso->soporte = $filename;
                $egreso->cuenta_por_pagar_id = $cuenta->id;
                $egreso->abono_id = $abono->id;
                $egreso->user_id = $this->getValidUserId();
                $egreso->save();

                // Create Caja Menor movement if needed
                if ($metodo_pago === 'Caja Menor') {
                    $cajaMov = new \App\CajaMenorMovimiento();
                    $cajaMov->tipo = 'Egreso';
                    $cajaMov->monto = $abonoMonto;
                    $cajaMov->saldo_resultante = $saldoActual - (float)$abonoMonto;
                    $cajaMov->fecha = $request->fecha;
                    $cajaMov->descripcion = $egreso->concepto;
                    $cajaMov->soporte = $filename;
                    $cajaMov->egreso_id = $egreso->id;
                    $cajaMov->user_id = $egreso->user_id;
                    $cajaMov->save();

                    // Update running balance locally
                    $saldoActual -= (float)$abonoMonto;
                }

                $totalAbonos = (float) $cuenta->abonos()->sum('monto');
                if ($cantEnt > 0 && $cantEnt < $cantTot) {
                    $montoLiquidado = $cantEnt * $valUnit;
                    $cuenta->monto = $montoLiquidado;
                    $cuenta->saldo = max(0.0, $montoLiquidado - $totalAbonos);
                } else {
                    $montoTotal = $cantTot * $valUnit;
                    if ($montoTotal > 0) {
                        $cuenta->monto = $montoTotal;
                    }
                    $cuenta->saldo = max(0.0, $cuenta->monto - $totalAbonos);
                }

                if ($cuenta->saldo <= 0 && ($cantTot == 0 || $cantEnt >= $cantTot)) {
                    $cuenta->estado = 'Pagado';
                } elseif ($totalAbonos > 0) {
                    $cuenta->estado = 'Abonado';
                }
                $cuenta->save();

                $montoRestante -= $abonoMonto;
            }

            // If there is still a remaining amount (excess), create a placeholder account for the advance
            if ($montoRestante > 0) {
                $anticipo = new CuentaPorPagar();
                $anticipo->proveedor_id = $tipo === 'proveedor' ? $id : null;
                $anticipo->activo_id = $tipo === 'operaria' ? $id : null;
                $anticipo->numero_factura = 'ANTICIPO';
                $anticipo->descripcion = 'Anticipo / Saldo a Favor';
                $anticipo->cantidad = 0;
                $anticipo->cantidad_entregada = 0;
                $anticipo->valor_unitario = 0;
                $anticipo->monto = 0;
                $anticipo->saldo = -$montoRestante;
                $anticipo->estado = 'Abonado';
                $anticipo->fecha = $request->fecha;
                $anticipo->save();

                $abono = new AbonoCuentaPorPagar();
                $abono->cuenta_por_pagar_id = $anticipo->id;
                $abono->monto = $montoRestante;
                $abono->fecha = $request->fecha;
                $abono->observaciones = $request->observaciones ?: 'Abono general (Exceso/Anticipo)';
                if ($filename) {
                    $abono->soporte = $filename;
                }
                $abono->metodo_pago = $metodo_pago;
                $abono->save();

                // Contabilizar abono general (anticipo)
                $this->contabilizarAbono($abono);

                $beneficiarioNombre = 'Varios';
                if ($tipo === 'proveedor') {
                    $prov = Proveedor::find($id);
                    if ($prov) $beneficiarioNombre = $prov->nombre;
                } else {
                    $act = Activo::find($id);
                    if ($act) $beneficiarioNombre = $act->activo;
                }

                // Create Egreso
                $egreso = new \App\Egresos();
                $egreso->tipo_egreso = 'Otros';
                $egreso->concepto = "Abono General (Anticipo / Saldo a Favor) No. " . $anticipo->id;
                $egreso->valor = $montoRestante;
                $egreso->fecha = $request->fecha;
                $egreso->metodo_pago = $metodo_pago;
                $egreso->beneficiario = $beneficiarioNombre;
                $egreso->soporte = $filename;
                $egreso->cuenta_por_pagar_id = $anticipo->id;
                $egreso->abono_id = $abono->id;
                $egreso->user_id = $this->getValidUserId();
                $egreso->save();

                // Create Caja Menor movement if needed
                if ($metodo_pago === 'Caja Menor') {
                    $cajaMov = new \App\CajaMenorMovimiento();
                    $cajaMov->tipo = 'Egreso';
                    $cajaMov->monto = $montoRestante;
                    $cajaMov->saldo_resultante = $saldoActual - (float)$montoRestante;
                    $cajaMov->fecha = $request->fecha;
                    $cajaMov->descripcion = $egreso->concepto;
                    $cajaMov->soporte = $filename;
                    $cajaMov->egreso_id = $egreso->id;
                    $cajaMov->user_id = $egreso->user_id;
                    $cajaMov->save();
                    
                    $saldoActual -= (float)$montoRestante;
                }
                
                $montoRestante = 0;
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filename) {
                $path = public_path('uploads/abonos_cuentas_por_pagar/' . $filename);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            \Log::error('Error en registrarAbonoGeneral: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error al registrar el abono general: ' . $e->getMessage()], 500);
        }
    }

    public function actualizarAbono(Request $request, $id)
    {
        $this->validate($request, [
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120'
        ]);

        $abono = AbonoCuentaPorPagar::findOrFail($id);
        $cuenta = CuentaPorPagar::findOrFail($abono->cuenta_por_pagar_id);

        $diferencia = $request->monto - $abono->monto;

        $filename = $abono->soporte;
        if ($request->hasFile('soporte_file')) {
            // Delete old file if exists and not used elsewhere
            if ($abono->soporte) {
                $otherAbonosWithSameSoporte = AbonoCuentaPorPagar::where('soporte', $abono->soporte)
                    ->where('id', '!=', $abono->id)
                    ->exists();
                if (!$otherAbonosWithSameSoporte) {
                    $path = public_path('uploads/abonos_cuentas_por_pagar/' . $abono->soporte);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $file = $request->file('soporte_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/abonos_cuentas_por_pagar');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
        }

        DB::beginTransaction();
        try {
            // Recalculate account balance and state
            $cuenta->saldo -= $diferencia;
            if ($cuenta->estado !== 'En Espera') {
                if ($cuenta->saldo == 0) {
                    $cuenta->estado = 'Pagado';
                } elseif ($cuenta->saldo >= $cuenta->monto) {
                    $cuenta->estado = 'Pendiente';
                } else {
                    $cuenta->estado = 'Abonado';
                }
            }
            $cuenta->save();

            // Update abono details
            $abono->monto = $request->monto;
            $abono->fecha = $request->fecha;
            $abono->observaciones = $request->observaciones;
            $abono->soporte = $filename;
            $abono->save();

            // Contabilizar abono actualizado
            $this->contabilizarAbono($abono);

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta, 'abono' => $abono]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en actualizarAbono: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error al actualizar el abono: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download PDF for a Cuenta por Pagar abono formatted as Egreso.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function descargarPDFAbono($id)
    {
        $egreso = \App\Egresos::with(['user'])->where('abono_id', $id)->first();
        
        if (!$egreso) {
            $abono = AbonoCuentaPorPagar::with(['cuentaPorPagar.proveedor', 'cuentaPorPagar.activo'])->findOrFail($id);
            
            $beneficiarioNombre = 'Varios';
            if ($abono->cuentaPorPagar->proveedor) {
                $beneficiarioNombre = $abono->cuentaPorPagar->proveedor->nombre;
            } elseif ($abono->cuentaPorPagar->activo) {
                $beneficiarioNombre = $abono->cuentaPorPagar->activo->activo;
            }
            
            $egreso = new \App\Egresos();
            $egreso->id = $abono->id;
            $egreso->tipo_egreso = 'Otros';
            $egreso->concepto = "Abono a Cuenta por Pagar No. " . $abono->cuenta_por_pagar_id . " - Observaciones: " . $abono->observaciones;
            $egreso->valor = $abono->monto;
            $egreso->fecha = $abono->fecha;
            $egreso->metodo_pago = $abono->metodo_pago ?: 'Banco';
            $egreso->beneficiario = $beneficiarioNombre;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.comprobante_egreso', compact('egreso'));
        return $pdf->stream('comprobante_egreso_abono_' . str_pad($egreso->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    /**
     * Recalculates running balances of Caja Menor.
     *
     * @return void
     */
    private function recalculateCajaMenorBalances()
    {
        $movimientos = \App\CajaMenorMovimiento::orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();
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

    public function contabilizarCuenta($cuenta)
    {
        $comp = $cuenta->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Diario')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Diario';
            $comp->numero = $ultimoNumero + 1;
        }
        $comp->fecha = $cuenta->fecha;
        $comp->descripcion = "CXP No. " . $cuenta->id . " - " . $cuenta->descripcion;
        $comp->user_id = $this->getValidUserId();
        $comp->save();

        $cuenta->comprobante_id = $comp->id;
        $cuenta->saveQuietly();

        $comp->detalles()->delete();

        $terceroId = null;
        if ($cuenta->proveedor_id) {
            $terceroId = $cuenta->proveedor_id;
            $cuentaCredito = \App\Cuenta::where('codigo', '220505')->first();
            $cuentaDebito = \App\Cuenta::where('codigo', '519595')->first();
        } else {
            if ($cuenta->activo) {
                $persona = \App\Persona::firstOrCreate(
                    ['nombre' => $cuenta->activo->activo],
                    ['tipo_documento' => 'CC', 'num_documento' => '00000000']
                );
                $terceroId = $persona->id;
            }
            $cuentaCredito = \App\Cuenta::where('codigo', '233525')->first();
            if (!$cuentaCredito) {
                $parent = \App\Cuenta::where('codigo', '2335')->first();
                $cuentaCredito = \App\Cuenta::create([
                    'codigo' => '233525',
                    'nombre' => 'Honorarios y Operarias por Pagar',
                    'tipo' => 'Pasivo',
                    'naturaleza' => 'Credito',
                    'es_detalle' => true,
                    'padre_id' => $parent ? $parent->id : null
                ]);
            }
            $cuentaDebito = \App\Cuenta::where('codigo', '510506')->first();
        }

        if ($cuenta->cuenta_id) {
            $dynamicDebit = \App\Cuenta::find($cuenta->cuenta_id);
            if ($dynamicDebit) {
                $cuentaDebito = $dynamicDebit;
            }
        }

        if ($cuentaDebito && $cuentaCredito) {
            $iva = (float)($cuenta->iva ?? 0);
            if ($iva > 0) {
                $subtotal = max(0.00, (float)$cuenta->monto - $iva);
                
                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaDebito->id,
                    'tercero_id' => $terceroId,
                    'debe' => $subtotal,
                    'haber' => 0.00,
                    'referencia' => 'Subtotal CXP #' . $cuenta->id
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
                    'referencia' => 'IVA Descontable CXP #' . $cuenta->id
                ]);
            } else {
                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaDebito->id,
                    'tercero_id' => $terceroId,
                    'debe' => $cuenta->monto,
                    'haber' => 0.00,
                    'referencia' => 'CXP #' . $cuenta->id
                ]);
            }

            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCredito->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $cuenta->monto,
                'referencia' => 'CXP #' . $cuenta->id
            ]);
        }
    }

    public function contabilizarAbono($abono)
    {
        $cuenta = $abono->cuentaPorPagar;
        if (!$cuenta) return;

        $comp = $abono->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Egreso')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Egreso';
            $comp->numero = $ultimoNumero + 1;
        }
        $comp->fecha = $abono->fecha;
        $comp->descripcion = "Abono a CXP No. " . $cuenta->id . " - " . ($abono->observaciones ?: 'Pago general');
        $comp->user_id = $this->getValidUserId();
        $comp->save();

        $abono->comprobante_id = $comp->id;
        $abono->saveQuietly();

        $comp->detalles()->delete();

        $terceroId = null;
        if ($cuenta->proveedor_id) {
            $terceroId = $cuenta->proveedor_id;
            $cuentaDebito = \App\Cuenta::where('codigo', '220505')->first();
        } else {
            if ($cuenta->activo) {
                $persona = \App\Persona::firstOrCreate(
                    ['nombre' => $cuenta->activo->activo],
                    ['tipo_documento' => 'CC', 'num_documento' => '00000000']
                );
                $terceroId = $persona->id;
            }
            $cuentaDebito = \App\Cuenta::where('codigo', '233525')->first();
            if (!$cuentaDebito) {
                $parent = \App\Cuenta::where('codigo', '2335')->first();
                $cuentaDebito = \App\Cuenta::create([
                    'codigo' => '233525',
                    'nombre' => 'Honorarios y Operarias por Pagar',
                    'tipo' => 'Pasivo',
                    'naturaleza' => 'Credito',
                    'es_detalle' => true,
                    'padre_id' => $parent ? $parent->id : null
                ]);
            }
        }

        if ($abono->metodo_pago === 'Caja Menor') {
            $cuentaCredito = \App\Cuenta::where('codigo', '110510')->first();
        } else {
            $cuentaCredito = \App\Cuenta::where('codigo', '111005')->first();
        }

        if ($cuentaDebito && $cuentaCredito) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaDebito->id,
                'tercero_id' => $terceroId,
                'debe' => $abono->monto,
                'haber' => 0.00,
                'referencia' => 'Abono CXP #' . $cuenta->id
            ]);

            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCredito->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $abono->monto,
                'referencia' => 'Abono CXP #' . $cuenta->id
            ]);
        }
    }
}

