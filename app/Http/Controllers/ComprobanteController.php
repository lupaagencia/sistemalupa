<?php

namespace App\Http\Controllers;
use App\Ordentrabajo;
use App\CostoProduccion;
use App\Detalletrabajo;
use App\statusProduccion;
use App\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Comprobante;
use App\Cliente;
use App\LineaComprobante;
use App\CifrasEnLetras;
use App\Ajustes;
use App\Entrega;
use App\InventariosMateriaPrima;
use Exception;
use stdClass;
use App\Http\Controllers\OrdentrabajoController;
use App\Http\Controllers\StatusProduccionController;
use phpDocumentor\Reflection\Types\Self_;
use Barryvdh\DomPDF\Facade\Pdf;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PedidoProduccionMailable;

class ComprobanteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $PRIMER_ESTADO;
    protected $ESTADO_CONTROL;
    protected $ULTIMO_ESTADO;
    protected $ESTADO_CAMBIO;

    public function __construct()
    {
        $this->PRIMER_ESTADO = config('constantes.PRIMER_ESTADO');
        $this->ESTADO_CONTROL = config('constantes.ESTADO_CONTROL');
        $this->ULTIMO_ESTADO = config('constantes.ULTIMO_ESTADO');
        $this->ESTADO_CAMBIO = config('constantes.ESTADO_CAMBIO');
    }
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $per_page = $request->per_page;
        $estadoFiltro = $request->estado_filtro;
        $id = array();

        $query = Comprobante::with([
            'cliente.empresas',
            'cliente.envios',
            'cliente.contactos',
            'lineas.articulo.tipo.atributos',
            'lineas.orden.detalles'
        ])->where('tipo', 'pedido');

        if ($buscar !== '' && $buscar !== null && $buscar !== 'todos' && $buscar !== 0 && $buscar !== '0') {
            if ($criterio == 'cliente_id') {
                $query->where(function($q) use ($buscar) {
                    $q->whereHas('cliente', function($c) use ($buscar) {
                        $c->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    })
                    ->orWhereHas('razonsocial', function($r) use ($buscar) {
                        $r->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    });
                    if (is_numeric($buscar)) {
                        $q->orWhere('comprobantes.cliente_id', $buscar)
                          ->orWhere('comprobantes.num_comprobante', 'LIKE', '%' . $buscar . '%')
                          ->orWhere('comprobantes.id', $buscar);
                    }
                });
            } elseif ($criterio == 'id' || $criterio == 'num_comprobante') {
                $query->where(function($q) use ($buscar) {
                    $q->where('comprobantes.num_comprobante', 'LIKE', '%' . $buscar . '%')
                      ->orWhere('comprobantes.id', 'LIKE', '%' . $buscar . '%');
                });
            } else if ($criterio == 'estado') {
                $query->where('comprobantes.estado', (int) $buscar);
            } else if (!empty($criterio)) {
                $query->where('comprobantes.' . $criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        if ($estadoFiltro !== null && $estadoFiltro !== '') {
            $query->where('estado', (int) $estadoFiltro);
        }

        $comprobantes = $query->orderBy('id', 'desc')->paginate($per_page ?: 50);

        foreach ($comprobantes as $comp) {
            // Recalcular el total real (Subtotal * 1.19 si tiene impuestos registrados)
            // Esto corrige casos donde el campo 'total' en BD solo tiene el valor neto.
            if ((float) $comp->impuestos > 0) {
                $comp->total = (float) $comp->subtotal * 1.19;
            } else if ((float) $comp->total <= 0) {
                $comp->total = (float) $comp->subtotal;
            }

            // Cargar facturas relacionadas (si existen)
            $comp->factura = Comprobante::where('fuente_id', $comp->id)->where('tipo', 'factura')->get();

            // Calcular el total de recibos registrados (directos o cruzados) para saber si se puede migrar abono
            if ((float) $comp->abono > 0) {
                $comp->total_recibos_registrados = max(
                    \App\ReciboPago::where('pedido_id', $comp->id)->sum('monto'),
                    \App\CruceCartera::where('comprobante_id', $comp->id)->sum('monto')
                );
            } else {
                $comp->total_recibos_registrados = 0;
            }

            foreach ($comp->lineas as $linea) {
                // Mapear tipo de articulo
                if ($linea->articulo && $linea->articulo->tipo) {
                    $linea->tipo = $linea->articulo->tipo;
                }

                if ($linea->orden) {
                    // Cargar planchas
                    if (isset($linea->orden->cliente_id)) {
                        $linea->orden->planchas = InventariosMateriaPrima::where('asignado_id', $linea->orden->cliente_id)
                            ->where('tipo', 'Plancha')->get();
                    }

                    // Mapear y procesar detalles
                    if (isset($linea->orden->detalles)) {
                        foreach ($linea->orden->detalles as $det) {
                            if (($det->titulo == 'Tinta' || $det->titulo == 'tinta') && is_string($det->valor)) {
                                $decoded = json_decode($det->valor);
                                if (is_array($decoded)) {
                                    $det->valor = $decoded;
                                }
                            }
                            if (!is_null($det->costos_id) && $det->costos_id != 0) {
                                if (!empty($det->costo)) {
                                    $det->costo->costois;
                                }
                            }
                        }
                        $linea->detalles = $linea->orden->detalles->sortBy('orden')->values();
                    }
                }
            }
        }


        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes,
            'id' => $id,
        ];
    }

    public function cotizaciones(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $per_page = $request->per_page ?: 50;

        $query = Comprobante::with(['cliente', 'lineas.articulo', 'lineas.orden.detalles'])
            ->where('tipo', 'cotizacion');

        if ($buscar != '') {
            if ($criterio == 'cliente_id') {
                $clienteIds = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->pluck('id');
                $query->whereIn('cliente_id', $clienteIds);
            } else if (!empty($criterio)) {
                $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        $comprobantes = $query->orderBy('id', 'desc')->paginate($per_page);

        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes
        ];
    }

    public function convertirAPedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $cotizacion = Comprobante::with('lineas')->findOrFail($request->id);
            if ($cotizacion->tipo != 'cotizacion') {
                return response()->json(['error' => 'El documento no es una cotización'], 400);
            }

            $cotizacion->tipo = 'pedido';
            $cotizacion->estado = 1; // Estado inicial de pedido (Pendiente)
            $cotizacion->save();

            // Garantizar creación/activación de Orden de Trabajo para cada línea del pedido
            foreach ($cotizacion->lineas as $linea) {
                if (!$linea->ordentrabajo_id || !\App\Ordentrabajo::find($linea->ordentrabajo_id)) {
                    $orden = new \App\Ordentrabajo();
                    $orden->total = $linea->valor_total ?? $linea->subtotal;
                    $orden->pago = 0;
                    $orden->prioridad = 0;
                    $orden->impresa = 0;
                    $orden->cliente_id = $cotizacion->cliente_id;
                    $orden->articulo_id = $linea->articulo_id ?? 0;
                    $orden->fecha = $cotizacion->fecha ?? date('Y-m-d');
                    $orden->fecha_entrega = Carbon::parse($orden->fecha)->addDays(12)->format('Y-m-d');
                    $orden->cantidad = $linea->cantidad ?? 1;
                    $orden->valor_unitario = $linea->valor_unitario ?? 0;
                    $orden->descuento = $linea->descuento ?? 0;
                    $orden->impuesto = $linea->impuesto ?? 0;
                    $orden->totalParcial = $linea->subtotal ?? 0;
                    $orden->abono = 0;
                    $orden->saldo = $orden->total;
                    $orden->estado = 'OSP';
                    $orden->produccion = 'ENP'; // En producción inicial
                    $orden->save();

                    // Guardar relación en la línea
                    $linea->ordentrabajo_id = $orden->id;
                    $linea->save();

                    // Generar registros de statusProduccion
                    $status = new \App\statusProduccion();
                    $status->estado = config('constantes.PRIMER_ESTADO') ?? 'Espera';
                    $status->idorden = $orden->id;
                    $status->observaciones = '';
                    $status->prioridad = 0;
                    $status->fecha_termina = date('Y-m-d');
                    $status->hora = date('H:i:s');
                    $status->save();
                } else {
                    $orden = \App\Ordentrabajo::find($linea->ordentrabajo_id);
                    if ($orden) {
                        if ($orden->produccion == 'P' || $orden->produccion == 'NR') {
                            $orden->produccion = 'ENP';
                        }
                        $orden->save();
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'comprobante' => $cotizacion]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error en convertirAPedido: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo convertir la cotización: ' . $e->getMessage()], 500);
        }
    }

    public function facturas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $tipo = $request->tipo;
        $per_page = $request->per_page ?: 50;

        $query = Comprobante::with([
            'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
            'lineas.articulo.tipo.atributos',
            'lineas.orden.detalles.costo.costois',
            'lineas.orden.costos.costois',
            'lineas.orden.status',
            'facturaElectronica'
        ])->whereIn('tipo', ['factura', 'proforma']);

        if (!empty($tipo) && in_array($tipo, ['factura', 'proforma'])) {
            $query->where('comprobantes.tipo', $tipo);
        }

        if ($criterio == 'estado' && ($buscar !== '' && $buscar !== null)) {
            $query->where('comprobantes.estado', (int)$buscar);
        } elseif ($buscar !== '' && $buscar !== null && $buscar !== 0 && $buscar !== '0') {
            if ($criterio == 'cliente_id') {
                $query->where(function($q) use ($buscar) {
                    $q->whereHas('cliente', function($c) use ($buscar) {
                        $c->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    })
                    ->orWhereHas('razonsocial', function($r) use ($buscar) {
                        $r->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    });
                    if (is_numeric($buscar)) {
                        $q->orWhere('comprobantes.cliente_id', $buscar)
                          ->orWhere('comprobantes.num_comprobante', 'LIKE', '%' . $buscar . '%')
                          ->orWhere('comprobantes.id', $buscar);
                    }
                });
            } elseif (!empty($criterio)) {
                $query->where('comprobantes.' . $criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        $estadoFiltro = $request->estado_filtro;
        if ($estadoFiltro !== null && $estadoFiltro !== '') {
            if ((int)$estadoFiltro === 0) {
                $query->where(function($q) {
                    $q->where('comprobantes.estado', 0)->orWhereNull('comprobantes.estado');
                });
            } else {
                $query->where('comprobantes.estado', (int)$estadoFiltro);
            }
        }

        $comprobantes = $query->orderBy('comprobantes.id', 'desc')->paginate($per_page);

        $cantProformas = Comprobante::where('tipo', 'proforma')->count();
        $cantFacturas = Comprobante::where('tipo', 'factura')->count();
        $cantEnviadas = Comprobante::where('tipo', 'proforma')->where('estado', 1)->count();
        $cantNoEnviadas = Comprobante::where('tipo', 'proforma')->where(function($q) {
            $q->where('estado', 0)->orWhereNull('estado');
        })->count();

        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes,
            'cant_proformas' => $cantProformas,
            'cant_facturas' => $cantFacturas,
            'cant_enviadas' => $cantEnviadas,
            'cant_no_enviadas' => $cantNoEnviadas
        ];
    }

    public function resumenProformas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $per_page = $request->per_page ?: 50;

        $baseQuery = Comprobante::where('tipo', 'proforma');

        $totalProformas = (float) (clone $baseQuery)->sum('total');
        $totalNoEnviadas = (float) (clone $baseQuery)->where('estado', 0)->sum('total');
        $totalEnviadas = (float) (clone $baseQuery)->where('estado', 1)->sum('total');
        $saldoTotalProformas = (float) (clone $baseQuery)->sum('saldo');

        $query = Comprobante::with([
            'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
            'lineas.articulo'
        ])->where('tipo', 'proforma');

        if ($buscar != '' && $buscar !== 'todos') {
            if ($criterio == 'estado') {
                $query->where('estado', (int)$buscar);
            } elseif ($criterio == 'cliente_id') {
                if (!is_numeric($buscar)) {
                    $cliente = Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->first();
                    if ($cliente) {
                        $query->where('cliente_id', $cliente->id);
                    }
                } else {
                    $query->where('cliente_id', $buscar);
                }
            } elseif (!empty($criterio)) {
                $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        $comprobantes = $query->orderBy('id', 'desc')->paginate($per_page);

        return response()->json([
            'total_proformas' => $totalProformas,
            'total_no_enviadas' => $totalNoEnviadas,
            'total_enviadas' => $totalEnviadas,
            'saldo_total_proformas' => $saldoTotalProformas,
            'comprobantes' => $comprobantes
        ]);
    }

    public static function sincronizarProformaConPedido($pedidoId, $createIfMissing = true)
    {
        try {
            $pedido = Comprobante::with('lineas')->find($pedidoId);
            if (!$pedido || !in_array($pedido->tipo, ['pedido', 'cotizacion'])) return;

            $proforma = Comprobante::where('tipo', 'proforma')
                ->where(function($q) use ($pedidoId) {
                    $q->where('pedido_id', $pedidoId)->orWhere('fuente_id', (string)$pedidoId);
                })->first();

            if (!$proforma && $createIfMissing) {
                // Obtener número inicial de proforma configurado en Ajustes
                $ajusteInicial = Ajustes::where('tipo', 'consecutivo')
                    ->where('detalle', 'consecutivo_proforma')
                    ->first();
                if (!$ajusteInicial) {
                    $ajusteInicial = Ajustes::where('tipo', 'inicial_proforma')->first();
                }

                $numInicial = $ajusteInicial ? (int)$ajusteInicial->valor : 5242;
                $maxExistente = (int) Comprobante::where('tipo', 'proforma')->max('num_comprobante');
                $siguienteNumero = max($maxExistente + 1, $numInicial);

                $proforma = new Comprobante();
                $proforma->tipo = 'proforma';
                $proforma->num_comprobante = $siguienteNumero;
                $proforma->fuente_id = (string)$pedidoId;
                $proforma->pedido_id = $pedidoId;
                $proforma->datos_factura_id = $pedido->datos_factura_id;
                $proforma->cliente_id = $pedido->cliente_id;
                $proforma->user_id = auth()->id() ?? $pedido->user_id;
                $proforma->fecha = date('Y-m-d');
                $proforma->estado = 0; // No Enviada

                if ($ajusteInicial) {
                    $ajusteInicial->valor = (string) $siguienteNumero;
                    $ajusteInicial->save();
                } else {
                    Ajustes::create([
                        'tipo' => 'consecutivo',
                        'detalle' => 'consecutivo_proforma',
                        'valor' => (string) $siguienteNumero,
                        'categoria' => 'consecutivo'
                    ]);
                }
            }

            if ($proforma) {
                $proforma->subtotal = $pedido->subtotal;
                $proforma->descuento = $pedido->descuento;
                $proforma->total = $pedido->total;
                $proforma->impuestos = $pedido->impuestos;
                $proforma->iva = $pedido->iva;
                $proforma->cliente_id = $pedido->cliente_id;
                $proforma->datos_factura_id = $pedido->datos_factura_id;
                $proforma->forma_pago = $pedido->forma_pago;
                $proforma->transportadora = $pedido->transportadora;
                $proforma->abono = (int) $pedido->abono;
                $proforma->saldo = max(0, round($pedido->total - $pedido->abono, 2));
                $proforma->save();

                LineaComprobante::where('comprobante_id', $proforma->id)->delete();
                foreach ($pedido->lineas as $lineaPedido) {
                    $lineaP = $lineaPedido->replicate();
                    $lineaP->comprobante_id = $proforma->id;
                    $lineaP->ordentrabajo_id = 0;
                    $lineaP->save();
                }
            }
            return $proforma;
        } catch (\Throwable $e) {
            \Log::error("Error al sincronizar proforma con pedido {$pedidoId}: " . $e->getMessage());
            return null;
        }
    }

    public static function sincronizarPedidoConProforma($proformaId)
    {
        try {
            $proforma = Comprobante::find($proformaId);
            if (!$proforma || $proforma->tipo !== 'proforma') return;

            $pedidoId = $proforma->pedido_id ?: $proforma->fuente_id;
            if (!$pedidoId) return;

            $pedido = Comprobante::find($pedidoId);
            if ($pedido) {
                $pedido->abono = $proforma->abono;
                $pedido->saldo = max(0, round($pedido->total - $proforma->abono, 2));
                $pedido->save();
            }
        } catch (\Throwable $e) {}
    }

    public function remisiones(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $per_page = $request->per_page ?? 100;

        $query = Comprobante::with([
            'cliente', 'razonsocial', 'lineas.articulo',
            'lineas.orden.detalles.costo.costois',
            'lineas.orden.costos.costois',
            'lineas.orden.status'
        ])
            ->where('tipo', 'remision');

        if ($buscar != '') {
            if ($criterio == 'cliente_id') {
                $clienteIds = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->pluck('id');
                $query->whereIn('cliente_id', $clienteIds);
            } else if (!empty($criterio)) {
                $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        $comprobantes = $query->orderBy('id', 'desc')->paginate($per_page);

        $remisionIds = $comprobantes->pluck('id')->toArray();
        $links = \App\PedidoRemision::with('cuentacobro')->whereIn('remision_id', $remisionIds)->get()->keyBy('remision_id');

        $comprobantes->getCollection()->transform(function($rem) use ($links) {
            $link = $links->get($rem->id);
            if ($link && $link->cuentacobro && in_array($link->cuentacobro->estado, ['Valida', 'Válida', 'Cerrado', '1'])) {
                $rem->cuentacobro_id = $link->cuentacobro_id;
                $rem->cuentacobro_num = $link->cuentacobro->num_comprobante ?: $link->cuentacobro->id;
            } else {
                $rem->cuentacobro_id = null;
                $rem->cuentacobro_num = null;
            }

            $pId = $link ? $link->pedido_id : $rem->pedido_id;
            if ($pId) {
                $pedidoPadre = \App\Comprobante::find($pId);
                $rem->pedido_num = $pedidoPadre ? ($pedidoPadre->num_comprobante ?: $pedidoPadre->id) : $pId;
            } else {
                $rem->pedido_num = null;
            }

            return $rem;
        });

        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes
        ];
    }

    public function crearCuentaCobroDesdeRemisiones(Request $request)
    {
        $remisionIds = $request->remision_ids;
        if (empty($remisionIds) || !is_array($remisionIds)) {
            return response()->json(['status' => 'error', 'message' => 'Debe seleccionar al menos una remisión.'], 422);
        }

        $remisiones = Comprobante::whereIn('id', $remisionIds)->where('tipo', 'remision')->get();
        if ($remisiones->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No se encontraron las remisiones seleccionadas.'], 404);
        }

        $clienteIds = $remisiones->pluck('cliente_id')->unique();
        if ($clienteIds->count() > 1) {
            return response()->json(['status' => 'error', 'message' => 'Todas las remisiones seleccionadas deben pertenecer al mismo cliente.'], 422);
        }

        foreach ($remisiones as $rem) {
            $ccExistente = \App\PedidoRemision::where('remision_id', $rem->id)
                ->whereNotNull('cuentacobro_id')
                ->whereHas('cuentacobro', function($q) {
                    $q->where('estado', 'Valida');
                })
                ->first();

            if (!$ccExistente) {
                $ccExistente = Comprobante::where('tipo', 'cuentacobro')
                    ->where('estado', 'Valida')
                    ->where(function($q) use ($rem) {
                        $q->where('fuente_id', $rem->id)
                          ->orWhere('fuente_id', 'LIKE', '%'.$rem->id.'%');
                    })
                    ->first();
            }

            if ($ccExistente) {
                $numCC = $ccExistente->cuentacobro ? $ccExistente->cuentacobro->num_comprobante : $ccExistente->num_comprobante;
                return response()->json([
                    'status' => 'error',
                    'message' => 'La remisión #' . ($rem->num_comprobante ?: $rem->id) . ' ya tiene la Cuenta de Cobro #' . $numCC . ' generada.'
                ], 422);
            }
        }

        $primeraRem = $remisiones->first();
        $userId = \Auth::id() ?: 1;

        $ajusteCC = \App\Ajustes::where('tipo', 'consecutivo')->where('detalle', 'consecutivo_cuentacobro')->first();
        if (!$ajusteCC) {
            $ajusteCC = \App\Ajustes::create(['tipo' => 'consecutivo', 'detalle' => 'consecutivo_cuentacobro', 'valor' => 0]);
        }
        $maxExistenteCC = (int) \App\Comprobante::where('tipo', 'cuentacobro')->max('num_comprobante');
        $nuevoValCC = max((int)$ajusteCC->valor + 1, $maxExistenteCC + 1);
        $ajusteCC->valor = $nuevoValCC;
        $ajusteCC->save();

        $subtotalTotal = 0;
        $impuestosTotal = 0;
        $totalGeneral = 0;

        foreach ($remisiones as $rem) {
            $subtotalTotal += (float) $rem->subtotal;
            $impuestosTotal += (float) $rem->impuestos;
            $totalGeneral += (float) $rem->total;
        }

        $cc = new Comprobante();
        $cc->tipo = 'cuentacobro';
        $cc->num_comprobante = $ajusteCC->valor;
        $cc->pedido_id = $primeraRem->pedido_id;
        $cc->cliente_id = $primeraRem->cliente_id;
        $cc->datos_factura_id = $primeraRem->datos_factura_id;
        $cc->fuente_id = $primeraRem->id;
        $cc->user_id = $userId;
        $cc->fecha = date('Y-m-d');
        $cc->subtotal = $subtotalTotal;
        $cc->iva = $primeraRem->iva;
        $cc->impuestos = $impuestosTotal;
        $cc->total = $totalGeneral;
        $cc->abono = 0;
        $cc->saldo = $totalGeneral;
        $cc->estado = 'Valida';
        $cc->save();

        foreach ($remisiones as $rem) {
            foreach (\App\LineaComprobante::where('comprobante_id', $rem->id)->get() as $lRem) {
                $lcCopy = $lRem->replicate();
                $lcCopy->comprobante_id = $cc->id;
                $lcCopy->save();
            }

            $link = \App\PedidoRemision::where('remision_id', $rem->id)->first();
            if ($link) {
                $link->cuentacobro_id = $cc->id;
                $link->save();
            } else {
                $pedId = $rem->pedido_id ?: $rem->getPedidoPadreId();
                if (!$pedId) {
                    $line = \App\LineaComprobante::where('comprobante_id', $rem->id)->whereNotNull('ordentrabajo_id')->first();
                    if ($line) {
                        $pedId = \App\LineaComprobante::where('ordentrabajo_id', $line->ordentrabajo_id)
                            ->whereHas('pedido', function ($q) {
                                $q->where('tipo', 'pedido');
                            })
                            ->value('comprobante_id');
                    }
                }

                if ($pedId) {
                    \App\PedidoRemision::create([
                        'pedido_id' => $pedId,
                        'remision_id' => $rem->id,
                        'cuentacobro_id' => $cc->id
                    ]);
                }
            }
        }

        // Cruzar y aplicar automáticamente abonos del pedido si existen
        $this->sincronizarYAplicarAbonosCuentaCobro($cc);
        $cc = $cc->fresh();

        return response()->json([
            'status' => 'ok',
            'message' => 'Cuenta de Cobro #' . $cc->num_comprobante . ' generada exitosamente.',
            'cuentacobro' => $cc
        ]);
    }

    /**
     * Sincroniza y cruza automáticamente los abonos/anticipos del pedido padre con la cuenta de cobro.
     */
    public function sincronizarYAplicarAbonosCuentaCobro(Comprobante $cc)
    {
        if ($cc->tipo !== 'cuentacobro') {
            return;
        }

        $pedidoIds = $cc->getPedidoPadreIds();
        if (empty($pedidoIds)) {
            return;
        }

        // Ordenar pedidos de más antiguo a más reciente (FIFO: fecha asc, id asc)
        $pedidosOrdenados = Comprobante::whereIn('id', $pedidoIds)
            ->where('tipo', 'pedido')
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalCruzadoActual = (float) \App\CruceCartera::whereHas('reciboPago')->where('comprobante_id', $cc->id)->sum('monto');
        $saldoPendienteCC = max(0, (float) $cc->total - $totalCruzadoActual);

        if ($saldoPendienteCC > 0) {
            foreach ($pedidosOrdenados as $pedidoObj) {
                if ($saldoPendienteCC <= 0) break;

                // Recibos vinculados al pedido
                $recibosDirectos = \App\ReciboPago::where('pedido_id', $pedidoObj->id)->get();

                foreach ($recibosDirectos as $recibo) {
                    if ($saldoPendienteCC <= 0) break;

                    // Calcular cuánto de este recibo se ha cruzado con cualquier comprobante
                    $montoUsadoInRecibo = (float) \App\CruceCartera::whereHas('reciboPago')->where('recibo_pago_id', $recibo->id)->sum('monto');
                    $saldoDisponibleRecibo = max(0, (float)$recibo->monto - $montoUsadoInRecibo);

                    if ($recibo->saldo_recibo > 0) {
                        $saldoDisponibleRecibo = max($saldoDisponibleRecibo, (float)$recibo->saldo_recibo);
                    }

                    if ($saldoDisponibleRecibo > 0) {
                        $montoAplicar = min($saldoPendienteCC, $saldoDisponibleRecibo);

                        $cruceExistente = \App\CruceCartera::where('recibo_pago_id', $recibo->id)
                            ->where('comprobante_id', $cc->id)
                            ->first();

                        if ($cruceExistente) {
                            $cruceExistente->monto = (float)$cruceExistente->monto + $montoAplicar;
                            $cruceExistente->save();
                        } else {
                            \App\CruceCartera::create([
                                'recibo_pago_id' => $recibo->id,
                                'comprobante_id' => $cc->id,
                                'monto' => $montoAplicar,
                                'fecha_cruce' => date('Y-m-d'),
                            ]);
                        }

                        $recibo->saldo_recibo = max(0, (float)$recibo->saldo_recibo - $montoAplicar);
                        $recibo->save();

                        $saldoPendienteCC -= $montoAplicar;
                    }
                }
            }
        }

        $abonoRealVigente = (float) \App\CruceCartera::whereHas('reciboPago')->where('comprobante_id', $cc->id)->sum('monto');
        $cc->abono = round($abonoRealVigente, 2);
        $cc->saldo = max(0, round((float)$cc->total - $cc->abono, 2));
        $cc->save();

        foreach ($pedidoIds as $pid) {
            (new OrdentrabajoController())->verificarYActualizarPedido($pid);
        }
    }

    /**
     * Registra un pago/abono directamente a una Cuenta de Cobro y lo distribuye FIFO a los pedidos conectados (del más antiguo al más reciente).
     */
    public function registrarAbonoCuentaCobro(Request $request)
    {
        $request->validate([
            'cuentacobro_id' => 'required|integer',
            'monto' => 'required|numeric|gt:0',
            'fecha' => 'required|date',
            'forma_pago' => 'required|string',
        ]);

        try {
            \DB::beginTransaction();

            $cc = Comprobante::findOrFail($request->cuentacobro_id);
            if ($cc->tipo !== 'cuentacobro') {
                return response()->json(['status' => 'error', 'message' => 'El comprobante no es una Cuenta de Cobro.'], 422);
            }

            $monto = (float) $request->monto;
            $userId = \Auth::id() ?: 1;
            $pedidoIds = $cc->getPedidoPadreIds();

            // 1. Crear UN ÚNICO ReciboPago por el importe total ingresado por el usuario
            $pago = new \App\ReciboPago();
            $pago->cliente_id = $cc->cliente_id;
            $pago->user_id = $userId;
            $pago->fecha = $request->fecha;
            $pago->monto = $monto;
            $pago->forma_pago = $request->forma_pago;
            $pago->observaciones = $request->observaciones ?: ('Abono a Cuenta de Cobro #' . ($cc->num_comprobante ?: $cc->id));
            $pago->pedido_id = !empty($pedidoIds) ? $pedidoIds[0] : null;

            $ultimoRecibo = \App\ReciboPago::orderBy('id', 'desc')->first();
            $pago->num_recibo = $ultimoRecibo ? $ultimoRecibo->num_recibo + 1 : 1;
            $pago->saldo_recibo = 0;
            $pago->save();

            try {
                (new OrdentrabajoController())->contabilizarRecibo($pago);
            } catch (\Exception $ex) {}

            // 2. Vincular el recibo a la Cuenta de Cobro mediante CruceCartera
            \App\CruceCartera::create([
                'recibo_pago_id' => $pago->id,
                'comprobante_id' => $cc->id,
                'monto' => $monto,
                'fecha_cruce' => $request->fecha,
            ]);

            // 3. Distribuir el monto abonado entre los pedidos conectados de forma FIFO (cubriendo primero el más antiguo)
            if (!empty($pedidoIds)) {
                $pedidosConectados = Comprobante::whereIn('id', $pedidoIds)
                    ->where('tipo', 'pedido')
                    ->orderBy('fecha', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $montoRestante = $monto;
                foreach ($pedidosConectados as $pedidoObj) {
                    if ($montoRestante <= 0) break;

                    $saldoPedido = (float) $pedidoObj->saldo;
                    if ($saldoPedido <= 0) continue;

                    $aplicar = min($montoRestante, $saldoPedido);

                    $pedidoObj->abono = round((float)$pedidoObj->abono + $aplicar, 2);
                    $pedidoObj->saldo = max(0, round((float)$pedidoObj->total - (float)$pedidoObj->abono, 2));
                    $pedidoObj->save();

                    (new OrdentrabajoController())->verificarYActualizarPedido($pedidoObj->id);

                    $montoRestante -= $aplicar;
                }
            }

            // 4. Recalcular abono y saldo de la Cuenta de Cobro
            $totalCruzado = (float) \App\CruceCartera::where('comprobante_id', $cc->id)->sum('monto');
            $cc->abono = round($totalCruzado, 2);
            $cc->saldo = max(0, round((float)$cc->total - $cc->abono, 2));
            if ($cc->saldo <= 0) {
                $cc->saldo = 0;
            }
            $cc->save();

            // Registrar Actividad
            $datosA = new \stdClass();
            $datosA->user_id = $userId;
            $datosA->actividad = 'Abono $' . number_format($monto, 0, ',', '.') . ' registrado a Cuenta de Cobro #' . ($cc->num_comprobante ?: $cc->id);
            (new ActividadController())->store($datosA);

            \DB::commit();

            return response()->json([
                'status' => 'ok',
                'message' => 'Abono de $' . number_format($monto, 0, ',', '.') . ' registrado exitosamente.',
                'cuentacobro' => $cc->fresh(),
                'recibos' => [$pago]
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error en registrarAbonoCuentaCobro: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint para cruzar abonos pendientes del pedido a la cuenta de cobro manualmente.
     */
    public function cruzarAbonosCuentaCobro(Request $request)
    {
        $request->validate(['cuentacobro_id' => 'required|integer']);

        try {
            \DB::beginTransaction();
            $cc = Comprobante::findOrFail($request->cuentacobro_id);
            $this->sincronizarYAplicarAbonosCuentaCobro($cc);
            \DB::commit();

            return response()->json([
                'status' => 'ok',
                'message' => 'Abonos del pedido sincronizados y cruzados correctamente con la Cuenta de Cobro.',
                'cuentacobro' => $cc->fresh()
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function cambiarEstadoCuentaCobro(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'estado' => 'required|string'
        ]);

        $cc = Comprobante::findOrFail($request->id);
        if ($cc->tipo !== 'cuentacobro') {
            return response()->json(['status' => 'error', 'message' => 'El comprobante no es una Cuenta de Cobro.'], 422);
        }

        $cc->estado = $request->estado;
        $cc->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Estado de la Cuenta de Cobro #' . ($cc->num_comprobante ?: $cc->id) . ' actualizado a "' . $cc->estado . '".',
            'comprobante' => $cc
        ]);
    }

    public function crearProformaDesdePedido(Request $request)
    {
        $pedidoId = $request->pedido_id;
        $pedido = Comprobante::find($pedidoId);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        $proforma = self::sincronizarProformaConPedido($pedidoId, true);

        return response()->json([
            'status' => 'success',
            'message' => 'Proforma #' . ($proforma->num_comprobante ?: $proforma->id) . ' creada y sincronizada exitosamente.',
            'proforma' => $proforma,
            'num_comprobante' => $proforma->num_comprobante ?: $proforma->id,
            'pdf_url' => '/imprimirProforma?id=' . $proforma->id
        ]);
    }

    public function cuentasCobro(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $tipoDoc = $request->tipo_doc;
        $per_page = $request->per_page ?? 100;

        $query = Comprobante::with([
            'cliente', 'razonsocial', 'lineas.articulo',
            'lineas.orden.detalles.costo.costois',
            'lineas.orden.costos.costois',
            'lineas.orden.status'
        ]);

        if (!empty($tipoDoc) && in_array($tipoDoc, ['cuentacobro', 'proforma'])) {
            $query->where('tipo', $tipoDoc);
        } else {
            $query->whereIn('tipo', ['cuentacobro', 'proforma']);
        }

        if ($buscar !== '' && $buscar !== null && $buscar !== 0 && $buscar !== '0') {
            if ($criterio == 'cliente_id') {
                $query->where(function($q) use ($buscar) {
                    $q->whereHas('cliente', function($c) use ($buscar) {
                        $c->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    })
                    ->orWhereHas('razonsocial', function($r) use ($buscar) {
                        $r->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    });
                    if (is_numeric($buscar)) {
                        $q->orWhere('comprobantes.cliente_id', $buscar)
                          ->orWhere('comprobantes.num_comprobante', 'LIKE', '%' . $buscar . '%')
                          ->orWhere('comprobantes.id', $buscar);
                    }
                });
            } elseif ($criterio == 'id' || $criterio == 'num_comprobante') {
                $query->where(function($q) use ($buscar) {
                    $q->where('comprobantes.num_comprobante', 'LIKE', '%' . $buscar . '%')
                      ->orWhere('comprobantes.id', 'LIKE', '%' . $buscar . '%');
                });
            } else if (!empty($criterio)) {
                $query->where('comprobantes.' . $criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        $comprobantes = $query->orderBy('id', 'desc')->paginate($per_page);

        // Sincronizar y adjuntar información de los pedidos padres y remisiones para cada comprobante
        foreach ($comprobantes->items() as $item) {
            if ($item->tipo === 'cuentacobro') {
                $this->sincronizarYAplicarAbonosCuentaCobro($item);

                // Obtener todos los pedidos vinculados
                $pedidoIds = $item->getPedidoPadreIds();
                $item->pedido_id_padre = !empty($pedidoIds) ? reset($pedidoIds) : $item->pedido_id;

                $pedObjs = !empty($pedidoIds) ? \App\Comprobante::whereIn('id', $pedidoIds)->get() : collect();
                $pedNums = $pedObjs->map(function($p) {
                    return '#' . ($p->num_comprobante ?: $p->id);
                })->toArray();

                if (count($pedNums) === 1) {
                    $item->pedidos_texto = 'Pedido ' . $pedNums[0];
                } else if (count($pedNums) > 1) {
                    $item->pedidos_texto = 'Pedidos ' . implode(', ', $pedNums);
                } else {
                    $item->pedidos_texto = null;
                }

                // Obtener las remisiones anexadas
                $remisionIds = \App\PedidoRemision::where('cuentacobro_id', $item->id)->pluck('remision_id')->filter()->toArray();
                $numsRem = !empty($remisionIds) ? \App\Comprobante::whereIn('id', $remisionIds)->pluck('num_comprobante')->toArray() : [];
                $item->remisiones_texto = !empty($numsRem) ? 'Remisión(es) #' . implode(', #', $numsRem) : null;
            } else if ($item->tipo === 'proforma') {
                $item->saldo = floatval($item->total) - floatval($item->abono);
                $item->pedidos_texto = $item->pedido_id ? ('Pedido #' . $item->pedido_id) : ('Proforma #' . ($item->num_comprobante ?: $item->id));
                $item->remisiones_texto = null;
            }
        }

        $cantCuentas = Comprobante::where('tipo', 'cuentacobro')->count();
        $cantProformas = Comprobante::where('tipo', 'proforma')->count();

        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes,
            'cant_cuentas' => $cantCuentas,
            'cant_proformas' => $cantProformas
        ];
    }

    public function pedidosEntregar(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $id = array();
        if ($buscar == '') {
            $comprobantes = Comprobante::with([
                'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
                'lineas.articulo.tipo.atributos',
                'lineas.orden.detalles.costo.costois',
                'lineas.orden.costos.costois',
                'lineas.orden.status'
            ])->where('estado', 4)->where('tipo', 'pedido')
                ->orderBy('comprobantes.id', 'desc')->paginate(50);
        } else {
            if ($criterio == 'cliente_id') {
                $cliente = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->get();
                if (count($cliente) > 0) {
                    $buscar = $cliente[0]->id;
                }
            }
            if (!empty($criterio)) {
                $comprobantes = Comprobante::with([
                    'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
                    'lineas.articulo.tipo.atributos',
                    'lineas.orden.detalles.costo.costois',
                    'lineas.orden.costos.costois',
                    'lineas.orden.status'
                ])->where($criterio, $buscar)->where('estado', 4)->where('tipo', 'pedido')
                    ->orderBy('comprobantes.id', 'desc')->paginate(50);
            } else {
                $comprobantes = Comprobante::with([
                    'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
                    'lineas.articulo.tipo.atributos',
                    'lineas.orden.detalles.costo.costois',
                    'lineas.orden.costos.costois',
                    'lineas.orden.status'
                ])->where('estado', 4)->where('tipo', 'pedido')
                    ->orderBy('comprobantes.id', 'desc')->paginate(50);
            }
        }

        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes,
            'id' => $id,

        ];
    }
    public function pedidosCompletados(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $ultimoEstado = config('constantes.ULTIMO_ESTADO');
        $id = array();
        if ($buscar == '') {
            $comprobantes = Comprobante::with([
                'lineas.articulo',
                'lineas.orden.status',
                'lineas.orden.detalles.costo.costois',
                'lineas.orden.costos.costois',
                'cliente.empresas', 'cliente.envios', 'cliente.contactos'
            ])
                ->where('tipo', 'pedido')
                ->where('estado', 3)
                ->where('saldo', '>', 0)
                ->whereHas('lineas.orden.status', function ($q) use ($ultimoEstado) {
                    $q->where('estado', $ultimoEstado);
                })
                ->whereDoesntHave('lineas.orden.status', function ($q) use ($ultimoEstado) {
                    $q->where('estado', '!=', $ultimoEstado);
                })
                ->orderBy('comprobantes.id', 'desc')->paginate(50);
        } else {
            if ($criterio == 'cliente_id') {
                $cliente = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->get();
                if (count($cliente) > 0) {
                    $buscar = $cliente[0]->id;
                }
            }

            $query = Comprobante::with([
                'lineas.articulo',
                'lineas.orden.status',
                'lineas.orden.detalles.costo.costois',
                'lineas.orden.costos.costois',
                'cliente.empresas', 'cliente.envios', 'cliente.contactos'
            ])
                ->where('tipo', 'pedido')
                ->where('estado', 3)
                ->where('saldo', '>', 0)
                ->whereHas('lineas.orden.status', function ($q) use ($ultimoEstado) {
                    $q->where('estado', $ultimoEstado);
                })
                ->whereDoesntHave('lineas.orden.status', function ($q) use ($ultimoEstado) {
                    $q->where('estado', '!=', $ultimoEstado);
                });

            if (!empty($criterio)) {
                $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            }

            $comprobantes = $query->orderBy('comprobantes.id', 'desc')->paginate(50);
        }
        
        return [
            'pagination' => [
                'total' => $comprobantes->total(),
                'current_page' => $comprobantes->currentPage(),
                'per_page' => $comprobantes->perPage(),
                'last_page' => $comprobantes->lastPage(),
                'from' => $comprobantes->firstItem(),
                'to' => $comprobantes->lastItem(),
            ],
            'comprobantes' => $comprobantes,
            'id' => $id,

        ];
    }
    public function eliminarLinea(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar ítems o líneas.'], 403);
        }
        $id = $request->id;
        $borrarorden = $request->borrarorden;
        $idorden = $request->idorden;
        $cambios = $request->cambios;
        $linea = LineaComprobante::find($id);
        $linea->delete();

        if ($orden = Ordentrabajo::find($idorden)) {
            if ($borrarorden) {
                $orden->delete();
            } else {
                $orden->estado = 'OSP';
                $orden->save();
            }
        }

        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = $cambios;
        $actividad = new ActividadController();
        $actividad->store($datosA);
    }
    /**
     * Elimina un comprobante (Cuenta de Cobro, Remisión o Pedido) limpiando sus relaciones y recalculando saldos.
     */
    public function eliminarComprobante(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar comprobantes.'], 403);
        }
        $id = $request->id ?: $request->input('id');
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID de comprobante no especificado.'], 400);
        }

        try {
            \DB::beginTransaction();

            $comprobante = Comprobante::find($id);
            if (!$comprobante) {
                return response()->json(['status' => 'error', 'message' => 'No se encontró el comprobante.'], 404);
            }

            $tipo = $comprobante->tipo;
            $userId = $request->user_id ?: (\Auth::id() ?: 1);
            $pedidoIds = $comprobante->getPedidoPadreIds();

            // 1. Revertir cruces de cartera asociados
            $cruces = \App\CruceCartera::where('comprobante_id', $comprobante->id)->get();
            foreach ($cruces as $cruce) {
                $recibo = \App\ReciboPago::find($cruce->recibo_pago_id);
                if ($recibo) {
                    $recibo->saldo_recibo = (float)$recibo->saldo_recibo + (float)$cruce->monto;
                    $recibo->save();
                }
                $cruce->delete();
            }

            // 2. Manejo según el tipo de comprobante
            if ($tipo === 'cuentacobro') {
                \App\PedidoRemision::where('cuentacobro_id', $comprobante->id)->update(['cuentacobro_id' => null]);
                \App\Entrega::where('cuentacobro_id', $comprobante->id)->update(['cuentacobro_id' => null]);
                \App\LineaComprobante::where('comprobante_id', $comprobante->id)->delete();
                $comprobante->delete();
            } else if ($tipo === 'remision') {
                // Si es remisión, borrar la CC que haya sido creada a partir de esta remisión
                $cc = Comprobante::where('tipo', 'cuentacobro')->where('fuente_id', $comprobante->id)->first();
                if ($cc) {
                    \App\CruceCartera::where('comprobante_id', $cc->id)->delete();
                    \App\LineaComprobante::where('comprobante_id', $cc->id)->delete();
                    \App\PedidoRemision::where('cuentacobro_id', $cc->id)->update(['cuentacobro_id' => null]);
                    $cc->delete();
                }

                // Revertir cantidades entregadas en ordentrabajos
                $lineas = \App\LineaComprobante::where('comprobante_id', $comprobante->id)->get();
                foreach ($lineas as $linea) {
                    $orden = \App\Ordentrabajo::find($linea->ordentrabajo_id);
                    if ($orden) {
                        $orden->cantidad_entregada = max(0, (int) $orden->cantidad_entregada - (int) $linea->cantidad);
                        if ($orden->cantidad_original && $orden->cantidad_entregada <= $orden->cantidad_original) {
                            $orden->cantidad = $orden->cantidad_original;
                        }
                        if ($orden->cantidad_entregada < $orden->cantidad) {
                            $orden->produccion = 'E';
                            $statusOrd = \App\statusProduccion::where('idorden', $orden->id)->first();
                            if ($statusOrd) {
                                $statusOrd->estado = config('constantes.ESTADO_CAMBIO');
                                $statusOrd->save();
                            }
                        }
                        $orden->save();
                    }
                }

                \App\PedidoRemision::where('remision_id', $comprobante->id)->delete();
                \App\Entrega::where('comprobante_id', $comprobante->id)->delete();
                \App\LineaComprobante::where('comprobante_id', $comprobante->id)->delete();
                $comprobante->delete();
            } else {
                // Caso pedido
                $lineas = \App\LineaComprobante::where('comprobante_id', $id)->get();
                foreach ($lineas as $linea) {
                    if (isset($linea->orden)) {
                        $linea->orden->delete();
                    }
                }
                $comprobante->delete();
            }

            // 3. Recalcular y actualizar saldos y estados de los pedidos involucrados
            foreach ($pedidoIds as $pid) {
                (new OrdentrabajoController())->verificarYActualizarPedido($pid);
            }

            // 4. Registrar actividad
            $datosA = new \stdClass();
            $datosA->user_id = $userId;
            $datosA->actividad = 'Se eliminó ' . ucfirst($tipo) . ' #' . ($comprobante->num_comprobante ?: $comprobante->id);
            (new ActividadController())->store($datosA);

            \DB::commit();

            return response()->json(['status' => 'ok', 'message' => ucfirst($tipo) . ' eliminada correctamente.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error en eliminarComprobante: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    public function eliminar(Request $request)
    {
        return $this->eliminarComprobante($request);
    }
    public function eliminarFactura(Request $request)
    {
        $id = $request->id;
        $comprobante = Comprobante::find($id);
        $comprobante->delete();
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Se elimino Factura #' . $id;
        $actividad = new ActividadController();
        $actividad->store($datosA);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cambiarEstado(Request $request)
    {
        $comprobante = Comprobante::find($request->id);
        $estadoAnterior = $comprobante->estado;

        $nuevoEstado = $request->estado;
        if (($nuevoEstado == 3 || $nuevoEstado == 4 || $nuevoEstado == 5 || $nuevoEstado == 6) && ($comprobante->tipo == 'pedido')) {
            // Regla de saldo para estados finales de pedido
            if ($comprobante->saldo > 0) {
                if ($request->estado == 4 || $request->estado == 5 || $request->estado == 6) {
                    $nuevoEstado = $request->estado; // Permitir pasar a para entregar (4), entregado (5) o no recogido (6) aunque haya saldo
                } else {
                    $nuevoEstado = 3; // Completado (con deuda)
                }
            } else {
                if ($request->estado == 6) {
                    $nuevoEstado = 6;
                } else if ($request->estado >= 5) {
                    $nuevoEstado = 5; // Entregado final
                } else {
                    $nuevoEstado = 4; // Para entregar (paid but needs confirmation in list)
                }
            }
        }

        $comprobante->estado = $nuevoEstado;
        $comprobante->save();

        if ($nuevoEstado == 2 && $estadoAnterior != 2 && $comprobante->tipo == 'pedido') {
            try {
                self::enviarNotificacionVenta($comprobante);
            } catch (\Exception $e) {
                \Log::error("Error al procesar notificaciones de venta: " . $e->getMessage());
            }
        }
        $comprobante->lineas;
        foreach ($comprobante->lineas as $linea) {
            if (!$linea->orden)
                continue;
            $status = statusProduccion::where('idorden', $linea->orden->id)->get();
            if ($request->estado == 2) {
                $linea->orden->produccion = 'ENP';
                $linea->orden->estado = 'VC';
                $status[0]->estado = $this->PRIMER_ESTADO;
            } elseif ($request->estado == 1 || $request->estado == 0) {
                $linea->orden->estado = 'PA';
                $linea->orden->produccion = 'P';
                if (isset($status[0])) {
                    $status[0]->estado = 'Pendiente';
                }
            } elseif ($request->estado == 6) {
                if ($linea->orden) {
                    $linea->orden->estado = 'NR';
                    $linea->orden->produccion = 'NR';
                }
                if (isset($status[0])) {
                    $status[0]->estado = 'No recogido';
                }
            } elseif ($request->estado == 3 || $request->estado == 5) {
                // Ajustar cantidades al finalizar pedido si hay diferencias
                if ($linea->orden && $linea->orden->cantidad_entregada < $linea->orden->cantidad) {
                    if (!$linea->orden->cantidad_original) {
                        $linea->orden->cantidad_original = $linea->orden->cantidad;
                    }
                    $linea->orden->cantidad = $linea->orden->cantidad_entregada;
                    $linea->cantidad = $linea->orden->cantidad;
                    $linea->subtotal = $linea->cantidad * $linea->valor_unitario;
                    $linea->valor_total = $linea->subtotal;
                    $linea->save();
                }
                if ($linea->orden) {
                    $linea->orden->produccion = 'T';
                    if (isset($status[0])) {
                        $status[0]->estado = 'Para entregar';
                    }
                }
            } else {
                if ($linea->orden) {
                    $linea->orden->estado = 'VC';
                    $linea->orden->produccion = 'E';
                }
                if (isset($status[0])) {
                    $status[0]->estado = $this->ULTIMO_ESTADO;
                }
            }
            if ($linea->orden) {
                $linea->orden->save();
            }
            if (isset($status[0])) {
                $status[0]->save();
            }
        }

        // Recalcular el total del comprobante por si hubo ajustes de cantidad
        if ($request->estado == 3 || $request->estado == 5) {
            $nuevoSubtotal = \App\LineaComprobante::where('comprobante_id', $comprobante->id)->sum('valor_total');
            $comprobante->subtotal = $nuevoSubtotal;
            $tasaIva = (float) ($comprobante->iva ?? 0.19);
            $comprobante->impuestos = round($nuevoSubtotal * $tasaIva, 2);
            $comprobante->total = $comprobante->subtotal + $comprobante->impuestos;
            $comprobante->saldo = max(0, $comprobante->total - ($comprobante->abono ?? 0));
            $comprobante->save();
        }


        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Se cambio a estado del pedido a ' . $request->estado . ' comprobante #' . $comprobante->id;
        $actividad = new ActividadController();
        $actividad->store($datosA);
        return $comprobante;
    }
    public function cambiarEstadoFactura(Request $request)
    {
        $comprobante = Comprobante::find($request->id);
        $comprobante->estado = $request->estado;
        if ($request->estado == 0) {
            $estado = 'No enviada';
        } else {
            $estado = 'Enviada';
        }
        $comprobante->save();
        $comprobante->lineas;
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Se cambio a estado de la factura # ' . $comprobante->id . ' a estado ' . $estado;
        $actividad = new ActividadController();
        $actividad->store($datosA);
        return $comprobante;
    }
    public static function duplicarOrden($id, $cantidad, $detalles)
    {
        $orden = Ordentrabajo::find($id);
        $orden->estado = 'VC';
        $orden->produccion = 'A';
        $orden->impresa = 0;
        $orden->fecha = date('Y-m-d');
        $date = date('Y-m-d');
        $mod_date = strtotime($date . "12 days");
        $fecha_entrega = date('Y-m-d', $mod_date);
        $orden->fecha_entrega = $fecha_entrega;
        $orden->created_at = date('Y-m-d');
        $newOrden = $orden->replicate();
        $newOrden->cantidad = $cantidad;
        $newOrden->created_at = Carbon::now();
        $newOrden->save();

        foreach ($detalles as $det) {
            $detalle = new Detalletrabajo();
            $detalle->titulo = $det->titulo;
            $detalle->valor = $det->valor;
            $detalle->ordentrabajo_id = $newOrden->id;
            $detalle->save();
        }

        $procesos = [
            ['proceso' => 'Corte material', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Impresión', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Transito', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Acabado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Troquelado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Terminado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Empacado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
            ['proceso' => 'Para entregar', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')]
        ];
        $procesos = json_encode($procesos);
        $status = new statusProduccion();
        $status->estado = config('constantes.PRIMER_ESTADO');
        $status->idorden = $newOrden->id;
        $status->observaciones = '';
        $status->prioridad = 0;
        $status->fecha_termina = date('Y-m-d');
        $status->hora = date('H:i:s');
        $status->save();

        return $newOrden;
    }
    public function crearPedidos(Request $request)
    {
        $ordenes = Ordentrabajo::all();
        foreach ($ordenes as $orden) {
            $pedi = new Comprobante();
            $pedi->tipo = 'pedido';
            $pedi->cliente_id = $orden->cliente_id;
            $pedi->user_id = 2;
            $pedi->fecha = $orden->fecha;
            $pedi->forma_pago = 'Contado';
            $pedi->transportadora = 'transprensa';
            $pedi->fuente_id = 0;
            $pedi->datos_factura_id = 0;
            $pedi->estado = 5;
            $pedi->subtotal = $orden->totalParcial;
            $pedi->descuento = $orden->descuento;
            $pedi->total = $orden->total;
            $pedi->impuestos = 0;
            $pedi->abono = $orden->abono;
            $pedi->saldo = $orden->saldo;
            $pedi->save();

            $linea = new LineaComprobante();
            $linea->comprobante_id = $pedi->id;
            $linea->ordentrabajo_id = $orden->id;
            $linea->articulo_id = $orden->articulo_id;
            $linea->fecha = $orden->fecha;
            $linea->impuesto = 0;
            $linea->fecha_entrega = $orden->fecha_entrega;
            $linea->descuento = $orden->descuento;
            $linea->cantidad = $orden->cantidad;
            $linea->valor_unitario = $orden->valor_unitario;
            $linea->subtotal = $orden->totalParcial;
            $linea->valor_total = $orden->total;
            $linea->estado = 0;
            $linea->save();



        }
    }
    public static function getNextDate($origin_date, $daysToAdd)
    {
        $date = new \DateTime($origin_date);
        $date->modify('+' . $daysToAdd . ' days');
        return $date->format('Y-m-d');
    }
    public static function crearOrden($request, $pedido)
    {
        // if (!$request->ajax()) return redirect('/');
        try {

            $mytime = Carbon::now('America/Bogota');
            $orden = new Ordentrabajo();
            $orden->total = $request->valor_total;

            if ($orden->total == 0) {
                $orden->pago = 1;
            } else {
                $orden->pago = 0;
            }

            $orden->prioridad = $request->orden->prioridad ?? 0;
            $orden->impresa = 0;
            $orden->cliente_id = $pedido->cliente_id;
            $orden->articulo_id = $request->articulo_id;

            $articuloId = $request->articulo_id ?? ($request->articulo->id ?? null);
            $articulo = (isset($request->articulo) && is_object($request->articulo) && isset($request->articulo->id)) 
                ? $request->articulo 
                : ($articuloId ? Articulo::find($articuloId) : null);

            $date = date('Y-m-d');
            $mod_date = strtotime($date . "12 days");
            $orden->fecha_entrega = Carbon::parse($pedido->fecha)->addDays(12);
            $fecha_entrega = date('Y-m-d', $mod_date);
            $orden->detalles_diseno = !empty($request->orden->detalles_diseno) ? $request->orden->detalles_diseno : null;
            $orden->fecha = $pedido->fecha;
            $orden->observaciones = !empty($request->orden->observaciones) ? $request->orden->observaciones : null;
            $orden->cantidad = $request->cantidad;

            $medidaFinalReq = !empty($request->medida_final) ? $request->medida_final : (!empty($request->orden->medida_final) ? $request->orden->medida_final : null);
            $orden->medida_final = !empty($medidaFinalReq) ? $medidaFinalReq : (($articulo && !empty($articulo->medida_final)) ? $articulo->medida_final : null);
            
            $tamanoVal = $request->tamano ?? $request->orden->tamano ?? null;
            $orden->tamano = (isset($tamanoVal) && is_numeric($tamanoVal)) ? $tamanoVal : null;

            // Automatic capacity, plate lookup, and material sizing
            $analisis = \App\Http\Controllers\OrdentrabajoController::analizarCabidaYPlancha($articulo, $pedido->cliente_id, $request->cantidad);
            $planchaVal = $analisis['plancha_id'] ?: ($request->orden->plancha ?? null);
            $orden->plancha = (isset($planchaVal) && is_numeric($planchaVal)) ? $planchaVal : null;

            $cabidaVal = $analisis['cabida'] ?: ($request->cabida ?? $request->orden->cabida ?? null);
            $orden->cabida = (isset($cabidaVal) && is_numeric($cabidaVal)) ? $cabidaVal : null;

            $orden->medida_material = $analisis['medida_material'] ?: (!empty($request->medida_material) ? $request->medida_material : (!empty($request->orden->medida_material) ? $request->orden->medida_material : null));

            // Automatic surplus (sobrante / carpeta_cliente) calculation
            $sobrante = \App\Http\Controllers\OrdentrabajoController::calcularSobrante($request->cantidad, $orden->cabida, $request);
            $sobranteVal = $sobrante ?: ($request->orden->carpeta_cliente ?? null);
            $orden->carpeta_cliente = (isset($sobranteVal) && is_numeric($sobranteVal)) ? $sobranteVal : null;
            $orden->valor_unitario = $request->valor_unitario;
            $orden->descuento = (integer) $request->descuento;
            $orden->abono = 0;
            $orden->saldo = $request->valor_total;
            $orden->impuesto = 0;
            $orden->totalParcial = $request->subtotal;
            $orden->total = $request->valor_total;
            if ($pedido->estado != 1) {
                $orden->estado = 'VC';
            } else {
                $orden->estado = 'PA';
            }
            $orden->produccion = $request->orden->produccion ?? 'P';
            $orden->save();
            if (isset($request->orden->detalles)) {
                $costos = isset($request->orden->costos) ? $request->orden->costos : [];
                $detalles = self::crearCostosDetalles($request->orden->detalles, $costos, $orden, '', $pedido->user_id);
            }
            $status = new statusProduccion();
            $procesos = [
                ['proceso' => 'Corte material', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Impresión', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Transito', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Acabado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Troquelado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Terminado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Empacado', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
                ['proceso' => 'Para entregar', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')]
            ];
            $procesos = json_encode($procesos);
            $status->estado = config('constantes.PRIMER_ESTADO');
            $status->idorden = $orden->id;
            $status->observaciones = '';
            $status->prioridad = 0;
            $status->fecha_termina = date('Y-m-d');
            $status->hora = date('H:i:s');
            $status->save();


        } catch (Exception $e) {
            \Log::error('Error al crear la orden: ' . $e->getMessage());
        }

        return $orden;
    }
    public static function eliminarCosto($costo)
    {

    }

    public static function crearCostosDetalles($detalles, $costos, $orden, $cambios_extra, $user_id)
    {
        $cambiosCostos = '';
        $cambiosDetalles = '';
        $detalleNuevo = '';
        $costoNuevo = '';
        $costo_id = 0;
        $cambios = 'Se actualizo la orden #' . $orden->id;
        if (!empty($cambios_extra)) {
            $cambios .= ', Cambios: ' . $cambios_extra;
        }
        foreach ($detalles as $index => $det) {
            // Process cost if provided, otherwise preserve existing ID
            if (isset($det->costo) && is_object($det->costo)) {
                if ($det->costos_id == 0) {
                    $costo = new CostoProduccion();
                    $costo->ordentrabajo_id = $orden->id;
                    $costoNuevo .= 'Costo: ' . $det->costo->titulo . ', Cantidad: ' . $det->costo->cantidad . ', Tamaño: ' . $det->costo->descripcion . ', Valor unitario: ' . $det->costo->valor;
                } else {
                    $costo = CostoProduccion::findOrFail($det->costos_id);
                    $costo->costois_id = $det->costo->costois_id;
                    $costo->costois;

                    $cambiosCostos .= ($costo->cantidad != $det->costo->cantidad) ? 'Costo ' . $costo->titulo . '-> Cantidad: ' . $det->costo->cantidad . ', ' : '';
                }
                $costo->costois_id = $det->costo->costois_id;
                $costo->titulo = $det->costo->titulo;
                $costo->descripcion = $det->costo->descripcion;
                $costo->cantidad = $det->costo->cantidad;
                $costo->orden = $det->costo->orden;
                $costo->completado = $det->costo->completado;
                $costo->fecha_termina = date('Y-m-d');
                $costo->terminado = $det->costo->terminado;
                $costo->valor = $det->costo->valor;
                $costo->pago = 0;
                $costo->total = $det->costo->cantidad * $det->costo->valor;
                $costo->save();
                $costo_id = $costo->id;
            } else {
                $costo_id = $det->costos_id ?? 0;
            }

            // Protection: preserve existing cost assignment if incoming is 0 for an existing row
            if ($costo_id == 0 && isset($det->id) && $det->id != 0) {
                $existingDet = Detalletrabajo::find($det->id);
                if ($existingDet && $existingDet->costos_id != 0) {
                    $costo_id = $existingDet->costos_id;
                }
            }
            $valor = '';
            if (is_array($det->valor)) {
                foreach ($det->valor as $val) {
                    $valor .= isset($val->pantone) ? $val->pantone : '';
                }
            } else {
                $valor = $det->valor;
            }
            if (!isset($det->id) || $det->id == 0) {
                $detalle = new Detalletrabajo();
                $detalle->ordentrabajo_id = $orden->id;
                $detalleNuevo .= 'Titulo: ' . $det->titulo . ', Valor: ' . $valor . ', Descripcion: ' . $det->descripcion;
            } else {
                $detalle = Detalletrabajo::find($det->id);
                if (!$detalle) {
                    $detalle = new Detalletrabajo();
                    $detalle->ordentrabajo_id = $orden->id;
                } else {
                    $cambiosDetalles .= ($detalle->descripcion != $det->descripcion) ? 'Detalle ' . $det->titulo . '-> Descripcion: ' . $det->descripcion . ', ' : '';
                    $cambiosDetalles .= ($detalle->valor != $det->valor) ? 'Detalle ' . $det->titulo . '-> Valor: ' . $valor . ', ' : '';
                    $cambiosDetalles .= ($detalle->titulo != $det->titulo) ? 'Detalle ' . $det->titulo . '-> Titulo: ' . $det->titulo . ', ' : '';
                }
            }
            $detalle->costos_id = $costo_id;
            $detalle->titulo = $det->titulo;
            if (is_array($det->valor)) {

                $detalle->valor = json_encode($det->valor);
            } else {
                $detalle->valor = $det->valor;

            }
            $detalle->descripcion = $det->descripcion;
            $detalle->orden = $index + 1;
            $detalle->save();

        }

        if (!empty($costoNuevo)) {
            $cambios .= ', Nuevos costos: ' . $costoNuevo;
        }

        if (!empty($detalleNuevo)) {
            $cambios .= ', Nuevos detalles: ' . $detalleNuevo;
        }
        if (!empty($cambiosDetalles)) {
            $cambios .= ', Cambios en detalles: ' . $cambiosDetalles;
        }

        if (!empty($cambiosCostos)) {
            $cambios .= ', Cambios costos: ' . $cambiosCostos;
        }
        // $linea=LineaComprobante::where('ordentrabajo_id', $orden->id)->get();
        // return $linea;
        // $linea->articulo_id=$orden->articulo_id;
        // $linea->save();
        $datosA = new stdClass();
        $datosA->user_id = $user_id;
        $datosA->actividad = $cambios;
        $actividad = new ActividadController();
        $actividad->store($datosA);
        // DB::commit(); // Movido al llamador
        return true;
    }
    public function generarFactura(Request $request)
    {
        $f = Comprobante::where('tipo', 'factura')->orderBy('num_comprobante', 'desc')->take(1)->get();
        if (count($f) > 0) {
            $numcomprobante = $f[0]->num_comprobante + 1;
        } else {
            $ajuste = Ajustes::where('tipo', 'inicial_factura')->get();
            $numcomprobante = $ajuste[0]->valor;
        }
        $factura = json_decode($request->data);
        $edit = $request->edit;
        $fact = Comprobante::where('fuente_id', $factura->id)->get();
        if (count($fact) == 0) {
            $fact = new Comprobante();
        } else {
            return $fact[0];
        }
        $fact->tipo = 'factura';
        $fact->num_comprobante = $numcomprobante;
        $fact->fuente_id = $factura->id;
        $fact->datos_factura_id = $factura->empresa->id;
        $fact->cliente_id = $factura->cliente->id;
        $fact->user_id = $factura->user_id;
        $fact->fecha = date('Y-m-d');
        $fact->forma_pago = $factura->forma_pago;
        $fact->transportadora = $factura->transportadora;
        $fact->subtotal = $factura->subtotal;
        $fact->descuento = $factura->descuento;
        $fact->total = $factura->total;
        $fact->abono = (integer) $factura->abono;
        $fact->saldo = $factura->saldo;
        $fact->estado = 0;
        $fact->impuestos = $factura->impuestos;
        $fact->iva = $factura->iva;
        $fact->save();


        return $fact;

    }
    public function crearPedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = json_decode($request->data);
            $edit = $request->edit;
            $estadoAnterior = null;
            if ($edit == 1) {
                $pedi = Comprobante::find($pedido->id);
                if ($pedi) {
                    $estadoAnterior = $pedi->estado;
                }
            } else {
                $pedi = new Comprobante();
            }

            $pedi->tipo = 'pedido';
            $pedi->cliente_id = $pedido->cliente->id;
            $pedi->user_id = $pedido->user_id;
            $pedi->fecha = $pedido->fecha;
            $pedi->forma_pago = $pedido->forma_pago;
            $pedi->transportadora = $pedido->transportadora;
            $pedi->fuente_id = 0;
            $pedi->datos_factura_id = 0;
            $pedi->subtotal = $pedido->subtotal;
            $pedi->descuento = $pedido->descuento;
            $pedi->total = $pedido->total;
            $pedi->abono = (integer) ($pedido->abono ?? 0);
            $pedi->saldo = $pedi->total - $pedi->abono;
            if ($pedido->saldo == 0 && $pedido->estado == 3) {
                $pedi->estado = 4;
            } elseif ($pedido->saldo > 0) {
                $pedi->estado = $pedido->estado;
            }
            $pedi->impuestos = (isset($pedido->impuestos) && is_numeric($pedido->impuestos)) ? $pedido->impuestos : 0;
            $pedi->fuente_id = 0;
            $pedi->iva = (isset($pedido->iva) && is_numeric($pedido->iva)) ? $pedido->iva : 0;
            $pedi->save();
            $calculationLogs = [];
            foreach ($pedido->lineas as $index => $pro) {
                if ($pro->ordentrabajo_id == 0) {
                    $orden = self::crearOrden($pro, $pedi);
                    if (!$orden || !$orden->id) {
                        throw new Exception("No se pudo crear la Orden de Trabajo.");
                    }

                    // Gather auto calculation details
                    $articuloNombre = $pro->articulo->nombre ?? ('Producto #' . $pro->articulo_id);
                    $planchaName = 'Ninguna';
                    if ($orden->plancha) {
                        $plancha = \App\InventariosMateriaPrima::find($orden->plancha);
                        if ($plancha) {
                            $planchaName = $plancha->referencia ?: ('Plancha #' . $orden->plancha);
                        }
                    }
                    $calculationLogs[] = "<b>" . e($articuloNombre) . "</b> (Cant: {$pro->cantidad}):<br>" .
                                         "• Plancha: " . e($planchaName) . "<br>" .
                                         "• Cabida: {$orden->cabida}<br>" .
                                         "• Medida Material: " . e($orden->medida_material) . "<br>" .
                                         "• Sobrante: {$orden->carpeta_cliente} pliegos";
                } else {
                    $ord = $pro->orden;

                    $orden = Ordentrabajo::find($pro->ordentrabajo_id);
                    if (!$orden) {
                        throw new Exception("No se encontró la Orden de Trabajo relacionada (#" . $pro->ordentrabajo_id . ").");
                    }
                    $orden->detalles_diseno = $ord->detalles_diseno ?? null;
                    $orden->observaciones = $ord->observaciones ?? null;
                    
                    $tamanoVal = $ord->tamano ?? null;
                    $orden->tamano = (isset($tamanoVal) && is_numeric($tamanoVal)) ? $tamanoVal : null;
                    
                    $orden->medida_material = $ord->medida_material ?? null;
                    
                    $cabidaVal = $ord->cabida ?? null;
                    $orden->cabida = (isset($cabidaVal) && is_numeric($cabidaVal)) ? $cabidaVal : null;
                    
                    $medidaFinalReq = !empty($ord->medida_final) ? $ord->medida_final : (!empty($pro->medida_final) ? $pro->medida_final : (!empty($pro->orden->medida_final) ? $pro->orden->medida_final : null));
                    if (!empty($medidaFinalReq)) {
                        $orden->medida_final = $medidaFinalReq;
                    } else if (empty($orden->medida_final) && $orden->articulo_id) {
                        $artTemp = \App\Articulo::find($orden->articulo_id);
                        if ($artTemp && !empty($artTemp->medida_final)) {
                            $orden->medida_final = $artTemp->medida_final;
                        }
                    }
                    
                    $sobranteVal = $ord->carpeta_cliente ?? null;
                    $orden->carpeta_cliente = (isset($sobranteVal) && is_numeric($sobranteVal)) ? $sobranteVal : null;
                    
                    $planchaVal = $ord->plancha ?? null;
                    $orden->plancha = (isset($planchaVal) && is_numeric($planchaVal)) ? $planchaVal : null;
                    $orden->prioridad = $ord->prioridad ?? 0;
                    $orden->cantidad = $pro->cantidad;
                    $date = date('Y-m-d');
                    if ($orden->produccion == 'A') {
                        if (($ord->produccion ?? '') == 'EP' || ($ord->produccion ?? '') == 'ENP') {
                            $mod_date = strtotime($date . "12 days");
                            $fecha_entrega = date('Y-m-d', $mod_date);
                            $orden->fecha_entrega = $fecha_entrega;
                        }
                    } elseif ($orden->produccion == 'D') {
                        if (($ord->produccion ?? '') == 'EP' || ($ord->produccion ?? '') == 'ENP') {
                            $mod_date = strtotime($date . "12 days");
                            $fecha_entrega = date('Y-m-d', $mod_date);
                            $orden->fecha_entrega = $fecha_entrega;
                        }
                    }
                    $orden->valor_unitario = $pro->valor_unitario;
                    $orden->descuento = (integer) $pro->descuento;
                    $orden->saldo = $pro->valor_total;
                    $orden->impuesto = 0;
                    $orden->totalParcial = $pro->subtotal;
                    $orden->total = $pro->valor_total;
                    $orden->estado = ($pedido->estado != 1) ? 'VC' : 'PA';
                    $orden->produccion = $ord->produccion ?? 'P';
                    $orden->save();

                    if (isset($ord->detalles)) {
                        $itemCostos = isset($ord->costos) ? $ord->costos : [];
                        $detcosto = self::crearCostosDetalles($ord->detalles, $itemCostos, $orden, '', $pedido->user_id);
                    }

                }
                if ($pro->id > 0) {
                    $linea = LineaComprobante::find($pro->id);
                } else {
                    $linea = new LineaComprobante();
                    $linea->ordentrabajo_id = $orden->id;
                    $linea->fecha = $pedi->fecha;
                    $linea->fecha_entrega = $orden->fecha_entrega;
                }
                $linea->comprobante_id = $pedi->id;
                $linea->articulo_id = $pro->articulo_id;
                $linea->cantidad = $pro->cantidad;
                $linea->descuento = $pro->descuento;
                $linea->impuesto = 0;
                $linea->valor_unitario = $pro->valor_unitario;
                $linea->valor_total = $pro->valor_total;
                $linea->subtotal = $pro->subtotal;
                $linea->estado = 0;
                $linea->orden = $index + 1;
                $linea->save();

            }
            $this->contabilizarPedido($pedi);
            self::sincronizarProformaConPedido($pedi->id);
            DB::commit();

            if ($pedi->estado == 2 && $estadoAnterior != 2) {
                try {
                    self::enviarNotificacionVenta($pedi);
                } catch (\Exception $e) {
                    \Log::error("Error al procesar notificaciones de venta en crearPedido: " . $e->getMessage());
                }
            }

            if (!empty($calculationLogs)) {
                $pedi->calculations_summary = "<b>Cálculos automáticos del pedido:</b><br><br>" . implode("<br><br>", $calculationLogs);
            }

            return $pedi;
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error("Error al crear el pedido: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
    public function imprimirPedido(Request $request)
    {
        $pedido = Comprobante::with(['cliente.contactos', 'cliente.empresas', 'cliente.envios', 'lineas.articulo', 'lineas.orden.detalles'])->find($request->id);

        if (!$pedido || !$pedido->cliente) {
            return "Error: Pedido o Cliente no encontrado.";
        }

        if (isset($pedido->observaciones) && (strpos($pedido->observaciones, '[NO_TOTALIZAR]') !== false || strpos($pedido->observaciones, '[SIN_TOTALES]') !== false)) {
            $pedido->ocultar_totales_pdf = true;
            $pedido->observaciones = trim(str_replace(['[NO_TOTALIZAR]', '[SIN_TOTALES]'], '', $pedido->observaciones));
        }

        $v = new CifrasEnLetras();
        //Convertimos el total en letras
        $pedido->letras = ($v->convertirEurosEnLetras(number_format($pedido->total, 0)));
        $id = array();
        foreach ($pedido->lineas as $linea) {
            $hasIva = (isset($linea->impuesto) && $linea->impuesto > 0) || (isset($linea->iva) && $linea->iva > 0) || ($pedido->impuestos > 0 && !isset($linea->impuesto) && !isset($linea->iva));
            if ($hasIva) {
                $linea->valorconiva = $linea->valor_unitario * 1.19;
                $linea->valortotalconiva = ($linea->valor_total ?: ($linea->cantidad * $linea->valor_unitario)) * 1.19;
            } else {
                $linea->valorconiva = $linea->valor_unitario;
                $linea->valortotalconiva = $linea->valor_total ?: ($linea->cantidad * $linea->valor_unitario);
            }

            if ($linea->articulo && $linea->articulo->tipo) {
                $linea->tipo = $linea->articulo->tipo;
            }
            if ($linea->articulo) {
                $linea->articulo;
            }

            if ($linea->orden && isset($linea->orden->detalles)) {
                $linea->detalles = $linea->orden->detalles;
                foreach ($linea->detalles as $det) {
                    if ($det->titulo == 'Tinta' || $det->titulo == 'tinta') {
                        if (is_array(json_decode($det->valor))) {
                            $det->valor = json_decode($det->valor);
                        }
                    }

                }
            } else {
                array_push($id, $pedido->id);
            }
            if (isset($linea->orden->costos)) {

            }

            $atributos = new \stdClass();
            // $atributos->label='';
            // $atributos->descripcion='';
            $linea->tipo->atributos;
        }
        // return $pedido;
        // $co
        // $pedido=json_decode($request->pedido);
        // foreach ($pedido->lineas as $linea) {
        //     return $linea;
        // }

        // return view('pdf.pedido', [
        //     'pedido' => $request->pedido
        // ]);
        // $formatterES = new NumberFormatter("es", NumberFormatter::SPELLOUT);
        // echo $formatterES->format(123.45);
        $path = public_path() . '/reportes/pedido.pdf';

        $pdf = PDF::loadView('pdf.pedido', compact('pedido'));
        $pdf->setPaper('carta', 'portrait');

        $pdf->save($path);

        return $pdf->stream();

    }
    public function imprimirFactura(Request $request)
    {
        $factura = Comprobante::with(['razonsocial', 'lineas.articulo', 'lineas.orden'])->findOrFail($request->id);

        // Asignar cliente priorizando la Razón Social de datos de facturación
        $clienteObj = null;
        if ($factura->razonsocial) {
            $clienteObj = $factura->razonsocial;
        } elseif ($factura->cliente) {
            if (method_exists($factura->cliente, 'empresas') && $factura->cliente->empresas && $factura->cliente->empresas->count() > 0) {
                $clienteObj = $factura->cliente->empresas->first();
            } else {
                $clienteObj = $factura->cliente;
            }
        }
        $factura->cliente = $clienteObj ?? $factura->cliente;

        // Si no tiene lineas propias, intentar buscar por fuente_id (compatibilidad con lógica antigua)
        if ($factura->lineas->count() == 0 && $factura->fuente_id) {
            $fids = explode(',', $factura->fuente_id);
            $factura->lineas = LineaComprobante::with(['articulo', 'orden'])->whereIn('comprobante_id', $fids)->get();
        }

        $v = new CifrasEnLetras();
        $factura->letras = $v->convertirEurosEnLetras(number_format($factura->total, 0));

        foreach ($factura->lineas as $linea) {
            if ($factura->impuestos > 0) {
                $linea->valorconiva = $linea->valor_unitario * 1.19;
                $linea->valortotalconiva = $linea->valor_total * 1.19;
            } else {
                $linea->valorconiva = $linea->valor_unitario;
                $linea->valortotalconiva = $linea->valor_total;
            }

            if ($linea->orden && isset($linea->orden->detalles)) {
                $linea->detalles = $linea->orden->detalles->sortBy('orden')->values();
            } elseif (!isset($linea->detalles)) {
                $linea->detalles = [];
            }

            foreach ($linea->detalles as $det) {
                $det->valor_formateado = '';
                $valRaw = $det->valor;
                if (is_string($valRaw) && (strpos(trim($valRaw), '[') === 0 || strpos(trim($valRaw), '{') === 0)) {
                    $decoded = json_decode($valRaw, true);
                    if (is_array($decoded)) {
                        $pantones = [];
                        foreach ($decoded as $item) {
                            if (is_array($item) && isset($item['pantone'])) {
                                $pantones[] = $item['pantone'];
                            } elseif (is_array($item) && isset($item['nombre'])) {
                                $pantones[] = $item['nombre'];
                            } elseif (is_string($item)) {
                                $pantones[] = $item;
                            }
                        }
                        $det->valor_formateado = !empty($pantones) ? implode(', ', $pantones) : '';
                    } else {
                        $det->valor_formateado = $valRaw;
                    }
                } elseif (is_array($valRaw)) {
                    $pantones = [];
                    foreach ($valRaw as $item) {
                        if (is_array($item) && isset($item['pantone'])) {
                            $pantones[] = $item['pantone'];
                        } elseif (is_array($item) && isset($item['nombre'])) {
                            $pantones[] = $item['nombre'];
                        } elseif (is_string($item)) {
                            $pantones[] = $item;
                        }
                    }
                    $det->valor_formateado = !empty($pantones) ? implode(', ', $pantones) : '';
                } else {
                    $det->valor_formateado = (string)$valRaw;
                }
            }
        }

        $pdf = PDF::loadView('pdf.proforma', compact('factura'));
        $pdf->setPaper('carta', 'portrait');

        return $pdf->stream('comprobante_' . $factura->num_comprobante . '.pdf');
    }

    public function imprimirProforma(Request $request)
    {
        $id = $request->id ?? $request->route('id');
        $comprobante = Comprobante::with(['razonsocial', 'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'lineas.articulo', 'lineas.orden.detalles'])->find($id);

        if (!$comprobante) {
            return response()->json(['error' => 'Comprobante no encontrado'], 404);
        }

        // Si el comprobante recibido ya es tipo 'proforma', lo usamos directamente.
        if ($comprobante->tipo === 'proforma') {
            $proforma = $comprobante;
        } else {
            // Es un pedido (o cotizacion/otro) y debemos obtener o generar la proforma para este pedido.
            $pedidoId = $comprobante->id;
            $proforma = Comprobante::with(['razonsocial', 'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'lineas.articulo', 'lineas.orden.detalles'])
                ->where('tipo', 'proforma')
                ->where(function($q) use ($pedidoId) {
                    $q->where('pedido_id', $pedidoId)
                      ->orWhere('fuente_id', (string)$pedidoId);
                })
                ->first();

            if (!$proforma) {
                DB::beginTransaction();
                try {
                    // Obtener número inicial de proforma configurado en Ajustes
                    $ajusteInicial = Ajustes::where('tipo', 'consecutivo')
                        ->where('detalle', 'consecutivo_proforma')
                        ->first();
                    if (!$ajusteInicial) {
                        $ajusteInicial = Ajustes::where('tipo', 'inicial_proforma')->first();
                    }

                    $numInicial = $ajusteInicial ? (int)$ajusteInicial->valor : 5242;

                    // Obtener el mayor num_comprobante actual para proformas
                    $maxExistente = (int) Comprobante::where('tipo', 'proforma')->max('num_comprobante');
                    $siguienteNumero = max($maxExistente + 1, $numInicial);

                    $proforma = new Comprobante();
                    $proforma->tipo = 'proforma';
                    $proforma->num_comprobante = $siguienteNumero;
                    $proforma->fuente_id = (string)$pedidoId;
                    $proforma->pedido_id = $pedidoId;
                    $proforma->datos_factura_id = $comprobante->datos_factura_id;
                    $proforma->cliente_id = $comprobante->cliente_id;
                    $proforma->user_id = auth()->id() ?? $comprobante->user_id;
                    $proforma->fecha = date('Y-m-d');
                    $proforma->forma_pago = $comprobante->forma_pago;
                    $proforma->transportadora = $comprobante->transportadora;
                    $proforma->subtotal = $comprobante->subtotal;
                    $proforma->descuento = $comprobante->descuento;
                    $proforma->total = $comprobante->total;
                    $proforma->abono = (int) $comprobante->abono;
                    $proforma->saldo = $comprobante->saldo;
                    $proforma->estado = 0;
                    $proforma->impuestos = $comprobante->impuestos;
                    $proforma->iva = $comprobante->iva;
                    $proforma->save();

                    // Copiar o asociar líneas del pedido al comprobante proforma
                    foreach ($comprobante->lineas as $lineaPedido) {
                        $lineaP = $lineaPedido->replicate();
                        $lineaP->comprobante_id = $proforma->id;
                        $lineaP->ordentrabajo_id = 0;
                        $lineaP->save();
                    }

                    // Actualizar el valor del consecutivo en Ajustes si corresponde
                    if ($ajusteInicial) {
                        $ajusteInicial->valor = (string) $siguienteNumero;
                        $ajusteInicial->save();
                    } else {
                        Ajustes::create([
                            'tipo' => 'consecutivo',
                            'detalle' => 'consecutivo_proforma',
                            'valor' => (string) $siguienteNumero,
                            'categoria' => 'consecutivo'
                        ]);
                    }

                    DB::commit();

                    // Recargar proforma con relaciones
                    $proforma = Comprobante::with(['razonsocial', 'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'lineas.articulo', 'lineas.orden.detalles'])->find($proforma->id);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
        }

        // Formatear cliente priorizando Razón Social de datos de facturación
        $factura = $proforma;
        $clienteObj = null;
        if ($factura->razonsocial) {
            $clienteObj = $factura->razonsocial;
        } elseif ($factura->cliente) {
            if (method_exists($factura->cliente, 'empresas') && $factura->cliente->empresas && $factura->cliente->empresas->count() > 0) {
                $clienteObj = $factura->cliente->empresas->first();
            } else {
                $clienteObj = $factura->cliente;
            }
        }
        $factura->cliente = $clienteObj ?? $factura->cliente;

        if ($factura->lineas->count() == 0 && $factura->fuente_id) {
            $fids = explode(',', $factura->fuente_id);
            $factura->lineas = LineaComprobante::with(['articulo', 'orden.detalles'])->whereIn('comprobante_id', $fids)->get();
        }

        $v = new CifrasEnLetras();
        $factura->letras = $v->convertirEurosEnLetras(number_format($factura->total, 0));

        foreach ($factura->lineas as $linea) {
            if ($factura->impuestos > 0) {
                $linea->valorconiva = $linea->valor_unitario * 1.19;
                $linea->valortotalconiva = $linea->valor_total * 1.19;
            } else {
                $linea->valorconiva = $linea->valor_unitario;
                $linea->valortotalconiva = $linea->valor_total;
            }

            if ($linea->orden && isset($linea->orden->detalles)) {
                $linea->detalles = $linea->orden->detalles->sortBy('orden')->values();
            } elseif (!isset($linea->detalles)) {
                $linea->detalles = [];
            }

            foreach ($linea->detalles as $det) {
                $det->valor_formateado = '';
                $valRaw = $det->valor;
                if (is_string($valRaw) && (strpos(trim($valRaw), '[') === 0 || strpos(trim($valRaw), '{') === 0)) {
                    $decoded = json_decode($valRaw, true);
                    if (is_array($decoded)) {
                        $pantones = [];
                        foreach ($decoded as $item) {
                            if (is_array($item) && isset($item['pantone'])) {
                                $pantones[] = $item['pantone'];
                            } elseif (is_array($item) && isset($item['nombre'])) {
                                $pantones[] = $item['nombre'];
                            } elseif (is_string($item)) {
                                $pantones[] = $item;
                            }
                        }
                        $det->valor_formateado = !empty($pantones) ? implode(', ', $pantones) : '';
                    } else {
                        $det->valor_formateado = $valRaw;
                    }
                } elseif (is_array($valRaw)) {
                    $pantones = [];
                    foreach ($valRaw as $item) {
                        if (is_array($item) && isset($item['pantone'])) {
                            $pantones[] = $item['pantone'];
                        } elseif (is_array($item) && isset($item['nombre'])) {
                            $pantones[] = $item['nombre'];
                        } elseif (is_string($item)) {
                            $pantones[] = $item;
                        }
                    }
                    $det->valor_formateado = !empty($pantones) ? implode(', ', $pantones) : '';
                } else {
                    $det->valor_formateado = (string)$valRaw;
                }
            }
        }

        // Cargar configuración de la empresa desde Ajustes
        $empresaConfig = Ajustes::where('tipo', 'empresa')->pluck('valor', 'detalle')->toArray();
        $defaults = [
            'nombre' => 'AGENCIA LUPA S.A.S.',
            'slogan' => 'Soluciones Integrales en Empaques e Impresión',
            'nit' => '901086443-7',
            'telefono' => '+57 316 5288931',
            'email' => 'contacto@lupack.com',
            'direccion' => 'Carrera 1 # 23-60, Cali - Colombia',
            'logo' => 'img/LOGO-LUPA.jpg'
        ];
        $empresa = array_merge($defaults, $empresaConfig);

        // Si es la primera vez que se consulta/imprime y está en estado 0, registrar actividad en CRM
        if ($proforma->estado == 0) {
            $proforma->estado = 1; // Marca como Enviada/Generada
            Comprobante::where('id', $proforma->id)->update(['estado' => 1]);

            $clienteNombre = $factura->cliente ? $factura->cliente->razonsocial : 'Cliente';

            // Actividad General
            $actividad = new ActividadController();
            $datosA = new stdClass();
            $datosA->user_id = auth()->id() ?? $proforma->user_id;
            $datosA->actividad = 'Factura Proforma #' . $proforma->num_comprobante . ' enviada al cliente ' . $clienteNombre;
            $actividad->store($datosA);

            // Registrar en CRM Actividades
            if ($proforma->cliente_id) {
                try {
                    $oportunidad = \App\CrmOportunidad::where('cliente_id', $proforma->cliente_id)->orderBy('id', 'desc')->first();
                    $prospecto = \App\CrmProspecto::where('cliente_id', $proforma->cliente_id)->orderBy('id', 'desc')->first();

                    \App\CrmActividad::create([
                        'prospecto_id' => $prospecto ? $prospecto->id : null,
                        'oportunidad_id' => $oportunidad ? $oportunidad->id : null,
                        'user_id' => auth()->id() ?? $proforma->user_id,
                        'tipo' => 'Documento',
                        'asunto' => 'Factura Proforma #' . $proforma->num_comprobante . ' Enviada',
                        'descripcion' => 'Factura Proforma #' . $proforma->num_comprobante . ' generada y enviada a ' . $clienteNombre . ' por $' . number_format($proforma->total, 0, ',', '.') . ' para cobro de anticipo.',
                        'fecha_vencimiento' => now(),
                        'completada' => 1,
                        'fecha_completada' => now()
                    ]);
                } catch (\Exception $ex) {
                    // Silenciar si falla tabla CRM opcional
                }
            }
        }

        $defaultTerminos = "• Documento generado como Factura Proforma para la solicitud y cobro de anticipo de producción.\n• Por favor tener en cuenta que el saldo final por pagar puede variar según ajustes de producción (+/- 10%).";
        $terminos = Ajustes::getValorConfig('proforma', 'terminos', $defaultTerminos);

        $defaultNota = "Este documento es un comprobante de cotización / factura proforma sin efectos fiscales inmediatos.";
        $notaPie = Ajustes::getValorConfig('proforma', 'nota_pie', $defaultNota);

        $pdf = PDF::loadView('pdf.proforma', compact('factura', 'empresa', 'terminos', 'notaPie'));
        $pdf->setPaper('carta', 'portrait');

        return $pdf->stream('factura_proforma_' . $factura->num_comprobante . '.pdf');
    }

    public function proformas(Request $request)
    {
        $query = Comprobante::with(['cliente', 'razonsocial', 'usuario', 'lineas.articulo'])
            ->where('tipo', 'proforma');

        if ($request->has('buscar') && $request->buscar != '') {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('num_comprobante', 'like', "%{$buscar}%")
                  ->orWhere('id', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function($qc) use ($buscar) {
                      $qc->where('razonsocial', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->has('estado') && $request->estado !== '' && $request->estado !== null) {
            $query->where('estado', $request->estado);
        }

        $proformas = $query->orderBy('num_comprobante', 'desc')->paginate(30);

        return response()->json([
            'pagination' => [
                'total'        => $proformas->total(),
                'current_page' => $proformas->currentPage(),
                'per_page'     => $proformas->perPage(),
                'last_page'    => $proformas->lastPage(),
                'from'         => $proformas->firstItem(),
                'to'           => $proformas->lastItem(),
            ],
            'proformas' => $proformas
        ]);
    }

    public function cambiarEstadoProforma(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:comprobantes,id',
            'estado' => 'required|integer'
        ]);

        $proforma = Comprobante::where('tipo', 'proforma')->findOrFail($request->id);
        $proforma->estado = $request->estado;
        $proforma->save();

        $nombresEstado = [
            0 => 'Generada',
            1 => 'Enviada al Cliente',
            2 => 'Anticipo Cobrado / Pagada',
            3 => 'Anulada'
        ];
        $nombreEstado = $nombresEstado[$request->estado] ?? 'Actualizada';
        $clienteNombre = $proforma->cliente ? $proforma->cliente->razonsocial : 'Cliente';

        // Actividad general
        $actividad = new ActividadController();
        $datosA = new stdClass();
        $datosA->user_id = auth()->id() ?? $proforma->user_id;
        $datosA->actividad = 'Factura Proforma #' . $proforma->num_comprobante . ' cambió de estado a "' . $nombreEstado . '" (' . $clienteNombre . ')';
        $actividad->store($datosA);

        // Actividad CRM
        if ($proforma->cliente_id) {
            try {
                $oportunidad = \App\CrmOportunidad::where('cliente_id', $proforma->cliente_id)->orderBy('id', 'desc')->first();
                $prospecto = \App\CrmProspecto::where('cliente_id', $proforma->cliente_id)->orderBy('id', 'desc')->first();

                \App\CrmActividad::create([
                    'prospecto_id' => $prospecto ? $prospecto->id : null,
                    'oportunidad_id' => $oportunidad ? $oportunidad->id : null,
                    'user_id' => auth()->id() ?? $proforma->user_id,
                    'tipo' => 'Documento',
                    'asunto' => 'Factura Proforma #' . $proforma->num_comprobante . ': ' . $nombreEstado,
                    'descripcion' => 'El estado de la Factura Proforma #' . $proforma->num_comprobante . ' cambió a "' . $nombreEstado . '". Total: $' . number_format($proforma->total, 0, ',', '.'),
                    'fecha_vencimiento' => now(),
                    'completada' => 1,
                    'fecha_completada' => now()
                ]);
            } catch (\Exception $ex) {
                // Silenciar si falla tabla CRM opcional
            }
        }

        return response()->json([
            'message' => 'Estado de Factura Proforma #' . $proforma->num_comprobante . ' actualizado a "' . $nombreEstado . '" y registrado en CRM.',
            'proforma' => $proforma
        ]);
    }

    public function getConsecutivoProforma()
    {
        $ajuste = Ajustes::where('tipo', 'consecutivo')
            ->where('detalle', 'consecutivo_proforma')
            ->first();
        if (!$ajuste) {
            $ajuste = Ajustes::where('tipo', 'inicial_proforma')->first();
        }

        $valorActual = $ajuste ? (int)$ajuste->valor : 5242;
        $maxNum = (int) Comprobante::where('tipo', 'proforma')->max('num_comprobante');

        $defaultTerminos = "• Documento generado como Factura Proforma para la solicitud y cobro de anticipo de producción.\n• Por favor tener en cuenta que el saldo final por pagar puede variar según ajustes de producción (+/- 10%).";
        $terminos = Ajustes::getValorConfig('proforma', 'terminos', $defaultTerminos);

        $defaultNota = "Este documento es un comprobante de cotización / factura proforma sin efectos fiscales inmediatos.";
        $notaPie = Ajustes::getValorConfig('proforma', 'nota_pie', $defaultNota);

        return response()->json([
            'numero_inicial' => $valorActual,
            'ultimo_generado' => $maxNum,
            'siguiente' => max($maxNum + 1, $valorActual),
            'terminos' => $terminos,
            'nota_pie' => $notaPie
        ]);
    }

    public function setConsecutivoProforma(Request $request)
    {
        $request->validate([
            'numero' => 'required|integer|min:1'
        ]);

        $nuevoValor = (int)$request->numero;

        $ajuste = Ajustes::where('tipo', 'consecutivo')
            ->where('detalle', 'consecutivo_proforma')
            ->first();

        if (!$ajuste) {
            $ajuste = Ajustes::where('tipo', 'inicial_proforma')->first();
        }

        if ($ajuste) {
            $ajuste->valor = (string) $nuevoValor;
            $ajuste->save();
        } else {
            Ajustes::create([
                'tipo' => 'consecutivo',
                'detalle' => 'consecutivo_proforma',
                'valor' => (string) $nuevoValor,
                'categoria' => 'consecutivo'
            ]);
        }

        if ($request->has('terminos') && $request->terminos !== null) {
            Ajustes::updateOrCreate(
                ['tipo' => 'proforma', 'detalle' => 'terminos'],
                ['valor' => (string)$request->terminos, 'categoria' => 'proforma']
            );
        }

        if ($request->has('nota_pie') && $request->nota_pie !== null) {
            Ajustes::updateOrCreate(
                ['tipo' => 'proforma', 'detalle' => 'nota_pie'],
                ['valor' => (string)$request->nota_pie, 'categoria' => 'proforma']
            );
        }

        return response()->json([
            'status' => 'success',
            'numero' => $nuevoValor,
            'terminos' => $request->terminos,
            'nota_pie' => $request->nota_pie
        ]);
    }

    public function imprimirCuentaCobro(Request $request)
    {
        $id = $request->id ?? $request->route('id');
        $comprobante = Comprobante::with([
            'cliente.contactos', 'cliente.empresas', 'cliente.envios',
            'razonsocial',
            'lineas.articulo', 'lineas.orden.detalles',
            'usuario'
        ])->findOrFail($id);

        $cliente = $comprobante->cliente ?? $comprobante->razonsocial;

        $parentIds = method_exists($comprobante, 'getPedidoPadreIds') ? $comprobante->getPedidoPadreIds() : [];
        $pedido = !empty($parentIds) ? Comprobante::find(reset($parentIds)) : null;

        $data = [
            'comprobante' => $comprobante,
            'cliente' => $cliente,
            'lineas' => $comprobante->lineas,
            'pedido' => $pedido,
            'logo' => public_path('img/LOGO-LUPA.jpg')
        ];

        $view = 'pdf.cuentacobro_entrega_batch';
        $filename = 'Cuenta_Cobro_' . ($comprobante->num_comprobante ?? $comprobante->id) . '.pdf';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('carta', 'portrait');

        return $pdf->stream($filename);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function actualizarComprobante(Request $request)
    {
        $comprobanteData = json_decode($request->data);
        $comprobante = Comprobante::findOrFail($comprobanteData->id);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Update main fields
            $comprobante->fecha = $comprobanteData->fecha;
            $comprobante->forma_pago = $comprobanteData->forma_pago;
            $comprobante->subtotal = $comprobanteData->subtotal;
            $comprobante->descuento = $comprobanteData->descuento;
            $comprobante->total = $comprobanteData->total;
            $comprobante->impuestos = $comprobanteData->impuestos;
            $comprobante->abono = $comprobanteData->abono;
            $comprobante->saldo = $comprobanteData->saldo;
            if (isset($comprobanteData->estado)) {
                $comprobante->estado = $comprobanteData->estado;
            }
            if (isset($comprobanteData->transportadora)) {
                $comprobante->transportadora = $comprobanteData->transportadora;
            }
            $comprobante->save();

            // Update lines
            foreach ($comprobanteData->lineas as $index => $lineaData) {
                $linea = LineaComprobante::findOrFail($lineaData->id);
                $cantAnterior = $linea->cantidad;

                $linea->cantidad = $lineaData->cantidad;
                $linea->valor_unitario = $lineaData->valor_unitario;
                $linea->subtotal = $lineaData->subtotal;
                $linea->valor_total = $lineaData->valor_total;
                $linea->orden = $index + 1;
                $linea->save();

                // Sync with Orders and Deliveries if applicable
                if ($linea->ordentrabajo_id && in_array($comprobante->tipo, ['remision', 'cuentacobro'])) {
                    $orden = \App\Ordentrabajo::find($linea->ordentrabajo_id);
                    if ($orden) {
                        $diff = $linea->cantidad - $cantAnterior;
                        $entrega = \App\Entrega::where('comprobante_id', $comprobante->id)
                            ->where('ordentrabajo_id', $orden->id)
                            ->first();

                        if ($entrega) {
                            $entrega->cantidad = $linea->cantidad;
                            $orden->cantidad_entregada += $diff;

                            // If it's a remision, update linked CC
                            if ($comprobante->tipo === 'remision') {
                                $cc = Comprobante::where('tipo', 'cuentacobro')->where('fuente_id', $comprobante->id)->first();
                                if ($cc) {
                                    $lineaCC = LineaComprobante::where('comprobante_id', $cc->id)
                                        ->where('ordentrabajo_id', $orden->id)
                                        ->first();
                                    if ($lineaCC) {
                                        $lineaCC->cantidad = $linea->cantidad;
                                        $lineaCC->subtotal = $linea->subtotal;
                                        $lineaCC->valor_total = $linea->valor_total;
                                        $lineaCC->save();

                                        $cc->subtotal = LineaComprobante::where('comprobante_id', $cc->id)->sum('subtotal');
                                        $cc->impuestos = round($cc->subtotal * ($cc->iva ?? 0.19), 2);
                                        $cc->total = $cc->subtotal + $cc->impuestos;
                                        $cc->saldo = $cc->total - $cc->abono;
                                        $cc->save();
                                    }
                                }
                            }

                            $orden->produccion = ($orden->cantidad_entregada >= $orden->cantidad) ? 'T' : 'P';
                            $orden->save();
                            $entrega->save();
                        }
                    }
                }
            }
            $this->contabilizarPedido($comprobante);

            // Sincronizar Proforma si es un Pedido, o Pedido si es una Proforma
            if (in_array($comprobante->tipo, ['pedido', 'cotizacion'])) {
                self::sincronizarProformaConPedido($comprobante->id);
            } elseif ($comprobante->tipo === 'proforma') {
                self::sincronizarPedidoConProforma($comprobante->id);
            }

            \Illuminate\Support\Facades\DB::commit();

            $datosA = new \stdClass();
            $datosA->user_id = $request->user_id ?? $comprobante->user_id;
            $datosA->actividad = 'Edito comprobante ' . $comprobante->tipo . ' #' . $comprobante->num_comprobante;
            $actividad = new ActividadController();
            $actividad->store($datosA);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $clienteData = is_string($request->cliente) ? json_decode($request->cliente, true) : $request->cliente;
            $pedidoData = is_string($request->pedido) ? json_decode($request->pedido, true) : $request->pedido;

            if (!$clienteData || !isset($clienteData['id'])) {
                return response()->json(['status' => 'error', 'message' => 'Cliente no válido.'], 422);
            }
            if (!$pedidoData || !isset($pedidoData['productos']) || count($pedidoData['productos']) == 0) {
                return response()->json(['status' => 'error', 'message' => 'Debe agregar al menos un producto.'], 422);
            }

            $comprobante = new Comprobante();
            $comprobante->tipo = $pedidoData['tipo'] ?? 'cotizacion';
            $comprobante->cliente_id = $clienteData['id'];
            $comprobante->user_id = \Auth::id() ?? 2;
            $comprobante->fecha = date('Y-m-d');
            $comprobante->forma_pago = 'Contado';
            $comprobante->subtotal = $pedidoData['subtotal'] ?? 0;
            $comprobante->iva = $pedidoData['impuesto'] ?? 0;
            $comprobante->descuento = $pedidoData['descuento'] ?? 0;
            $comprobante->total = $pedidoData['total'] ?? 0;
            $comprobante->impuestos = $pedidoData['impuesto'] ?? 0;
            $comprobante->abono = 0;
            $comprobante->saldo = $comprobante->total;
            $comprobante->estado = 1;
            $comprobante->datos_factura_id = 0;
            $maxNum = (int)Comprobante::max('num_comprobante') ?: 0;
            $comprobante->num_comprobante = $maxNum + 1;
            $comprobante->save();

            $firstArticuloId = \App\Articulo::value('id') ?? 1;

            foreach ($pedidoData['productos'] as $prodIndex => $prod) {
                $artId = (!empty($prod['id']) && $prod['id'] > 0) ? $prod['id'] : $firstArticuloId;

                // Crear Orden de Trabajo para asociar detalles de especificaciones
                $orden = new Ordentrabajo();
                $orden->cliente_id = $clienteData['id'];
                $orden->articulo_id = $artId;
                $orden->detalles_diseno = $prod['nombre'] ?? 'Trabajo Personalizado';
                $orden->observaciones = $prod['nombre'] ?? 'Trabajo Personalizado';
                $orden->fecha = date('Y-m-d');
                $orden->fecha_entrega = date('Y-m-d', strtotime("+12 days"));
                $orden->cantidad = $prod['cantidad'] ?? 1;
                $orden->valor_unitario = $prod['valor_unitario'] ?? 0;
                $orden->totalParcial = $prod['subtotal'] ?? 0;
                $orden->descuento = $prod['descuento'] ?? 0;
                $orden->impuesto = $prod['iva'] ?? 0;
                $orden->total = $prod['total'] ?? 0;
                $orden->estado = 'VC';
                $orden->produccion = 'EP';
                $orden->save();

                // Guardar especificaciones/atributos en Detalletrabajo
                if (isset($prod['atributos']) && is_array($prod['atributos'])) {
                    foreach ($prod['atributos'] as $attrIndex => $attr) {
                        $titulo = $attr['nombre'] ?? $attr['titulo'] ?? 'Detalle';
                        $valor = '';

                        if (isset($attr['open'])) {
                            if (is_array($attr['open'])) {
                                $vals = [];
                                foreach ($attr['open'] as $op) {
                                    if (is_array($op)) {
                                        $vals[] = $op['labelOpAtributo'] ?? $op['label'] ?? $op['nombre'] ?? $op['valor'] ?? '';
                                    } else if (is_object($op)) {
                                        $vals[] = $op->labelOpAtributo ?? $op->label ?? $op->nombre ?? $op->valor ?? '';
                                    } else {
                                        $vals[] = (string)$op;
                                    }
                                }
                                $valor = implode(', ', array_filter($vals));
                            } else if (is_object($attr['open']) || is_array($attr['open'])) {
                                $valArr = (array)$attr['open'];
                                $valor = $valArr['labelOpAtributo'] ?? $valArr['label'] ?? $valArr['nombre'] ?? $valArr['valor'] ?? implode(', ', array_filter($valArr));
                            } else {
                                $valor = (string)$attr['open'];
                            }
                        } else if (isset($attr['valor'])) {
                            if (is_array($attr['valor']) || is_object($attr['valor'])) {
                                $valor = json_encode($attr['valor']);
                            } else {
                                $valor = (string)$attr['valor'];
                            }
                        } else if (isset($attr['labelOpAtributo'])) {
                            $valor = (string)$attr['labelOpAtributo'];
                        }

                        if ($valor !== '' && $valor !== 'false' && $valor !== 'null') {
                            $det = new Detalletrabajo();
                            $det->ordentrabajo_id = $orden->id;
                            $det->titulo = mb_substr($titulo, 0, 20);
                            $det->valor = mb_substr($valor, 0, 1900);
                            $det->orden = $attrIndex + 1;
                            $det->save();
                        }
                    }
                }

                // Guardar múltiples papeles en tabla costos si vienen especificados
                if (isset($prod['papeles']) && is_array($prod['papeles'])) {
                    foreach ($prod['papeles'] as $pItem) {
                        $costoPapel = new \App\CostoProduccion();
                        $costoPapel->ordentrabajo_id = $orden->id;
                        $costoPapel->titulo = 'papel';
                        $costoPapel->costois_id = $pItem['costois_id'] ?? null;
                        $costoPapel->descripcion = $pItem['descripcion'] ?? ($pItem['nombre'] ?? 'Papel');
                        $costoPapel->cantidad = $pItem['cantidad'] ?? ($pItem['pliegos'] ?? 0);
                        $costoPapel->valor = $pItem['valor'] ?? 0;
                        $costoPapel->total = $pItem['total'] ?? 0;
                        $costoPapel->medida_material = $pItem['medida_material'] ?? ($pItem['corte'] ?? null);
                        $costoPapel->tamano = $pItem['tamano'] ?? null;
                        $costoPapel->medida_final = $pItem['medida_final'] ?? null;
                        $costoPapel->cabida = $pItem['cabida'] ?? null;
                        $costoPapel->sobrante = $pItem['sobrante'] ?? null;
                        $costoPapel->componente = $pItem['componente'] ?? null;
                        $costoPapel->save();
                    }
                }

                $linea = new LineaComprobante();
                $linea->comprobante_id = $comprobante->id;
                $linea->articulo_id = $artId;
                $linea->ordentrabajo_id = $orden->id;
                $linea->fecha = date('Y-m-d');
                $linea->fecha_entrega = date('Y-m-d', strtotime("+12 days"));
                $linea->cantidad = $prod['cantidad'] ?? 1;
                $linea->valor_unitario = $prod['valor_unitario'] ?? 0;
                $linea->subtotal = $prod['subtotal'] ?? 0;
                $linea->descuento = $prod['descuento'] ?? 0;
                $linea->impuesto = $prod['iva'] ?? 0;
                $linea->valor_total = $prod['total'] ?? 0;
                $linea->estado = 1;
                $linea->orden = $prodIndex + 1;
                $linea->save();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'comprobante' => $comprobante,
                'pdf_url' => url('/imprimirPedido?id=' . $comprobante->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en ComprobanteController@store: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Comprobante  $comprobante
     * @return \Illuminate\Http\Response
     */
    public function show(Comprobante $comprobante)
    {
        return response($comprobante);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Comprobante  $comprobante
     * @return \Illuminate\Http\Response
     */
    public function edit(Comprobante $comprobante)
    {
        return response($comprobante);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Comprobante  $comprobante
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comprobante $comprobante)
    {
        return response(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Comprobante  $comprobante
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comprobante $comprobante)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar comprobantes.'], 403);
        }
        $comprobante->delete();
        return response(['success' => true]);
    }

    public static function enviarNotificacionVenta(Comprobante $pedido)
    {
        // Load relationships if they aren't loaded
        $pedido->loadMissing(['cliente', 'lineas.articulo']);

        // Find users that are assigned to receive notifications
        $users = User::where('notificar_ventas', 1)
                     ->where('condicion', 1)
                     ->with('empleado')
                     ->get();

        if ($users->isEmpty()) {
            return;
        }

        // Prepare message content
        $clienteNombre = $pedido->cliente ? $pedido->cliente->nombre : 'N/A';
        $totalFormatted = number_format($pedido->total, 2);
        
        // Find expected delivery date from lines or default 12 days
        $fechaEntrega = 'N/A';
        foreach ($pedido->lineas as $linea) {
            if ($linea->fecha_entrega) {
                $fechaEntrega = $linea->fecha_entrega;
                break;
            }
        }

        $mensajeWhatsapp = "¡Hola! Se ha puesto en producción el Pedido #{$pedido->id} para el cliente {$clienteNombre} por un valor total de \${$totalFormatted}. Fecha de entrega estimada: {$fechaEntrega}.";

        foreach ($users as $user) {
            if (!$user->empleado) {
                continue;
            }

            // 1. Send Email
            $email = $user->empleado->correo;
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new PedidoProduccionMailable($pedido));
                } catch (\Exception $e) {
                    \Log::error("Error enviando correo de notificación de venta a {$email}: " . $e->getMessage());
                }
            }

            // 2. Send WhatsApp
            $telefono = $user->empleado->telefono;
            if (!empty($telefono)) {
                // Remove non-numeric characters for cleaner phone handling, but keep + if present
                $telefonoLimpio = preg_replace('/[^0-9+]/', '', $telefono);
                self::enviarWhatsApp($telefonoLimpio, $mensajeWhatsapp);
            }
        }
    }

    public static function enviarWhatsApp($telefono, $mensaje)
    {
        $url = env('WHATSAPP_API_URL');
        $token = env('WHATSAPP_API_TOKEN');
        if (empty($url) || empty($telefono)) {
            return false;
        }

        try {
            $client = new \GuzzleHttp\Client();

            // Detect if this is Meta/Facebook Cloud API
            if (strpos($url, 'graph.facebook.com') !== false || strpos($url, 'facebook.com') !== false) {
                // Meta Cloud API expects phone number without leading '+'
                $telefonoMeta = ltrim($telefono, '+');
                $body = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $telefonoMeta,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $mensaje,
                    ]
                ];
            } else {
                // Default third-party API payload
                $body = [
                    'token' => $token,
                    'to' => $telefono,
                    'body' => $mensaje,
                ];
            }

            $response = $client->post($url, [
                'json' => $body,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'timeout' => 10,
            ]);
            return $response->getStatusCode() === 200 || $response->getStatusCode() === 201;
        } catch (\Exception $e) {
            \Log::error("Error enviando WhatsApp a {$telefono}: " . $e->getMessage());
            return false;
        }
    }

    private function contabilizarPedido($pedido)
    {
        if ($pedido->tipo !== 'pedido') {
            return;
        }

        $comp = null;
        if ($pedido->comprobante_contable_id) {
            $comp = \App\ComprobanteContable::find($pedido->comprobante_contable_id);
        }
        
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Diario')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Diario';
            $comp->numero = $ultimoNumero + 1;
        }
        
        $comp->fecha = $pedido->fecha;
        $comp->descripcion = "Pedido No. " . $pedido->num_comprobante . " - Cliente: " . ($pedido->cliente ? $pedido->cliente->razonsocial : 'Desconocido');
        $comp->user_id = \Illuminate\Support\Facades\Auth::id() ?: $pedido->user_id;
        $comp->save();

        $pedido->comprobante_contable_id = $comp->id;
        $pedido->saveQuietly();

        $comp->detalles()->delete();

        $cuentaDebito = \App\Cuenta::where('codigo', '130505')->first();
        $cuentaCredito = \App\Cuenta::where('codigo', '413505')->first();

        if ($cuentaDebito && $cuentaCredito) {
            $clienteExiste = \App\Persona::where('id', $pedido->cliente_id)->exists();
            $terceroId = $clienteExiste ? $pedido->cliente_id : null;

            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaDebito->id,
                'tercero_id' => $terceroId,
                'debe' => $pedido->total,
                'haber' => 0.00,
                'referencia' => 'Pedido #' . $pedido->id
            ]);

            $iva = (float)($pedido->impuestos ?? 0);
            if ($iva > 0) {
                $subtotal = (float)($pedido->subtotal ?? 0);
                
                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaCredito->id,
                    'tercero_id' => $terceroId,
                    'debe' => 0.00,
                    'haber' => $subtotal,
                    'referencia' => 'Pedido #' . $pedido->id
                ]);

                $cuentaIva = \App\Cuenta::where('codigo', '240805')->first();
                if (!$cuentaIva) {
                    $cuentaIva = \App\Cuenta::create([
                        'codigo' => '240805',
                        'nombre' => 'Impuesto sobre las Ventas por Pagar (IVA)',
                        'tipo' => 'Pasivo',
                        'naturaleza' => 'Crédito',
                        'es_detalle' => 1
                    ]);
                }

                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaIva->id,
                    'tercero_id' => $terceroId,
                    'debe' => 0.00,
                    'haber' => $iva,
                    'referencia' => 'IVA Pedido #' . $pedido->id
                ]);
            } else {
                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaCredito->id,
                    'tercero_id' => $terceroId,
                    'debe' => 0.00,
                    'haber' => $pedido->total,
                    'referencia' => 'Pedido #' . $pedido->id
                ]);
            }
        }
    }
}

