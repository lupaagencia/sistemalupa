<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entrega;
use App\EntregaItems;
use App\Ordentrabajo;
use App\LineaComprobante;
use App\Comprobante;
use App\Ajustes;
use App\PedidoRemision; // Added this line
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class EntregasController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ordentrabajo_id' => 'required|exists:ordentrabajos,id',
            'cantidad' => 'required|numeric|min:1',
            'tipo_documento' => 'required|in:Remision,Cuenta de Cobro,Ninguno',
            'observaciones' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $orden = Ordentrabajo::with(['cliente', 'articulo'])->findOrFail($request->ordentrabajo_id);
            $cantidadAEntregar = (int) $request->cantidad;

            $totalOriginal = (int) $orden->cantidad;
            $yaEntregado = (int) ($orden->cantidad_entregada ?? 0);

            $nuevaCantidadEntregada = $yaEntregado + $cantidadAEntregar;

            // Si la nueva entrega supera lo pedido originalmente, actualizamos el pedido
            if ($nuevaCantidadEntregada > $totalOriginal) {
                if (!$orden->cantidad_original) {
                    $orden->cantidad_original = $totalOriginal;
                }
                $orden->cantidad = $nuevaCantidadEntregada;

                // Buscamos el ID del comprobante original (Pedido)
                $originalId = LineaComprobante::where('ordentrabajo_id', $orden->id)->value('comprobante_id');
                if ($originalId) {
                    $lineaPedido = LineaComprobante::where('comprobante_id', $originalId)
                        ->where('ordentrabajo_id', $orden->id)
                        ->first();

                    if ($lineaPedido) {
                        $lineaPedido->cantidad = $orden->cantidad;
                        $lineaPedido->subtotal = $orden->cantidad * $lineaPedido->valor_unitario;
                        $lineaPedido->valor_total = $lineaPedido->subtotal;
                        $lineaPedido->save();

                        $pedido = Comprobante::find($originalId);
                        if ($pedido) {
                            $nuevoSubtotal = LineaComprobante::where('comprobante_id', $pedido->id)->sum('valor_total');
                            $pedido->subtotal = $nuevoSubtotal;
                            $tasaIva = (float) ($pedido->iva ?? 0.19);
                            $pedido->impuestos = round($nuevoSubtotal * $tasaIva, 2);
                            $pedido->total = $pedido->subtotal + $pedido->impuestos;
                            $pedido->saldo = max(0, $pedido->total - ($pedido->abono ?? 0));
                            $pedido->save();
                        }
                    }
                }
                $totalOriginal = $orden->cantidad;
            }

            $pendiente = $totalOriginal - $yaEntregado;
            $saldoAnterior = $pendiente;
            $saldoRestante = $pendiente - $cantidadAEntregar;

            $orden->cantidad_entregada = $nuevaCantidadEntregada;

            if ($orden->cantidad_entregada >= $orden->cantidad) {
                $orden->produccion = 'T';
            }
            $orden->save();

            $consecutivo = null;
            $comprobante_id = null;

            if ($request->tipo_documento !== 'Ninguno') {
                $tipoDoc = $request->tipo_documento === 'Remision' ? 'remision' : 'cuentacobro';

                // SIEMPRE CREAR NUEVA REMISION
                $ajusteKey = 'consecutivo_' . $tipoDoc;
                $ajuste = Ajustes::where('tipo', 'consecutivo')->where('detalle', $ajusteKey)->first();
                if (!$ajuste) {
                    $ajuste = Ajustes::create(['tipo' => 'consecutivo', 'detalle' => $ajusteKey, 'valor' => 0]);
                }
                $ajuste->valor = (int) $ajuste->valor + 1;
                $ajuste->save();

                $consecutivo = $ajuste->valor;

                $originalPedido = null;
                $originalId = LineaComprobante::where('ordentrabajo_id', $orden->id)->value('comprobante_id');
                if ($originalId) {
                    $originalPedido = Comprobante::find($originalId);
                }

                $comprobante = new Comprobante();
                $comprobante->tipo = $tipoDoc;
                $comprobante->num_comprobante = $consecutivo;
                $comprobante->cliente_id = $orden->cliente_id;
                $comprobante->pedido_id = $originalPedido ? $originalPedido->id : null;
                $comprobante->datos_factura_id = $originalPedido ? $originalPedido->datos_factura_id : null;
                $comprobante->fuente_id = $originalPedido ? $originalPedido->id : null; // Para CC directa de pedido
                $comprobante->user_id = Auth::id() ?? 7;
                $comprobante->fecha = now()->toDateString();
                $comprobante->iva = ($originalPedido && $originalPedido->iva !== null) ? $originalPedido->iva : 0.19;

                $valorUnitario = (float) ($orden->valor_unitario ?? 0);
                $comprobante->subtotal = $cantidadAEntregar * $valorUnitario;

                // Calcular impuestos y total
                $tasaIva = (float) ($comprobante->iva ?? 0.19);
                $impuestos = round($comprobante->subtotal * $tasaIva, 2);
                $comprobante->impuestos = $impuestos;
                $comprobante->total = $comprobante->subtotal + $impuestos;
                $comprobante->saldo = $comprobante->total;
                $comprobante->estado = 'Cerrado';
                $comprobante->save();

                // Cruzar automáticamente con saldos a favor (recibos con saldo) solo si NO es remisión
                if ($tipoDoc !== 'remision') {
                    $this->cruzarConSaldosFavor($comprobante);
                }
                $comprobante_id = $comprobante->id;

                // Registrar en pedidos_remision
                if ($comprobante->pedido_id) {
                    PedidoRemision::updateOrCreate([
                        'pedido_id' => $comprobante->pedido_id,
                        'remision_id' => ($tipoDoc === 'remision' ? $comprobante->id : null),
                        'cuentacobro_id' => ($tipoDoc === 'cuentacobro' ? $comprobante->id : null)
                    ]);
                }

                $linea = new LineaComprobante();
                $linea->comprobante_id = $comprobante_id;
                $linea->articulo_id = $orden->articulo_id;
                $linea->ordentrabajo_id = $orden->id;
                $linea->cantidad = $cantidadAEntregar;
                $linea->valor_unitario = $valorUnitario;
                $linea->subtotal = $cantidadAEntregar * $valorUnitario;
                $linea->valor_total = $linea->subtotal;
                $linea->fecha = now()->toDateString();
                $linea->save();
            }

            $entrega = Entrega::create([
                'ordentrabajo_id' => $orden->id,
                'pedido_id' => LineaComprobante::where('ordentrabajo_id', $orden->id)->value('comprobante_id'),
                'user_id' => Auth::id() ?? 1,
                'cantidad' => $cantidadAEntregar,
                'saldo_anterior' => $saldoAnterior,
                'saldo_restante' => $saldoRestante,
                'tipo_documento' => $request->tipo_documento,
                'numero_remision' => $consecutivo,
                'comprobante_id' => $comprobante_id,
                'fecha' => now()->toDateString(),
                'observaciones' => $request->observaciones ?? ''
            ]);

            // Actualizar estado del pedido si se completó la entrega
            if ($entrega->pedido_id) {
                $this->verificarYActualizarPedido($entrega->pedido_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Entrega registrada correctamente',
                'entrega_id' => $entrega->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar la entrega: ' . $e->getMessage()], 500);
        }
    }

    public function storeMassive(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required',
            'items' => 'required|array',
            'tipo_documento' => 'required|in:Remision,Cuenta de Cobro,Ninguno',
            'observaciones' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $clienteId = $request->cliente_id;
            $tipoDocumento = $request->tipo_documento;
            $observaciones = $request->observaciones;
            $userId = Auth::id() ?? 1;

            $comprobante_id = null;
            $consecutivo = null;
            $itemsDelivered = [];
            $pedidosInvolucrados = [];

            if ($tipoDocumento !== 'Ninguno') {
                $tipoDocShort = $tipoDocumento === 'Remision' ? 'remision' : 'cuentacobro';

                $ajusteKey = 'consecutivo_' . $tipoDocShort;
                $ajuste = Ajustes::where('tipo', 'consecutivo')->where('detalle', $ajusteKey)->first();
                if (!$ajuste) {
                    $ajuste = Ajustes::create(['tipo' => 'consecutivo', 'detalle' => $ajusteKey, 'valor' => 0]);
                }
                $ajuste->valor = (int) $ajuste->valor + 1;
                $ajuste->save();
                $consecutivo = $ajuste->valor;

                $comprobante = new Comprobante();
                $comprobante->tipo = $tipoDocShort;
                $comprobante->num_comprobante = $consecutivo;
                $comprobante->cliente_id = $clienteId;

                // Extraer todos los IDs de pedido involucrados (pueden ser uno o varios)
                $involvedPedidoIds = array_unique(array_filter(array_column($request->items, 'pedido_id')));
                $firstPedidoId = reset($involvedPedidoIds) ?: null;
                $origPedido = $firstPedidoId ? Comprobante::find($firstPedidoId) : null;

                $comprobante->datos_factura_id = $origPedido ? $origPedido->datos_factura_id : null;
                $comprobante->pedido_id = $firstPedidoId;
                $comprobante->fuente_id = implode(',', $involvedPedidoIds);
                $comprobante->user_id = $userId;
                $comprobante->fecha = $request->fecha ?? now()->toDateString();
                $comprobante->iva = $origPedido ? $origPedido->iva : 0.19;
                $comprobante->subtotal = 0;
                $comprobante->total = 0;
                $comprobante->estado = 'Cerrado';
                $comprobante->save();
                $comprobante_id = $comprobante->id;

                // Registrar en pedidos_remision
                foreach ($involvedPedidoIds as $pId) {
                    PedidoRemision::updateOrCreate([
                        'pedido_id' => $pId,
                        'remision_id' => ($tipoDocShort === 'remision' ? $comprobante->id : null),
                        'cuentacobro_id' => ($tipoDocShort === 'cuentacobro' ? $comprobante->id : null)
                    ]);
                }
            }

            $totalSubtotal = 0;
            foreach ($request->items as $itemData) {
                $cant = (int) ($itemData['cantidad'] ?? 0);
                $esFinal = filter_var($itemData['es_final'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($cant <= 0 && !$esFinal)
                    continue;

                $orden = Ordentrabajo::findOrFail($itemData['ordentrabajo_id']);
                $pedidoId = $itemData['pedido_id']; // ID del pedido original de esta linea
                $pedidosInvolucrados[$pedidoId] = true;

                $totalOriginal = (int) $orden->cantidad;
                $yaEntregado = (int) ($orden->cantidad_entregada ?? 0);
                $nuevaCantidadEntregada = $yaEntregado + $cant;

                if ($nuevaCantidadEntregada > $totalOriginal || $esFinal) {
                    if (!$orden->cantidad_original) {
                        $orden->cantidad_original = $totalOriginal;
                    }
                    $orden->cantidad = $nuevaCantidadEntregada;

                    $lineaPedido = LineaComprobante::where('comprobante_id', $pedidoId)
                        ->where('ordentrabajo_id', $orden->id)
                        ->first();

                    if ($lineaPedido) {
                        $lineaPedido->cantidad = $orden->cantidad;
                        $lineaPedido->subtotal = $orden->cantidad * $lineaPedido->valor_unitario;
                        $lineaPedido->valor_total = $lineaPedido->subtotal;
                        $lineaPedido->save();

                        $pedido = Comprobante::find($pedidoId);
                        if ($pedido) {
                            $nuevoSubtotal = LineaComprobante::where('comprobante_id', $pedido->id)->sum('valor_total');
                            $pedido->subtotal = $nuevoSubtotal;
                            $tasaIva = (float) ($pedido->iva ?? 0.19);
                            $pedido->impuestos = round($nuevoSubtotal * $tasaIva, 2);
                            $pedido->total = $pedido->subtotal + $pedido->impuestos;
                            $pedido->saldo = max(0, $pedido->total - ($pedido->abono ?? 0));
                            $pedido->save();
                        }
                    }
                    $totalOriginal = $orden->cantidad;
                }

                $pendiente = $totalOriginal - $yaEntregado;
                $orden->cantidad_entregada = $nuevaCantidadEntregada;
                $orden->produccion = ($orden->cantidad_entregada >= $orden->cantidad) ? 'T' : 'P';
                $orden->save();

                if ($comprobante_id) {
                    $valorUnitario = (float) ($orden->valor_unitario ?? 0);
                    $montoItem = $cant * $valorUnitario;
                    $totalSubtotal += $montoItem;

                    $linea = new LineaComprobante();
                    $linea->comprobante_id = $comprobante_id;
                    $linea->articulo_id = $orden->articulo_id;
                    $linea->ordentrabajo_id = $orden->id;
                    $linea->cantidad = $cant;
                    $linea->valor_unitario = $valorUnitario;
                    $linea->subtotal = $montoItem;
                    $linea->valor_total = $montoItem;
                    $linea->fecha = now()->toDateString();
                    $linea->save();
                }

                $entrega = Entrega::create([
                    'ordentrabajo_id' => $orden->id,
                    'pedido_id' => $pedidoId,
                    'user_id' => $userId,
                    'cantidad' => $cant,
                    'saldo_anterior' => $pendiente,
                    'saldo_restante' => $pendiente - $cant,
                    'tipo_documento' => $tipoDocumento,
                    'numero_remision' => $consecutivo,
                    'comprobante_id' => $comprobante_id,
                    'fecha' => $request->fecha ?? now()->toDateString(),
                    'observaciones' => $observaciones ?? ''
                ]);
                $itemsDelivered[] = $entrega->id;
            }

            $ccAutoId = null;
            if ($comprobante_id) {
                $comprobante = Comprobante::find($comprobante_id);
                $tasaIva = (float) ($comprobante->iva ?? 0.19);
                $montoIva = round($totalSubtotal * $tasaIva, 2);

                $comprobante->subtotal = $totalSubtotal;
                $comprobante->impuestos = $montoIva;
                $comprobante->total = $comprobante->subtotal + $montoIva;
                $comprobante->saldo = $comprobante->total;

                // Cruzar automáticamente con saldos a favor (recibos con saldo) solo si NO es remisión
                if ($comprobante->tipo !== 'remision') {
                    $this->cruzarConSaldosFavor($comprobante);
                }

                // Crear Cuenta de Cobro automática si es Remisión
                if ($comprobante->tipo === 'remision') {
                    $ajusteCC = Ajustes::where('tipo', 'consecutivo')->where('detalle', 'consecutivo_cuentacobro')->first();
                    if (!$ajusteCC) {
                        $ajusteCC = Ajustes::create(['tipo' => 'consecutivo', 'detalle' => 'consecutivo_cuentacobro', 'valor' => 0]);
                    }
                    $ajusteCC->valor = (int) $ajusteCC->valor + 1;
                    $ajusteCC->save();

                    $cc = new Comprobante();
                    $cc->tipo = 'cuentacobro';
                    $cc->num_comprobante = $ajusteCC->valor;
                    $cc->pedido_id = $comprobante->pedido_id;
                    $cc->cliente_id = $comprobante->cliente_id;
                    $cc->datos_factura_id = $comprobante->datos_factura_id;
                    $cc->fuente_id = $comprobante->id;
                    $cc->user_id = $userId;
                    $cc->fecha = $comprobante->fecha;
                    $cc->subtotal = $comprobante->subtotal;
                    $cc->iva = $comprobante->iva;
                    $cc->impuestos = $comprobante->impuestos;
                    $cc->total = $comprobante->total;
                    // La CC debe buscar cruces tambien
                    $cc->abono = 0;
                    $cc->saldo = $cc->total;
                    $cc->estado = 'Cerrado';
                    $cc->save();
                    $ccAutoId = $cc->id;

                    // Vincular CC con la remisión y pedidos en la tabla puente
                    PedidoRemision::where('remision_id', $comprobante->id)->update(['cuentacobro_id' => $ccAutoId]);

                    // Si la remisión NO tiene abonos (porque es remisión), la CC sí debe buscarlos
                    $this->cruzarConSaldosFavor($cc);

                    // Copiar lineas
                    foreach (LineaComprobante::where('comprobante_id', $comprobante->id)->get() as $lRem) {
                        $lcCopy = $lRem->replicate();
                        $lcCopy->comprobante_id = $cc->id;
                        $lcCopy->save();
                    }

                    // Actualizar entregas con el ID de la CC
                    Entrega::whereIn('id', $itemsDelivered)->update(['cuentacobro_id' => $ccAutoId]);
                }
            }

            // Actualizar estado de los pedidos involucrados
            foreach (array_keys($pedidosInvolucrados) as $pId) {
                $this->verificarYActualizarPedido($pId);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Entrega masiva registrada correctamente',
                'comprobante_id' => $comprobante_id,
                'cuentacobro_id' => $ccAutoId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function storeBatch(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required',
            'items' => 'required|array',
            'tipo_documento' => 'required|in:Remision,Cuenta de Cobro,Ninguno',
            'observaciones' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $pedidoId = $request->pedido_id;
            $tipoDocumento = $request->tipo_documento;
            $observaciones = $request->observaciones;
            $userId = Auth::id() ?? 1;
            $esEdicion = filter_var($request->es_edicion, FILTER_VALIDATE_BOOLEAN);
            $comprobanteDestino = $request->comprobante_id_destino;

            $comprobante_id = null;
            $consecutivo = null;
            $itemsDelivered = [];

            if ($esEdicion && $comprobanteDestino) {
                $entregasViejas = Entrega::where('comprobante_id', $comprobanteDestino)->get();
                foreach ($entregasViejas as $ev) {
                    $ord = Ordentrabajo::find($ev->ordentrabajo_id);
                    if ($ord) {
                        $ord->cantidad_entregada = max(0, (int) $ord->cantidad_entregada - (int) $ev->cantidad);
                        if ($ord->cantidad_original && $ord->cantidad_entregada <= $ord->cantidad_original) {
                            $ord->cantidad = $ord->cantidad_original;
                        }
                        $ord->produccion = ($ord->cantidad_entregada >= $ord->cantidad) ? 'T' : 'P';
                        $ord->save();
                    }
                    $ev->delete();
                }
                LineaComprobante::where('comprobante_id', $comprobanteDestino)->delete();

                $comprobante = Comprobante::findOrFail($comprobanteDestino);
                if ($request->fecha)
                    $comprobante->fecha = $request->fecha;
                $comprobante->subtotal = 0;
                $comprobante->total = 0;
                $comprobante->save();
                $comprobante_id = $comprobante->id;
                $consecutivo = $comprobante->num_comprobante;

            } elseif ($tipoDocumento !== 'Ninguno') {
                $tipoDocShort = $tipoDocumento === 'Remision' ? 'remision' : 'cuentacobro';
                $originalPedido = Comprobante::findOrFail($pedidoId);

                $ajusteKey = 'consecutivo_' . $tipoDocShort;
                $ajuste = Ajustes::where('tipo', 'consecutivo')->where('detalle', $ajusteKey)->first();
                if (!$ajuste) {
                    $ajuste = Ajustes::create(['tipo' => 'consecutivo', 'detalle' => $ajusteKey, 'valor' => 0]);
                }
                $ajuste->valor = (int) $ajuste->valor + 1;
                $ajuste->save();
                $consecutivo = $ajuste->valor;

                $comprobante = new Comprobante();
                $comprobante->tipo = $tipoDocShort;
                $comprobante->num_comprobante = $consecutivo;
                $comprobante->pedido_id = $pedidoId;
                $comprobante->cliente_id = $originalPedido->cliente_id;
                $comprobante->datos_factura_id = $originalPedido->datos_factura_id;
                $comprobante->fuente_id = $originalPedido->id;
                $comprobante->user_id = $userId;
                $comprobante->fecha = $request->fecha ?? now()->toDateString();
                $comprobante->iva = $originalPedido->iva ?? 0;
                $comprobante->subtotal = 0;
                $comprobante->total = 0;
                $comprobante->estado = 'Cerrado';
                $comprobante->save();
                $comprobante_id = $comprobante->id;

                // Registrar en pedidos_remision
                PedidoRemision::updateOrCreate([
                    'pedido_id' => $pedidoId,
                    'remision_id' => ($tipoDocShort === 'remision' ? $comprobante->id : null),
                    'cuentacobro_id' => ($tipoDocShort === 'cuentacobro' ? $comprobante->id : null)
                ]);
            }

            $totalSubtotal = 0;
            foreach ($request->items as $itemData) {
                $cant = (int) ($itemData['cantidad'] ?? 0);
                $esFinal = filter_var($itemData['es_final'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($cant <= 0 && !$esFinal)
                    continue;

                $orden = Ordentrabajo::findOrFail($itemData['ordentrabajo_id']);
                $totalOriginal = (int) $orden->cantidad;
                $yaEntregado = (int) ($orden->cantidad_entregada ?? 0);

                $nuevaCantidadEntregada = $yaEntregado + $cant;

                if ($nuevaCantidadEntregada > $totalOriginal || $esFinal) {
                    if (!$orden->cantidad_original) {
                        $orden->cantidad_original = $totalOriginal;
                    }
                    $orden->cantidad = $nuevaCantidadEntregada;

                    $lineaPedido = LineaComprobante::where('comprobante_id', $pedidoId)
                        ->where('ordentrabajo_id', $orden->id)
                        ->first();

                    if ($lineaPedido) {
                        $lineaPedido->cantidad = $orden->cantidad;
                        $lineaPedido->subtotal = $orden->cantidad * $lineaPedido->valor_unitario;
                        $lineaPedido->valor_total = $lineaPedido->subtotal;
                        $lineaPedido->save();

                        $pedido = Comprobante::find($pedidoId);
                        if ($pedido) {
                            $nuevoSubtotal = LineaComprobante::where('comprobante_id', $pedido->id)->sum('valor_total');
                            $pedido->subtotal = $nuevoSubtotal;
                            $tasaIva = (float) ($pedido->iva ?? 0.19);
                            $pedido->impuestos = round($nuevoSubtotal * $tasaIva, 2);
                            $pedido->total = $pedido->subtotal + $pedido->impuestos;
                            $pedido->saldo = max(0, $pedido->total - ($pedido->abono ?? 0));
                            $pedido->save();
                        }
                    }
                    $totalOriginal = $orden->cantidad;
                }

                $pendiente = $totalOriginal - $yaEntregado;

                $orden->cantidad_entregada = $nuevaCantidadEntregada;
                $orden->produccion = ($orden->cantidad_entregada >= $orden->cantidad) ? 'T' : 'P';
                $orden->save();

                if ($comprobante_id) {
                    $valorUnitario = (float) ($orden->valor_unitario ?? 0);
                    $montoItem = $cant * $valorUnitario;
                    $totalSubtotal += $montoItem;

                    $linea = new LineaComprobante();
                    $linea->comprobante_id = $comprobante_id;
                    $linea->articulo_id = $orden->articulo_id;
                    $linea->ordentrabajo_id = $orden->id;
                    $linea->cantidad = $cant;
                    $linea->valor_unitario = $valorUnitario;
                    $linea->subtotal = $montoItem;
                    $linea->valor_total = $montoItem;
                    $linea->fecha = now()->toDateString();
                    $linea->save();
                }

                $entrega = Entrega::create([
                    'ordentrabajo_id' => $orden->id,
                    'pedido_id' => $pedidoId,
                    'user_id' => $userId,
                    'cantidad' => $cant,
                    'saldo_anterior' => $pendiente,
                    'saldo_restante' => $pendiente - $cant,
                    'tipo_documento' => $tipoDocumento,
                    'numero_remision' => $consecutivo,
                    'comprobante_id' => $comprobante_id,
                    'fecha' => $request->fecha ?? now()->toDateString(),
                    'observaciones' => $itemData['observaciones'] ?? ''
                ]);
                $itemsDelivered[] = $entrega->id;
            }

            if ($comprobante_id) {
                $comprobante = Comprobante::find($comprobante_id);

                // Usar la tasa de IVA directamente del pedido original o 0.19 por defecto
                $pedOrig2 = Comprobante::find($pedidoId);
                $tasaIva = (float) ($pedOrig2->iva ?? 0.19);
                $montoIva = round($totalSubtotal * $tasaIva, 2);

                $comprobante->subtotal = $esEdicion ? ($comprobante->subtotal + $totalSubtotal) : $totalSubtotal;
                $comprobante->iva = $pedOrig2->iva ?? 0.19;
                $comprobante->impuestos = $montoIva;
                $comprobante->total = $comprobante->subtotal + $montoIva;
                $comprobante->saldo = $comprobante->total;
                $comprobante->save();

                // Cruzar automáticamente con saldos a favor solo si NO es remisión
                if ($comprobante->tipo !== 'remision') {
                    $this->cruzarConSaldosFavor($comprobante);
                }


                // Si es remision nueva, crear la CC automaticamente
                $ccAutoId = null;
                if ($comprobante->tipo === 'remision' && !$esEdicion) {
                    $ajusteCC = Ajustes::where('tipo', 'consecutivo')->where('detalle', 'consecutivo_cuentacobro')->first();
                    if (!$ajusteCC) {
                        $ajusteCC = Ajustes::create(['tipo' => 'consecutivo', 'detalle' => 'consecutivo_cuentacobro', 'valor' => 0]);
                    }
                    $ajusteCC->valor = (int) $ajusteCC->valor + 1;
                    $ajusteCC->save();

                    $cc = new Comprobante();
                    $cc->tipo = 'cuentacobro';
                    $cc->num_comprobante = $ajusteCC->valor;
                    $cc->pedido_id = $comprobante->pedido_id;
                    $cc->cliente_id = $comprobante->cliente_id;
                    $cc->datos_factura_id = $comprobante->datos_factura_id;
                    $cc->fuente_id = $comprobante->id;
                    $cc->user_id = $userId;
                    $cc->fecha = $comprobante->fecha;
                    $cc->subtotal = $comprobante->subtotal;
                    $cc->iva = $comprobante->iva;
                    $cc->impuestos = $montoIva;
                    $cc->total = $comprobante->total;
                    // La CC debe buscar cruces tambien
                    $cc->abono = 0;
                    $cc->saldo = $cc->total;
                    $cc->estado = 'Cerrado';
                    $cc->save();
                    $ccAutoId = $cc->id;

                    // Vincular CC con la remisión y pedido en la tabla puente
                    PedidoRemision::where('remision_id', $comprobante->id)->update(['cuentacobro_id' => $ccAutoId]);

                    // Si la remisión NO tiene abonos, la CC sí debe buscarlos
                    $this->cruzarConSaldosFavor($cc);

                    // Copiar lineas de remision a CC
                    foreach (LineaComprobante::where('comprobante_id', $comprobante->id)->get() as $lRem) {
                        $lcCopy = $lRem->replicate();
                        $lcCopy->comprobante_id = $cc->id;
                        $lcCopy->save();
                    }

                    // Marcar entregas con el ID de la CC
                    Entrega::whereIn('id', $itemsDelivered)->update(['cuentacobro_id' => $ccAutoId]);
                }
            }

            // Actualizar estado del pedido si se completó la entrega
            if ($pedidoId) {
                $this->verificarYActualizarPedido($pedidoId);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $esEdicion ? 'Remision actualizada correctamente' : 'Remision registrada correctamente',
                'comprobante_id' => $comprobante_id,
                'cuentacobro_id' => $ccAutoId ?? null,
                'entrega_ids' => $itemsDelivered
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function pdf($id)
    {
        $entrega = Entrega::with(['ordentrabajo.cliente', 'ordentrabajo.articulo', 'usuario', 'comprobante', 'cuentacobro'])->findOrFail($id);

        $data = [
            'entrega' => $entrega,
            'orden' => $entrega->ordentrabajo,
            'cliente' => $entrega->ordentrabajo->cliente,
            'articulo' => $entrega->ordentrabajo->articulo,
            'logo' => public_path('img/LOGO-LUPA.jpg')
        ];

        if ($entrega->tipo_documento === 'Remision') {
            $pdfContent = Pdf::loadView('pdf.remision_entrega', $data);
            $filename = 'Remision_';
        } else {
            $pdfContent = Pdf::loadView('pdf.cuentacobro_entrega', $data);
            $filename = 'Cuenta_Cobro_';
        }

        return $pdfContent->stream($filename . ($entrega->numero_remision ?? $entrega->id) . '.pdf');
    }

    public function pdfBatch($comprobante_id)
    {
        $comprobante = Comprobante::with(['cliente', 'lineas.articulo', 'lineas.orden', 'usuario'])->findOrFail($comprobante_id);

        $parentIds = $comprobante->getPedidoPadreIds();
        $pedido = !empty($parentIds) ? Comprobante::find(reset($parentIds)) : null;

        $data = [
            'comprobante' => $comprobante,
            'cliente' => $comprobante->cliente,
            'lineas' => $comprobante->lineas,
            'pedido' => $pedido,
            'logo' => public_path('img/LOGO-LUPA.jpg')
        ];

        $view = ($comprobante->tipo === 'remision') ? 'pdf.remision_entrega_batch' : 'pdf.cuentacobro_entrega_batch';
        $filename = ($comprobante->tipo === 'remision' ? 'Remision_' : 'Cuenta_Cobro_') . ($comprobante->num_comprobante ?? $comprobante->id) . '.pdf';

        $pdfContentBatch = Pdf::loadView($view, $data);
        return $pdfContentBatch->stream($filename);
    }

    public function index()
    {
        $entregas = Entrega::with(['ordentrabajo.articulo', 'usuario', 'comprobante'])
            ->orderBy('id', 'desc')
            ->paginate(50);
        return response()->json($entregas);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|numeric|min:1',
            'observaciones' => 'nullable|string',
            'tipo_documento' => 'required|string|in:Remision,Cuenta de Cobro,Ninguno'
        ]);

        DB::beginTransaction();
        try {
            $entrega = Entrega::findOrFail($id);
            $orden = Ordentrabajo::findOrFail($entrega->ordentrabajo_id);

            $nuevaCantidad = (int) $request->cantidad;
            $diferencia = $nuevaCantidad - (int) $entrega->cantidad;

            $orden->cantidad_entregada += $diferencia;

            if ($orden->cantidad_entregada > $orden->cantidad) {
                if (!$orden->cantidad_original) {
                    $orden->cantidad_original = $orden->cantidad;
                }
                $orden->cantidad = $orden->cantidad_entregada;

                $originalId = LineaComprobante::where('ordentrabajo_id', $orden->id)->value('comprobante_id');
                if ($originalId) {
                    $lineaPedido = LineaComprobante::where('comprobante_id', $originalId)
                        ->where('ordentrabajo_id', $orden->id)
                        ->first();

                    if ($lineaPedido) {
                        $lineaPedido->cantidad = $orden->cantidad;
                        $lineaPedido->subtotal = $orden->cantidad * $lineaPedido->valor_unitario;
                        $lineaPedido->valor_total = $lineaPedido->subtotal;
                        $lineaPedido->save();

                        $pedido = Comprobante::find($originalId);
                        if ($pedido) {
                            $nuevoSubtotal = LineaComprobante::where('comprobante_id', $pedido->id)->sum('valor_total');
                            $pedido->subtotal = $nuevoSubtotal;
                            $tasaIva = (float) ($pedido->iva ?? 0.19);
                            $pedido->impuestos = round($nuevoSubtotal * $tasaIva, 2);
                            $pedido->total = $pedido->subtotal + $pedido->impuestos;
                            $pedido->saldo = max(0, $pedido->total - ($pedido->abono ?? 0));
                            $pedido->save();
                        }
                    }
                }
            }

            $orden->produccion = ($orden->cantidad_entregada >= $orden->cantidad) ? 'T' : 'P';
            $orden->save();

            if ($entrega->comprobante_id) {
                $linea = LineaComprobante::where('comprobante_id', $entrega->comprobante_id)
                    ->where('ordentrabajo_id', $entrega->ordentrabajo_id)
                    ->first();

                if ($linea) {
                    $valorUnitario = (float) $linea->valor_unitario;
                    $linea->cantidad = $nuevaCantidad;
                    $linea->subtotal = $nuevaCantidad * $valorUnitario;
                    $linea->valor_total = $linea->subtotal;
                    $linea->save();

                    $comprobante = Comprobante::find($entrega->comprobante_id);
                    if ($comprobante) {
                        $total = LineaComprobante::where('comprobante_id', $comprobante->id)->sum('valor_total');
                        $comprobante->subtotal = $total;
                        $comprobante->total = $total;

                        $tipoDocMap = [
                            'Remision' => 'remision',
                            'Cuenta de Cobro' => 'cuentacobro'
                        ];
                        if (isset($tipoDocMap[$request->tipo_documento])) {
                            $comprobante->tipo = $tipoDocMap[$request->tipo_documento];
                        }

                        $comprobante->save();
                    }
                }
            }

            $entrega->cantidad = $nuevaCantidad;
            $entrega->saldo_restante = (int) $entrega->saldo_anterior - $nuevaCantidad;
            $entrega->observaciones = $request->observaciones ?? '';
            $entrega->tipo_documento = $request->tipo_documento;
            $entrega->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Entrega actualizada correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $entrega = Entrega::findOrFail($id);
            $orden = Ordentrabajo::findOrFail($entrega->ordentrabajo_id);

            $orden->cantidad_entregada -= (int) $entrega->cantidad;

            if ($orden->cantidad_original && $orden->cantidad_entregada <= $orden->cantidad_original) {
                $orden->cantidad = $orden->cantidad_original;

                $originalId = LineaComprobante::where('ordentrabajo_id', $orden->id)->value('comprobante_id');
                if ($originalId) {
                    $lineaPedido = LineaComprobante::where('comprobante_id', $originalId)
                        ->where('ordentrabajo_id', $orden->id)
                        ->first();
                    if ($lineaPedido) {
                        $lineaPedido->cantidad = $orden->cantidad;
                        $lineaPedido->subtotal = $orden->cantidad * $lineaPedido->valor_unitario;
                        $lineaPedido->valor_total = $lineaPedido->subtotal;
                        $lineaPedido->save();

                        $pedido = Comprobante::find($originalId);
                        if ($pedido) {
                            $nuevoSubtotal = LineaComprobante::where('comprobante_id', $pedido->id)->sum('valor_total');
                            $pedido->subtotal = $nuevoSubtotal;
                            $tasaIva = (float) ($pedido->iva ?? 0.19);
                            $pedido->impuestos = round($nuevoSubtotal * $tasaIva, 2);
                            $pedido->total = $pedido->subtotal + $pedido->impuestos;
                            $pedido->saldo = max(0, $pedido->total - ($pedido->abono ?? 0));
                            $pedido->save();
                        }
                    }
                }
            }

            if ($orden->cantidad_entregada < $orden->cantidad) {
                $orden->produccion = 'E'; // Vuelve a Empacado

                // Asegurar que el pedido original vuelva a producción (estado 2)
                $lineaPedido = \App\LineaComprobante::where('ordentrabajo_id', $orden->id)
                    ->whereHas('pedido', function ($q) {
                        $q->where('tipo', 'pedido');
                    })
                    ->first();
                if ($lineaPedido && $lineaPedido->pedido) {
                    $lineaPedido->pedido->estado = 2; // Produccion
                    $lineaPedido->pedido->save();
                }

                // Asegurar que el status de producción vuelva a 'Empaque' (o estado asociado a 'E')
                $statusOrd = \App\statusProduccion::where('idorden', $orden->id)->first();
                if ($statusOrd) {
                    $statusOrd->estado = config('constantes.ESTADO_CAMBIO'); // 'Empaque'
                    $statusOrd->save();
                }
            }
            $orden->save();

            if ($entrega->comprobante_id) {
                $comprobante = Comprobante::find($entrega->comprobante_id);
                if ($comprobante) {
                    $linea = LineaComprobante::where('comprobante_id', $comprobante->id)
                        ->where('ordentrabajo_id', $entrega->ordentrabajo_id)
                        ->first();

                    if ($linea) {
                        $linea->delete();

                        // Si existe una CC vinculada, borrar su linea tambien
                        if ($entrega->cuentacobro_id) {
                            \App\LineaComprobante::where('comprobante_id', $entrega->cuentacobro_id)
                                ->where('ordentrabajo_id', $entrega->ordentrabajo_id)
                                ->delete();

                            // Recalcular el total de la CC asociada
                            $ccObj = \App\Comprobante::find($entrega->cuentacobro_id);
                            if ($ccObj) {
                                $totalCC = \App\LineaComprobante::where('comprobante_id', $ccObj->id)->sum('valor_total');
                                if ($totalCC <= 0) {
                                    $ccObj->delete();
                                } else {
                                    $ccObj->subtotal = $totalCC;
                                    $ccObj->impuestos = round($totalCC * ($ccObj->iva ?? 0.19), 2);
                                    $ccObj->total = $ccObj->subtotal + $ccObj->impuestos;
                                    $ccObj->saldo = max(0, $ccObj->total - $ccObj->abono);
                                    $ccObj->save();
                                }
                            }
                        }
                    }

                    if (LineaComprobante::where('comprobante_id', $comprobante->id)->count() == 0) {
                        // Si es remision, borrar la CC asociada
                        if ($comprobante->tipo === 'remision') {
                            $cc = Comprobante::where('tipo', 'cuentacobro')->where('fuente_id', $comprobante->id)->first();
                            if ($cc) {
                                LineaComprobante::where('comprobante_id', $cc->id)->delete();
                                $cc->delete();
                            }
                        }
                        $comprobante->delete();
                    } else {
                        $total = LineaComprobante::where('comprobante_id', $comprobante->id)->sum('valor_total');
                        $comprobante->subtotal = $total;
                        $comprobante->total = $total;
                        $comprobante->save();
                    }
                }
            }

            $entrega->delete();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    public function getByPedido($id)
    {
        $entregas = Entrega::with(['ordentrabajo.articulo', 'usuario', 'comprobante'])
            ->where('pedido_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($entregas);
    }

    public function destroyComprobante($id)
    {
        DB::beginTransaction();
        try {
            $comprobante = Comprobante::findOrFail($id);
            $lineas = LineaComprobante::where('comprobante_id', $id)->get();

            foreach ($lineas as $linea) {
                $orden = Ordentrabajo::find($linea->ordentrabajo_id);
                if ($orden) {
                    $orden->cantidad_entregada = max(0, (int) $orden->cantidad_entregada - (int) $linea->cantidad);
                    if ($orden->cantidad_original && $orden->cantidad_entregada <= $orden->cantidad_original) {
                        $orden->cantidad = $orden->cantidad_original;
                        $originalId = LineaComprobante::where('ordentrabajo_id', $orden->id)->value('comprobante_id');
                        if ($originalId) {
                            $lineaPedido = LineaComprobante::where('comprobante_id', $originalId)
                                ->where('ordentrabajo_id', $orden->id)
                                ->first();
                            if ($lineaPedido) {
                                $lineaPedido->cantidad = $orden->cantidad;
                                $lineaPedido->subtotal = $orden->cantidad * $lineaPedido->valor_unitario;
                                $lineaPedido->valor_total = $lineaPedido->subtotal;
                                $lineaPedido->save();

                                $pedido = Comprobante::find($originalId);
                                if ($pedido) {
                                    $nuevoSubtotal = LineaComprobante::where('comprobante_id', $pedido->id)->sum('valor_total');
                                    $pedido->subtotal = $nuevoSubtotal;
                                    $tasaIva = (float) ($pedido->iva ?? 0.19);
                                    $pedido->impuestos = round($nuevoSubtotal * $tasaIva, 2);
                                    $pedido->total = $pedido->subtotal + $pedido->impuestos;
                                    $pedido->saldo = max(0, $pedido->total - ($pedido->abono ?? 0));
                                    $pedido->save();
                                }
                            }
                        }
                    }
                    if ($orden->cantidad_entregada < $orden->cantidad) {
                        $orden->produccion = 'E'; // Vuelve a Empacado

                        // Asegurar que el pedido original vuelva a producción (estado 2)
                        $lineaPedido = \App\LineaComprobante::where('ordentrabajo_id', $orden->id)
                            ->whereHas('pedido', function ($q) {
                                $q->where('tipo', 'pedido');
                            })
                            ->first();
                        if ($lineaPedido && $lineaPedido->pedido) {
                            $lineaPedido->pedido->estado = 2; // Produccion
                            $lineaPedido->pedido->save();
                        }

                        // Asegurar que el status de producción vuelva a 'Empaque' (o estado asociado a 'E')
                        $statusOrd = \App\statusProduccion::where('idorden', $orden->id)->first();
                        if ($statusOrd) {
                            $statusOrd->estado = config('constantes.ESTADO_CAMBIO'); // 'Empaque'
                            $statusOrd->save();
                        }
                    }
                    $orden->save();
                }
            }

            // Si es remision, borrar la CC asociada y sus lineas
            if ($comprobante->tipo === 'remision') {
                $cc = Comprobante::where('tipo', 'cuentacobro')->where('fuente_id', $id)->first();
                if ($cc) {
                    LineaComprobante::where('comprobante_id', $cc->id)->delete();
                    $cc->delete();
                }
            }

            Entrega::where('comprobante_id', $id)->delete();
            LineaComprobante::where('comprobante_id', $id)->delete();
            $comprobante->delete();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el comprobante: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Busca recibos de pago con saldo (valor a favor) del cliente
     * y los cruza automáticamente con el nuevo comprobante.
     */
    private function cruzarConSaldosFavor(Comprobante $comprobante)
    {
        $clienteId = $comprobante->cliente_id;
        if (!$clienteId)
            return;

        // Identificar pedido padre para priorizar sus abonos
        $pedidoPadreId = $comprobante->getPedidoPadreId();

        // Buscar recibos con saldo disponible para este cliente
        // Priorizamos los vinculados al pedido padre, luego por antigüedad
        $recibosConSaldo = \App\ReciboPago::where('cliente_id', $clienteId)
            ->where('saldo_recibo', '>', 0)
            ->orderByRaw("CASE WHEN pedido_id = ? THEN 0 ELSE 1 END", [$pedidoPadreId])
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($recibosConSaldo as $recibo) {
            if ($comprobante->saldo <= 0)
                break;

            $montoACruzar = min($recibo->saldo_recibo, (float) $comprobante->saldo);

            if ($montoACruzar > 0) {
                // Registrar el cruce
                $link = \App\PedidoRemision::where('cuentacobro_id', $comprobante->id)
                    ->orWhere('remision_id', $comprobante->id)
                    ->first();

                \App\CruceCartera::create([
                    'recibo_pago_id' => $recibo->id,
                    'comprobante_id' => $comprobante->id,
                    'pedido_remision_id' => $link ? $link->id : null,
                    'monto' => $montoACruzar,
                    'fecha_cruce' => date('Y-m-d')
                ]);

                // Actualizar saldos del comprobante actual (CC/Factura)
                $comprobante->abono = ($comprobante->abono ?? 0) + $montoACruzar;
                $comprobante->monto_aplicado_anticipo = ($comprobante->monto_aplicado_anticipo ?? 0) + $montoACruzar;
                $comprobante->saldo = max(0, (float) $comprobante->total - (float) $comprobante->abono);

                if ($comprobante->saldo <= 0) {
                    $comprobante->saldo = 0;
                    $comprobante->estado = 3; // Pagada
                }

                $recibo->saldo_recibo -= $montoACruzar;
                $recibo->save();
            }
        }
        $comprobante->save();

        // Propagar cambios al pedido padre para sincronizar abonos
        $ordController = new \App\Http\Controllers\OrdentrabajoController();
        $ordController->propagarPagoAPedido($comprobante, 0);
    }
    /**
     * Busca recibos de pago con saldo (valor a favor) del cliente
     * y los cruza automáticamente con el nuevo comprobante.
     */
    private function verificarYActualizarPedido($pedidoId)
    {
        $pedido = Comprobante::find($pedidoId);
        if (!$pedido || $pedido->tipo !== 'pedido')
            return;

        // Obtener todas las líneas del pedido que tienen orden de trabajo
        $lineas = LineaComprobante::where('comprobante_id', $pedidoId)
            ->whereNotNull('ordentrabajo_id')
            ->get();

        if ($lineas->isEmpty())
            return;

        $todoEntregado = true;
        foreach ($pedido->lineas as $linea) {
            if ($linea->orden && $linea->orden->cantidad_entregada < $linea->orden->cantidad) {
                $todoEntregado = false;
                break;
            }
        }

        if ($todoEntregado) {
            $pedido->estado = ($pedido->saldo <= 0) ? '4' : '3';
            $pedido->save();

            foreach ($lineas as $linea) {
                if ($linea->orden) {
                    $status = \App\statusProduccion::where('idorden', $linea->orden->id)->first();
                    if ($status) {
                        $status->estado = 'Para entregar';
                        $status->save();
                    }
                }
            }

            // Recalcular saldo real basado en abonos/pagos
            (new \App\Http\Controllers\OrdentrabajoController())->verificarYActualizarPedido($pedido);
        }
    }
}
