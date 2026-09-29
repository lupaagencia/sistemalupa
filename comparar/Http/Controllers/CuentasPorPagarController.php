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

        $query = CuentaPorPagar::with(['activo', 'proveedor', 'orden.cliente', 'orden.articulo', 'abonos']);

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
            'numero_factura' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'cantidad' => 'nullable|integer|min:1',
            'valor_unitario' => 'nullable|numeric|min:0',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120'
        ]);

        if (empty($request->activo_id) && empty($request->proveedor_id)) {
            return response()->json(['error' => 'Debe seleccionar una operaria o un proveedor.'], 422);
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

        $cuenta = new CuentaPorPagar();
        $cuenta->activo_id = $request->activo_id ?: null;
        $cuenta->proveedor_id = $request->proveedor_id ?: null;
        $cuenta->numero_factura = $numeroFactura ?: null;
        $cuenta->descripcion = $descripcion;
        $cuenta->cantidad = $request->cantidad ?: 1;
        $cuenta->valor_unitario = $request->valor_unitario ?: $monto;
        $cuenta->monto = $monto;
        $cuenta->saldo = $monto;
        $cuenta->estado = 'Pendiente';
        $cuenta->fecha = $request->fecha;
        $cuenta->fecha_vencimiento = $request->fecha_vencimiento ?: null;

        // Handle file upload
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

        return response()->json(['success' => true, 'cuenta' => $cuenta]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'activo_id' => 'nullable|exists:activos,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'numero_factura' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'cantidad' => 'nullable|integer|min:1',
            'valor_unitario' => 'nullable|numeric|min:0',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'soporte_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120'
        ]);

        if (empty($request->activo_id) && empty($request->proveedor_id)) {
            return response()->json(['error' => 'Debe seleccionar una operaria o un proveedor.'], 422);
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

        if ($cuenta->estado === 'Pagado' || $cuenta->abonos()->count() > 0) {
            // If it has payments, we only allow updating description, dates or relations to prevent balance inconsistencies
            $cuenta->descripcion = $descripcion;
            $cuenta->fecha = $request->fecha;
            $cuenta->fecha_vencimiento = $request->fecha_vencimiento ?: null;
            $cuenta->numero_factura = $numeroFactura ?: null;
            $cuenta->activo_id = $request->activo_id ?: null;
            $cuenta->proveedor_id = $request->proveedor_id ?: null;
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
            $cuenta->fecha = $request->fecha;
            $cuenta->fecha_vencimiento = $request->fecha_vencimiento ?: null;
            $cuenta->save();
        }

        return response()->json(['success' => true, 'cuenta' => $cuenta]);
    }

    public function destroy($id)
    {
        $cuenta = CuentaPorPagar::findOrFail($id);
        
        if ($cuenta->abonos()->count() > 0) {
            return response()->json(['error' => 'No se puede eliminar una cuenta que tiene abonos registrados.'], 422);
        }

        // Delete support file if exists
        if ($cuenta->soporte) {
            $path = public_path('uploads/cuentas_por_pagar/' . $cuenta->soporte);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $cuenta->delete();
        return response()->json(['success' => true]);
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
            return response()->json(['error' => 'No se puede registrar un abono en una cuenta "En Espera" hasta que se defina la cantidad final.'], 422);
        }

        if ($request->monto > $cuenta->saldo) {
            return response()->json(['error' => 'El monto del abono supera el saldo pendiente (' . $cuenta->saldo . ').'], 422);
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

            // Get beneficiario name
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
            $egreso->user_id = \Illuminate\Support\Facades\Auth::id() ?: 1;
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

            $cuenta->saldo -= $request->monto;
            if ($cuenta->saldo <= 0) {
                $cuenta->estado = 'Pagado';
            } else {
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
            return response()->json(['error' => 'Error al registrar el abono: ' . $e->getMessage()], 500);
        }
    }

    public function eliminarAbono($id)
    {
        $abono = AbonoCuentaPorPagar::findOrFail($id);
        $cuenta = CuentaPorPagar::findOrFail($abono->cuenta_por_pagar_id);

        DB::beginTransaction();
        try {
            $cuenta->saldo += $abono->monto;
            if ($cuenta->saldo >= $cuenta->monto) {
                $cuenta->estado = 'Pendiente';
            } else {
                $cuenta->estado = 'Abonado';
            }
            $cuenta->save();

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

            $abono->delete();

            // Recalculate petty cash balances since Cascade Delete clears the movement
            $this->recalculateCajaMenorBalances();

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el abono: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerEstadoCuentaIndividual(Request $request)
    {
        $tipo = $request->tipo; // 'operaria' or 'proveedor'
        $id = $request->id;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

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
                ->with(['orden.cliente', 'orden.articulo', 'abonos'])
                ->orderBy('fecha', 'asc')
                ->get();

            $abonos = AbonoCuentaPorPagar::whereHas('cuentaPorPagar', function($q) use ($tipo, $id) {
                    $q->where($tipo === 'operaria' ? 'activo_id' : 'proveedor_id', $id);
                })
                ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
                ->with('cuentaPorPagar')
                ->orderBy('fecha', 'asc')
                ->get();

            $totalGeneral = $cuentas->where('estado', '!=', 'En Espera')->sum('monto');
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
                ->with(['orden.cliente', 'orden.articulo', 'abonos'])
                ->orderBy('fecha', 'asc')
                ->get();

            $abonos = AbonoCuentaPorPagar::whereHas('cuentaPorPagar', function($q) use ($tipo, $id) {
                    $q->where($tipo === 'operaria' ? 'activo_id' : 'proveedor_id', $id);
                })
                ->with('cuentaPorPagar')
                ->orderBy('fecha', 'desc')
                ->get();

            $totalGeneral = $cuentas->where('estado', '!=', 'En Espera')->sum('monto');
            $totalPendiente = $cuentas->where('estado', '!=', 'En Espera')->sum('saldo');
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
        $query = CuentaPorPagar::where('estado', '!=', 'Pagado')->where('estado', '!=', 'En Espera');
        if ($tipo === 'operaria') {
            $query->where('activo_id', $id);
        } else {
            $query->where('proveedor_id', $id);
        }
        $cuentas = $query->orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();

        if ($cuentas->isEmpty()) {
            return response()->json(['error' => 'No hay cuentas pendientes de pago para este beneficiario.'], 422);
        }

        $totalPendiente = $cuentas->sum('saldo');
        if ($montoRestante > $totalPendiente) {
            return response()->json(['error' => 'El monto del abono ($' . number_format($montoRestante, 2) . ') supera el total de la deuda pendiente ($' . number_format($totalPendiente, 2) . ').'], 422);
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

                $abonoMonto = min($montoRestante, $cuenta->saldo);

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

                // Get beneficiario name
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
                $egreso->user_id = \Illuminate\Support\Facades\Auth::id() ?: 1;
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

                $cuenta->saldo -= $abonoMonto;
                if ($cuenta->saldo <= 0) {
                    $cuenta->estado = 'Pagado';
                } else {
                    $cuenta->estado = 'Abonado';
                }
                $cuenta->save();

                $montoRestante -= $abonoMonto;
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

        // Check if new amount exceeds limit
        $diferencia = $request->monto - $abono->monto;
        if ($diferencia > $cuenta->saldo) {
            return response()->json(['error' => 'El nuevo monto del abono supera el saldo pendiente de la cuenta (' . ($cuenta->saldo + $abono->monto) . ').'], 422);
        }

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
            if ($cuenta->saldo <= 0) {
                $cuenta->estado = 'Pagado';
            } else {
                $cuenta->estado = 'Abonado';
            }
            $cuenta->save();

            // Update abono details
            $abono->monto = $request->monto;
            $abono->fecha = $request->fecha;
            $abono->observaciones = $request->observaciones;
            $abono->soporte = $filename;
            $abono->save();

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta, 'abono' => $abono]);
        } catch (\Exception $e) {
            DB::rollBack();
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
}

