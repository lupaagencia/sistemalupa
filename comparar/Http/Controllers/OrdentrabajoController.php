<?php

namespace App\Http\Controllers;
use App\LineaComprobante;
use App\Ordentrabajo;
use App\CostoProduccion;
use App\Detalletrabajo;
use App\statusProduccion;

use App\Reportes;
use App\Costois;
use App\Cliente;
use App\Articulo;
use App\Comprobante;
use App\Costo;
use App\InventariosMateriaPrima;
use App\Http\Controllers\StatusProduccionController;
use App\Http\Controllers\ComprobanteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Mixins\DownloadCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\ReciboPago;
use App\Ajustes;
use Barryvdh\DomPDF\Facade\Pdf;
use stdClass;

class OrdentrabajoController extends Controller
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
        $operador = $request->operador;
        $per_page = $request->per_page;
        $query = Ordentrabajo::with(['detalles.costo.costois', 'articulo', 'cliente', 'status']);
        
        if ($criterio == '' || $buscar == '') {
            $ordenes = $query->orderBy('fecha_entrega', 'DESC')->paginate($per_page);
        } else {
            $ordenes = $query->where($criterio, $operador, '%' . $buscar . '%')
                             ->orderBy('ordentrabajos.fecha_entrega', 'DESC')->paginate($per_page);
        }
        foreach ($ordenes as $orden) {
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->id)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            foreach ($orden->detalles as $det) {
                if ($det->titulo == 'Tinta' || $det->titulo == 'tinta') {
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
            $orden->costos = $costos;
            $orden->articulo;
            $orden->cliente;
            $orden->planchas = InventariosMateriaPrima::where('asignado_id', $orden->cliente->id)->where('tipo', 'Plancha')->get();
            $newDate = date("Y-m-d", strtotime($orden->created_at));
            $orden->fecha_orden = $newDate;

        }

        return [
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function buscarorden(Request $request)
    {
        $orden = Ordentrabajo::with(['cliente', 'articulo', 'papel'])->find($request->id);
        return $orden;
    }
    public function filtroOrden(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador = $request->operador;
        $per_page = $request->per_page;
        if ($criterio == 'cliente_id') {
            $cliente = Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->get();
            if (count($cliente) > 0) {
                $buscar = $cliente[0]->id;
            }
        }
        if ($criterio == 'articulo_id') {
            $articulo = Articulo::where('nombre', 'LIKE', '%' . $buscar . '%')->get();
            if (count($articulo) > 0) {
                $buscar = $articulo[0]->id;
            }
        }
        if ($operador == 'like') {
            $buscar = '%' . $buscar . '%';
        }
        $ordenes = Ordentrabajo::where($criterio, $operador, $buscar)->orderBy('ordentrabajos.fecha_entrega', 'DESC')->paginate($per_page);

        foreach ($ordenes as $orden) {
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->id)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            foreach ($orden->detalles as $det) {
                if ($det->titulo == 'Tinta' || $det->titulo == 'tinta') {
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
            $orden->costos = $costos;
            $orden->articulo;
            $orden->cliente;
            $orden->planchas = InventariosMateriaPrima::where('asignado_id', $orden->cliente->id)->where('tipo', 'Plancha')->get();
            $newDate = date("Y-m-d", strtotime($orden->created_at));
            $orden->fecha_orden = $newDate;
        }

        return [
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function cartera(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador = $request->operador ?: 'like';

        // Definir los tipos que componen la cartera (deuda real financiera)
        // Se excluyen remisiones ya que son solo documentos de entrega
        $tiposCartera = ['cuentacobro', 'factura'];

        $query = Comprobante::with([
            'cliente.empresas',
            'cliente.contactos',
            'cliente.envios',
            'lineas.articulo.tipo.atributos',
            'lineas.orden.detalles.costo.costois',
            'lineas.orden.costos',
            'lineas.orden.status'
        ])
            ->whereIn('tipo', $tiposCartera)
            ->where('saldo', '>', 0);

        // 2. Obtener Recibos de Pago con saldo a favor (Ingresos por cruzar)
        $queryRecibos = \App\ReciboPago::with(['cliente', 'cruces.comprobante'])
            ->where('saldo_recibo', '>', 0);

        // Apply search filters to comprobantes
        $ids = [];
        if ($buscar != '' && $buscar != 'todos') {
            if ($criterio == 'cliente_id') {
                $clientes = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')
                    ->get();
                if (count($clientes) > 0) {
                    $ids = $clientes->pluck('id')->toArray();
                    $query->whereIn('cliente_id', $ids);
                    $queryRecibos->whereIn('cliente_id', $ids);
                } else {
                    $query->where('cliente_id', 0);
                    $queryRecibos->where('cliente_id', 0);
                }
            } else if ($criterio == 'total' || $criterio == 'saldo') {
                $val = str_replace(['$', '.', ','], '', $buscar);
                $query->where($criterio, 'LIKE', '%' . $val . '%');
                if ($criterio == 'total') {
                    $queryRecibos->where('monto', 'LIKE', '%' . $val . '%');
                }
            } else if ($criterio == 'num_comprobante') {
                $query->where(function ($q) use ($buscar) {
                    $q->where('num_comprobante', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('fuente_id', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('pedido_id', 'LIKE', '%' . $buscar . '%');
                });
                $queryRecibos->where(function ($q) use ($buscar) {
                    $q->where('num_recibo', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('pedido_id', 'LIKE', '%' . $buscar . '%');
                });
            } else if (!empty($criterio)) {
                $query->where($criterio, 'LIKE', '%' . $buscar . '%');
                if ($criterio == 'num_comprobante') { // Fallback safety although handled above
                    $queryRecibos->where('num_recibo', 'LIKE', '%' . $buscar . '%');
                }
            }
        }

        $comprobantes = $query->orderBy('fecha', 'desc')->get();

        $recibos = $queryRecibos->orderBy('fecha', 'desc')->get();

        // 3. Unificar ambos para la vista
        $clientesEnCartera = $comprobantes->pluck('cliente_id')->unique()->values()->toArray();
        $recibos = $recibos->filter(function ($r) use ($clientesEnCartera) {
            return in_array($r->cliente_id, $clientesEnCartera);
        });

        // 4. Mapear datos para la vista, incluyendo transformaciones necesarias para el componente Pedido
        $pedidos = collect($comprobantes)->map(function ($item) {
            $data = $item->toArray();

            // Recalcular total real para coherencia visual en las listas
            $v_total = (float) $item->impuestos > 0 ? ((float) $item->subtotal * 1.19) : (float) $item->subtotal;
            if ($v_total <= 0)
                $v_total = (float) $item->total;

            $data['total'] = $v_total;
            // Recalcular saldo basado en el total corregido y el abono actual
            $data['saldo'] = $v_total - (float) $item->abono;

            $data['clase_cartera'] = 'Deuda';
            $data['parent_pedido_ids'] = $item->getPedidoPadreIds();

            // El componente Pedido.vue espera que 'detalles' esté directamente en la línea
            if (isset($data['lineas'])) {
                foreach ($data['lineas'] as &$linea) {
                    if (isset($linea['orden']['detalles'])) {
                        $linea['detalles'] = $linea['orden']['detalles'];
                    }

                    // Asegurar que las planchas del cliente estén disponibles si es necesario
                    if (isset($linea['orden'])) {
                        $linea['orden']['planchas'] = \App\InventariosMateriaPrima::where('asignado_id', $item->cliente_id)
                            ->where('tipo', 'Plancha')->get();
                    }
                }
            }
            return $data;
        });

        foreach ($recibos as $r) {
            // Build a description of what documents this receipt was applied to
            $aplicadoA = '';
            if ($r->cruces && $r->cruces->count() > 0) {
                $nums = $r->cruces->map(function ($c) {
                    return $c->comprobante ? ($c->comprobante->num_comprobante ?: 'Doc#' . $c->comprobante_id) : '';
                })->filter()->implode(', ');
                $aplicadoA = $nums ? ' → ' . $nums : '';
            }

            $mock = new \stdClass();
            $mock->id = 'R' . $r->id;
            $mock->tipo = 'ingreso';
            $mock->num_comprobante = $r->num_recibo . $aplicadoA;
            $mock->fecha = $r->fecha;
            $mock->total = 0;
            $mock->abono = $r->monto;
            $mock->saldo = -$r->saldo_recibo; // negative = credit in favor
            $mock->cliente = $r->cliente;
            $mock->clase_cartera = 'Credito';
            $mock->forma_pago = $r->forma_pago;
            $mock->saldo_recibo = $r->saldo_recibo;
            $mock->parent_pedido_ids = $r->pedido_id ? [$r->pedido_id] : [];
            $pedidos->push($mock);
        }

        // Ordenar final por fecha desc
        $pedidos = $pedidos->sortByDesc('fecha')->values();

        return [
            'pagination' => [
                'total' => $pedidos->count(),
                'current_page' => 1,
                'per_page' => 500,
                'last_page' => 1,
                'from' => 1,
                'to' => $pedidos->count(),
            ],
            'pedidos' => [
                'data' => $pedidos
            ],
            'clientes_encontrados' => isset($clientes) ? $clientes : []
        ];
    }

    public function estadoCuenta(Request $request)
    {
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '256M');
        try {
            $buscar = $request->buscar;
            $criterio = $request->criterio;
            $valorBusq = $request->valor;
            $filtroFecha = $request->filtroFecha;

            $fechai = null;
            $fechaf = null;

            if ($filtroFecha && $filtroFecha != '1') {
                switch ($filtroFecha) {
                    case 'hoy':
                        $fechai = date('Y-m-d');
                        $fechaf = date('Y-m-d');
                        break;
                    case 'ayer':
                        $fechai = date('Y-m-d', strtotime("-1 days"));
                        $fechaf = date('Y-m-d', strtotime("-1 days"));
                        break;
                    case 'ultimos7':
                        $fechai = date('Y-m-d', strtotime("-7 days"));
                        $fechaf = date('Y-m-d');
                        break;
                    case 'ultimos30':
                        $fechai = date('Y-m-d', strtotime("-30 days"));
                        $fechaf = date('Y-m-d');
                        break;
                    case 'mes':
                        $fechai = date('Y-m-01');
                        $fechaf = date('Y-m-t');
                        break;
                    case 'semana':
                        $fechai = date('Y-m-d', strtotime('monday this week'));
                        $fechaf = date('Y-m-d', strtotime('sunday this week'));
                        break;
                    default:
                        $intervalo = explode(",", $filtroFecha);
                        if (count($intervalo) == 2) {
                            $fechai = $intervalo[0];
                            $fechaf = $intervalo[1];
                        }
                }
            }

            // NUEVA LOGICA:
            // Pedidos  = documentos financieros (controlan cartera y saldos)
            // Recibos  = se cruzan directamente con el Pedido (bajan el saldo)
            // CC / Remision = solo informativos, NO aparecen en estado de cuenta

            $clQuery = \App\Cliente::with(['empresas'])
                ->select('clientes.*')->distinct();

            if ($buscar != '' && $buscar != 'todos') {
                $clQuery->where(function ($q) use ($buscar) {
                    $q->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                    if (is_numeric($buscar)) {
                        $q->orWhereExists(function ($qe) use ($buscar) {
                            $qe->select(\DB::raw(1))->from('comprobantes')
                                ->whereRaw('comprobantes.cliente_id = clientes.id')
                                ->where('tipo', 'pedido')
                                ->where(function ($qq) use ($buscar) {
                                    $qq->where('id', $buscar)
                                        ->orWhere('num_comprobante', 'LIKE', "%$buscar%");
                                });
                        });
                    }
                });
            } else {
                // Solo clientes con pedidos pendientes de pago (saldo > 0)
                $clQuery->whereExists(function ($qe) use ($fechai, $fechaf) {
                    $qe->select(\DB::raw(1))->from('comprobantes')
                        ->whereRaw('comprobantes.cliente_id = clientes.id')
                        ->where('tipo', 'pedido')
                        ->where('saldo', '>', 0)
                        ->whereIn('estado', ['2', '3', '4', '5']);

                    if ($fechai && $fechaf) {
                        $qe->whereBetween('fecha', [$fechai . ' 00:00:00', $fechaf . ' 23:59:59']);
                    }
                });
            }

            $clientes = $clQuery->get();
            $clientIds = $clientes->pluck('id')->toArray();

            if (empty($clientIds))
                return response()->json([]);

            // Pre-cargar Pedidos de estos clientes
            $pedQuery = Comprobante::whereIn('cliente_id', $clientIds)
                ->where('tipo', 'pedido')
                ->where('saldo', '>', 0)
                ->whereIn('estado', ['2', '3', '4', '5']);

            if ($fechai && $fechaf) {
                $pedQuery->whereBetween('fecha', [$fechai . ' 00:00:00', $fechaf . ' 23:59:59']);
            }

            if ($valorBusq) {
                $val = str_replace(['$', '.', ','], '', $valorBusq);
                $pedQuery->where('total', 'LIKE', '%' . $val . '%');
            }

            $pedidosMap = $pedQuery->orderBy('fecha', 'asc')
                ->get()
                ->groupBy('cliente_id');

            // Pre-cargar Recibos de Pago de estos clientes
            $recibosMap = \App\ReciboPago::whereIn('cliente_id', $clientIds)
                ->orderBy('fecha', 'asc')
                ->get()
                ->groupBy('cliente_id');

            // Pre-cargar CruceCartera (tabla pequena, relacion alternativa recibo<->pedido)
            $crucesAll = \App\CruceCartera::all();
            $crucesByPedido = $crucesAll->groupBy('comprobante_id');

            $resultado = [];

            foreach ($clientes as $cliente) {
                $pedidos = $pedidosMap->get($cliente->id, collect([]));
                $recibos = $recibosMap->get($cliente->id, collect([]));

                if ($pedidos->isEmpty())
                    continue;

                $documentos = [];
                $totalValorPedidos = 0;
                $totalAbonos = 0;
                $totalSaldo = 0;
                $buscarNum = is_numeric($buscar) ? (int) $buscar : null;

                foreach ($pedidos as $pedido) {
                    // Calcular valor del pedido
                    $valorPedido = (float) $pedido->impuestos > 0
                        ? round((float) $pedido->subtotal * 1.19, 2)
                        : (float) $pedido->subtotal;
                    if ($valorPedido <= 0)
                        $valorPedido = (float) $pedido->total;

                    // Incluir pedidos en los estados permitidos con saldo > 0
                    $estadoNum = is_numeric($pedido->estado) ? (int) $pedido->estado : 0;
                    if (!in_array($estadoNum, [2, 3, 4, 5]))
                        continue;

                    // Recibos ligados por columna directa pedido_id
                    $recibosDirectos = $recibos->where('pedido_id', $pedido->id);

                    // Recibos ligados por tabla CruceCartera
                    $reciboIdsCruce = $crucesByPedido->get($pedido->id, collect([]))
                        ->pluck('recibo_pago_id')->unique()->toArray();
                    $recibosPorCruce = $recibos->whereIn('id', $reciboIdsCruce);

                    // Union de ambos conjuntos (sin duplicados)
                    $todosRecibos = $recibosDirectos->merge($recibosPorCruce)->unique('id');

                    // Calcular saldo
                    $abonoTotal = $todosRecibos->sum(function ($r) {
                        return (float) $r->monto;
                    });
                    $saldoPedido = max(0, $valorPedido - $abonoTotal);

                    // Solo incluir pedidos con saldo pendiente (o si es exactamente el buscado)
                    $esHit = $buscarNum && ($pedido->id == $buscarNum || $pedido->num_comprobante == $buscarNum);
                    if ($saldoPedido < 1 && !$esHit) // Cambiado a < 1 para ser más robusto ante decimales
                        continue;

                    // Fila del Pedido
                    $documentos[] = [
                        'id' => 'p_' . $pedido->id,
                        'clase' => 'pedido',
                        'raw_id' => $pedido->id,
                        'raw_num' => $pedido->num_comprobante ?: $pedido->id,
                        'label' => 'Pedido #' . ($pedido->num_comprobante ?: $pedido->id),
                        'fecha' => $pedido->fecha,
                        'valor_pedido' => $valorPedido,
                        'abono' => $abonoTotal,
                        'saldo' => $saldoPedido,
                        'estado' => $pedido->estado,
                    ];

                    // Filas de Recibos de Pago asociados
                    foreach ($todosRecibos as $r) {
                        $documentos[] = [
                            'id' => 'r_' . $r->id . '_p' . $pedido->id,
                            'clase' => 'recibo',
                            'raw_id' => $r->id,
                            'raw_num' => $r->num_recibo,
                            'label' => 'Recibo #' . $r->num_recibo,
                            'aplicado_a' => 'Pedido #' . ($pedido->num_comprobante ?: $pedido->id),
                            'pedido_ref' => $pedido->id,
                            'fecha' => $r->fecha,
                            'valor_pedido' => 0,
                            'abono' => (float) $r->monto,
                            'saldo' => 0,
                            'estado' => null,
                        ];
                    }

                    $totalValorPedidos += $valorPedido;
                    $totalAbonos += $abonoTotal;
                    $totalSaldo += $saldoPedido;
                }

                if (empty($documentos))
                    continue;

                $resultado[] = [
                    'cliente' => $cliente,
                    'documentos' => $documentos,
                    'totales' => [
                        'valor' => $totalValorPedidos,
                        'abono' => $totalAbonos,
                        'saldo' => $totalSaldo,
                    ],
                ];
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            \Log::error('estadoCuenta ERROR: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function carteraRespaldo(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $query = \Illuminate\Support\Facades\DB::table('comprobantes2 as c')
            ->select('c.*', 'cl.razonsocial')
            ->leftJoin('clientes as cl', 'c.cliente_id', '=', 'cl.id')
            // Solo documentos de tipo pedido de la tabla comprobantes2
            ->where('c.tipo', 'pedido')
            ->where('c.saldo', '>=', 1);

        if ($buscar != '' && $buscar != 'todos') {
            if ($criterio == 'cliente_id') {
                $query->where('cl.razonsocial', 'LIKE', '%' . $buscar . '%');
            } else if ($criterio == 'total' || $criterio == 'saldo' || $criterio == 'num_comprobante') {
                $val = str_replace(['$', '.', ','], '', $buscar);
                $query->where('c.' . $criterio, 'LIKE', '%' . $val . '%');
            }
        }

        $comprobantes = $query->orderBy('c.fecha', 'desc')->get();

        $pedidos = collect($comprobantes)->map(function ($item) {
            // Asegurar que el objeto se comporte como lo espera el frontend
            $itemArray = (array) $item;

            // Recalcular total real
            $v_total = (float) $item->impuestos > 0 ? ((float) $item->subtotal * 1.19) : (float) $item->subtotal;
            if ($v_total <= 0)
                $v_total = (float) $item->total;

            $itemArray['total'] = $v_total;
            $itemArray['saldo'] = $v_total - (float) $item->abono;

            $itemArray['cliente'] = [
                'id' => $item->cliente_id,
                'razonsocial' => $item->razonsocial,
            ];
            $itemArray['clase_cartera'] = 'Respaldo';
            return $itemArray;
        });

        return [
            'pagination' => [
                'total' => $pedidos->count(),
                'current_page' => 1,
                'per_page' => 1000,
                'last_page' => 1,
                'from' => 1,
                'to' => $pedidos->count(),
            ],
            'pedidos' => [
                'data' => $pedidos
            ]
        ];
    }

    public function carteraProyectada(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador = $request->operador;

        if ($criterio == '' || $buscar == '') {
            $pedidos = Comprobante::with([
                'cliente.empresas',
                'cliente.contactos',
                'cliente.envios',
                'lineas.articulo.tipo.atributos',
                'lineas.orden.detalles.costo.costois',
                'lineas.orden.costos',
                'lineas.orden.status'
            ])
                ->where('tipo', 'pedido')
                ->where('saldo', '>', 0)->orderBy('fecha', 'desc')->paginate(500);
        } else {
            if ($criterio == 'cliente_id') {
                $cliente = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->get();
                if (count($cliente) > 0) {
                    $buscar = $cliente[0]->id;
                }
            }
            $q = Comprobante::with([
                'cliente.empresas',
                'cliente.contactos',
                'cliente.envios',
                'lineas.articulo.tipo.atributos',
                'lineas.orden.detalles.costo.costois',
                'lineas.orden.costos',
                'lineas.orden.status'
            ])
                ->where('tipo', 'pedido')
                ->where('saldo', '>', 0);

            if (!empty($criterio)) {
                $q->where($criterio, $operador, '%' . $buscar . '%');
            }

            $pedidos = $q->orderBy('fecha', 'desc')->paginate(500);
        }

        foreach ($pedidos as $pedi) {
            // Recalcular total real
            $v_total = (float) $pedi->impuestos > 0 ? ((float) $pedi->subtotal * 1.19) : (float) $pedi->subtotal;
            if ($v_total <= 0)
                $v_total = (float) $pedi->total;

            $pedi->total = $v_total;
            $pedi->saldo = $v_total - (float) $pedi->abono;

            foreach ($pedi->lineas as $linea) {
                if ($linea->orden) {
                    $linea->orden->planchas = \App\InventariosMateriaPrima::where('asignado_id', $pedi->cliente_id)
                        ->where('tipo', 'Plancha')->get();
                }
            }
        }

        return [
            'pagination' => [
                'total' => $pedidos->total(),
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'last_page' => $pedidos->lastPage(),
                'from' => $pedidos->firstItem(),
                'to' => $pedidos->lastItem(),
            ],
            'pedidos' => $pedidos
        ];
    }

    public function historialPagos(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $pagos = \App\ReciboPago::with(['cliente', 'comprobante', 'usuario']);

        if ($buscar != '') {
            if ($criterio == 'cliente_id') {
                $pagos = $pagos->whereHas('cliente', function ($q) use ($buscar) {
                    $q->where('razonsocial', 'LIKE', '%' . $buscar . '%');
                });
            } else if (!empty($criterio)) {
                $pagos = $pagos->where($criterio, 'LIKE', '%' . $buscar . '%');
            }
        }

        $pagos = $pagos->orderBy('fecha', 'desc')->paginate(500);

        return [
            'pagination' => [
                'total' => $pagos->total(),
                'current_page' => $pagos->currentPage(),
                'per_page' => $pagos->perPage(),
                'last_page' => $pagos->lastPage(),
                'from' => $pagos->firstItem(),
                'to' => $pagos->lastItem(),
            ],
            'pagos' => $pagos
        ];
    }

    public function resumenPagos(Request $request)
    {
        $resumen = \App\ReciboPago::select('forma_pago', DB::raw('SUM(monto) as total'))
            ->groupBy('forma_pago')
            ->get();

        return $resumen;
    }
    public function filtrarCartera(Request $request)
    {
        if ($request->buscar == 'todos') {
            $buscar = ['D', 'A', 'EP', 'ENP', 'E'];
        } else {
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')
            ->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->select('*', 'articulos.nombre as articulo', 'clientes.razonsocial as rasonsocial', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
            ->whereIn('ordentrabajos.produccion', $buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(20);
        foreach ($ordenes as $orden) {
            $detalles = Detalletrabajo::where('ordentrabajo_id', '=', $orden['idorden'])->select('*', 'detalletrabajos.titulo as titulo_detalle', 'detalletrabajos.descripcion as descripcion_detalle', 'detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles = $detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->idorden)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            $orden->costos = $costos;
        }

        return [
            'buscar' => $buscar,
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function ventas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador = $request->operador;
        $fechai = Carbon::now()->startOfMonth()->toDateString();
        $fechaf = Carbon::now()->toDateString();
        if ($criterio == '' || $buscar == '' || $buscar == 'todos') {
            $pedidos = Comprobante::where('tipo', 'pedido')
                ->whereBetween('fecha', [$fechai, $fechaf])
                ->whereHas('lineas.orden', function ($q) {
                    $q->whereIn('produccion', ['P', 'EP', 'ENP', 'EM', 'A', 'D', 'T']);
                })
                ->orderBy('fecha', 'desc')->paginate(500);
            foreach ($pedidos as $pedi) {
                $pedi->cliente;
                $pedi->lineas;
            }
        } else {
            if ($criterio == 'cliente_id') {
                $cliente = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->get();
                if (count($cliente) > 0) {
                    $buscar = $cliente[0]->id;
                }
            }
            $q = Comprobante::where('tipo', 'pedido')
                ->whereBetween('fecha', [$fechai, $fechaf])
                ->whereHas('lineas.orden', function ($q) {
                    $q->whereIn('produccion', ['P', 'EP', 'ENP', 'EM', 'A', 'D', 'T']);
                });

            if (!empty($criterio) && $buscar != 'todos' && $buscar != '') {
                $q->where($criterio, $operador, '%' . $buscar . '%');
            }

            $pedidos = $q->orderBy('fecha', 'desc')->paginate(500);
            foreach ($pedidos as $pedi) {
                $pedi->cliente;
                $pedi->lineas;
            }
        }
        // foreach($ordenes as $orden){
        //     $costos=CostoProduccion::select(DB::raw('SUM(total) as totalcosto'))->where('ordentrabajo_id','=', $orden->idorden)->get();
        //     $orden->costos+=$costos[0]->totalcosto;
        // }

        return [
            'pagination' => [
                'total' => $pedidos->total(),
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'last_page' => $pedidos->lastPage(),
                'from' => $pedidos->firstItem(),
                'to' => $pedidos->lastItem(),
            ],
            'pedidos' => $pedidos
        ];
    }
    public function filtrarFechaVentas(Request $request)
    {
        $filtroFecha = $request->filtroFecha;
        switch ($filtroFecha) {
            case 'hoy':
                $fechai = date('Y-m-d');
                $fechaf = date('Y-m-d');
                break;
            case 'ayer':
                $date = date('Y-m-d');
                $mod_date = strtotime($date . "- 1 days");
                $fechai = date('Y-m-d', $mod_date);
                $fechaf = date('Y-m-d', $mod_date);
                break;
            case 'ultimos7':
                $date = date('Y-m-d');
                $mod_date = strtotime($date . "- 7 days");
                $fechai = date('Y-m-d', $mod_date);
                $fechaf = date('Y-m-d');
                break;
            case 'ultimos30':
                $date = date('Y-m-d');
                $mod_date = strtotime($date . "- 30 days");
                $fechai = date('Y-m-d', $mod_date);
                $fechaf = date('Y-m-d');
                break;
            case 'mes':
                $fechai = date('Y-m') . '-01';
                $fechaf = date('Y-m-d');
                break;
            case 'semana':
                $diaSemana = date("w");
                # Calcular el tiempo (no la fecha) de cuándo fue el inicio de semana
                $tiempoDeInicioDeSemana = strtotime("-" . $diaSemana . " days"); # Restamos -X days
                # Y formateamos ese tiempo
                $fechai = date("Y-m-d", $tiempoDeInicioDeSemana);
                # Ahora para el fin, sumamos
                $tiempoDeFinDeSemana = strtotime("+" . $diaSemana . " days", $tiempoDeInicioDeSemana); # Sumamos +X days, pero partiendo del tiempo de inicio
                # Y formateamos
                $fechaf = date("Y-m-d", $tiempoDeFinDeSemana);
                break;
            default:
                $intervalo = explode(",", $filtroFecha);
                $fechai = $intervalo[0];
                $fechaf = $intervalo[1];
        }
        $pedidos = Comprobante::where('tipo', 'pedido')
            ->whereBetween('fecha', [$fechai, $fechaf])
            ->whereHas('lineas.orden', function ($q) {
                $q->whereIn('produccion', ['P', 'EP', 'ENP', 'EM', 'A', 'D', 'T']);
            })
            ->orderBy('fecha', 'desc')->paginate(500);
        foreach ($pedidos as $pedi) {
            $pedi->cliente;
            $pedi->articulo;
            $pedi->lineas;
        }


        return [
            'pagination' => [
                'total' => $pedidos->total(),
                'current_page' => $pedidos->currentPage(),
                'per_page' => $pedidos->perPage(),
                'last_page' => $pedidos->lastPage(),
                'from' => $pedidos->firstItem(),
                'to' => $pedidos->lastItem(),
            ],
            'pedidos' => $pedidos
        ];
    }
    public function actualizarPapel(Request $request)
    {
        $papeles = json_decode($request->data);
        foreach ($papeles as $papel) {
            $orden = Ordentrabajo::find($papel->id);
            $orden->medida_final = $papel->medida_final;
            $orden->tamano = $papel->tamano;
            $orden->medida_material = $papel->medida_material;
            $orden->cabida = $papel->cabida;
            $orden->carpeta_cliente = $papel->carpeta_cliente;
            $orden->save();
            if ($papel->costos->id > 0) {
                $costo = CostoProduccion::find($papel->costos->id);
            } else {
                $costo = new CostoProduccion();

            }
            $costo->costois_id = $papel->costos->costois_id;
            $costo->titulo = $papel->costos->titulo;
            $costo->descripcion = $papel->costos->descripcion;
            $costo->cantidad = $papel->costos->cantidad;
            $costo->valor = 0;
            $costo->total = 0;
            $costo->orden = 1;
            $costo->ordentrabajo_id = $papel->id;
            $costo->save();

            if (isset($papel->detalle->id)) {
                $detalle = Detalletrabajo::find($papel->detalle->id);
                $detalle->valor = $papel->detalle->valor;
            } else {
                $detalle = new Detalletrabajo();
                $detalle->valor = $papel->costos->costo_nombre;
            }
            $detalle->costos_id = $costo->id;

            $detalle->titulo = 'Papel';
            $detalle->descripcion = $papel->detalle->descripcion;
            $detalle->ordentrabajo_id = $papel->id;
            $detalle->save();

        }

        return $papeles;
    }
    public function estadisticas()
    {
        // SELECT *, costos.updated_at as 'fecha' FROM `costos` JOIN costois ON costos.costois_id = costois.id WHERE costois.nombre LIKE '%ntigra%' AND costois.tipo_costo='papel' AND costos.updated_at BETWEEN '2022-07-10' AND '2022-09-15'

    }
    public function reporteProyeccionPapel(Request $request)
    {
        $ordenesStatus = StatusProduccion::join('ordentrabajos', 'statusproduccion.idorden', '=', 'ordentrabajos.id')
            ->where('statusproduccion.estado', $request->statusorden)
            ->whereIn('ordentrabajos.produccion', ['ENP'])
            ->select('ordentrabajos.*')
            ->get();
        foreach ($ordenesStatus as $orden) {
            $orden->cliente = Cliente::find($orden->cliente_id);
            $orden->articulo = Articulo::find($orden->articulo_id);
            $detalles = Detalletrabajo::where('titulo', 'Papel')->where('ordentrabajo_id', $orden->id)->get();
            if (count($detalles) > 0) {
                $orden->detalle = $detalles[0];
            } else {
                $orden->detalle = (object) [
                    'ordentrabajo_id' => $orden->id,
                    'costos_id' => 0,
                    'Titulo' => 'Papel',
                    'descripcion' => '',
                    'valor' => ''
                ];

            }
            $costos = Costo::join('costois', 'costos.costois_id', '=', 'costois.id')
                ->where('costos.id', $orden->detalle->costos_id)
                ->select('costos.*', 'costois.nombre as costo_nombre')
                ->get();
            if (count($costos) > 0) {
                $orden->costos = $costos[0];
            } else {
                $orden->costos = (object) [
                    'cantidad' => 1,
                    'costo_nombre' => '',
                    'costois_id' => 0,
                    'descripcion' => 1,
                    'id' => 0,
                    'ordentrabajo_id' => $orden->id,
                    'titulo' => 'Papel',
                ];
                $orden->detalle->costos_id = 0;

            }
        }
        return $ordenesStatus;
    }
    public function filtrarVentas(Request $request)
    {
        if ($request->buscar == 'todos') {
            $buscar = ['D', 'A', 'EP', 'ENP', 'EM', 'E'];
        } else {
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')
            ->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->select('*', 'articulos.nombre as articulo', 'clientes.razonsocial as rasonsocial', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
            ->whereIn('ordentrabajos.produccion', $buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(2000);
        foreach ($ordenes as $orden) {
            $detalles = Detalletrabajo::where('ordentrabajo_id', '=', $orden['idorden'])->select('*', 'detalletrabajos.titulo as titulo_detalle', 'detalletrabajos.descripcion as descripcion_detalle', 'detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles = $detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->idorden)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            $orden->costos = $costos;
        }

        return [
            'buscar' => $buscar,
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }

    public function filtrarFecha(Request $request)
    {
        $filtroFecha = $request->filtroFecha;
        switch ($filtroFecha) {
            case 'hoy':
                $fechai = date('Y-m-d');
                $fechaf = date('Y-m-d');
                break;
            case 'ayer':
                $date = date('Y-m-d');
                $mod_date = strtotime($date . "- 1 days");
                $fechai = date('Y-m-d', $mod_date);
                $fechaf = date('Y-m-d', $mod_date);
                break;
            case 'ultimos7':
                $date = date('Y-m-d');
                $mod_date = strtotime($date . "- 7 days");
                $fechai = date('Y-m-d', $mod_date);
                $fechaf = date('Y-m-d');
                break;
            case 'ultimos30':
                $date = date('Y-m-d');
                $mod_date = strtotime($date . "- 30 days");
                $fechai = date('Y-m-d', $mod_date);
                $fechaf = date('Y-m-d');
                break;
            case 'mes':
                $diaMes = date("t") - 1;
                $fechai = date('Y') . '-' . date('m') . '-1';
                $tiempoDeInicioDeMes = strtotime($fechai); # Restamos -X days
                $tiempoDeFinDeMes = strtotime("+" . $diaMes . " days", $tiempoDeInicioDeMes); # Sumamos +X days, pero partiendo del tiempo de inicio
                $fechaf = date("Y-m-d", $tiempoDeFinDeMes);
                break;
            case 'semana':
                $diaSemana = date("w");
                # Calcular el tiempo (no la fecha) de cuándo fue el inicio de semana
                $tiempoDeInicioDeSemana = strtotime("-" . $diaSemana . " days"); # Restamos -X days
                # Y formateamos ese tiempo
                $fechai = date("Y-m-d", $tiempoDeInicioDeSemana);
                # Ahora para el fin, sumamos
                $tiempoDeFinDeSemana = strtotime("+" . $diaSemana . " days", $tiempoDeInicioDeSemana); # Sumamos +X days, pero partiendo del tiempo de inicio
                # Y formateamos
                $fechaf = date("Y-m-d", $tiempoDeFinDeSemana);
                break;
            default:
                $intervalo = explode(",", $filtroFecha);
                $fechai = $intervalo[0];
                $fechaf = $intervalo[1];
                ;
        }
        $ordenes = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')
            ->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->select('*', 'articulos.nombre as articulo', 'clientes.razonsocial as rasonsocial', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
            ->whereBetween('ordentrabajos.created_at', [$fechai, $fechaf])->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(1000);
        foreach ($ordenes as $orden) {
            $detalles = Detalletrabajo::where('ordentrabajo_id', '=', $orden['idorden'])->select('*', 'detalletrabajos.titulo as titulo_detalle', 'detalletrabajos.descripcion as descripcion_detalle', 'detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles = $detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->idorden)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            $orden->costos = $costos;
        }

        return [
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function filtrarOrdenes(Request $request)
    {
        if ($request->buscar == 'todos' || empty($request->buscar)) {
            $buscar = ['D', 'A', 'EP', 'ENP', 'EM', 'E'];
        } else {
            $buscar = [$request->buscar];
        }

        $ordenes = Ordentrabajo::select()->whereIn('ordentrabajos.produccion', $buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(1000);

        foreach ($ordenes as $orden) {
            foreach ($orden->detalles as $detalle) {
                if ($detalle->titulo == 'Papel') {
                    if ($detalle->costo) {
                        if ($detalle->costo->costois) {

                        }

                    } else {
                        $detalle->costo = null;
                    }
                }
            }
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->id)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            $orden->costos = $costos;
            $orden->articulo;
            $orden->cliente;
            $newDate = date("Y-m-d", strtotime($orden->created_at));
            $orden->fecha_orden = $newDate;

        }

        return [
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];

    }

    public function filtrarEstadoc(Request $request)
    {
        if ($request->buscar == 'todos') {
            $buscar = ['C', 'PC', 'PA', 'VC', 'P', 'A'];
        } else {
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')
            ->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->select('*', 'articulos.nombre as articulo', 'clientes.razonsocial as rasonsocial', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
            ->whereIn('ordentrabajos.estado', $buscar)->orderBy('ordentrabajos.created_at', 'ASC')->paginate(50);
        foreach ($ordenes as $orden) {
            $detalles = Detalletrabajo::where('ordentrabajo_id', '=', $orden['idorden'])->select('*', 'detalletrabajos.titulo as titulo_detalle', 'detalletrabajos.descripcion as descripcion_detalle', 'detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles = $detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->idorden)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            $orden->costos = $costos;
        }

        return [
            'buscar' => $buscar,
            'pagination' => [
                'total' => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page' => $ordenes->perPage(),
                'last_page' => $ordenes->lastPage(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }

    public function procesos(Request $request)
    {
        $buscar = json_decode($request->buscar);
        $operador = $request->operador;
        $procesos = array();
        for ($i = 0; $i < count($buscar); $i++) {

            $ordenes = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')->join('costos', 'ordentrabajos.id', '=', 'costos.ordentrabajo_id')->join('costois', 'costos.costois_id', '=', 'costois.id')->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')
                ->select('*', 'articulos.nombre as articulo', 'clientes.razonsocial as cliente', 'costos.id as idcosto', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
                ->whereNotIn('costos.terminado', [1, true])->whereIn('ordentrabajos.produccion', ['EP', 'ENP'])->where('costos.titulo', $buscar[$i])->orderBy('ordentrabajos.id', 'desc')->get();
            array_push($procesos, $ordenes);
        }
        return ['procesos' => $procesos[0], 'buscar' => $buscar];
    }
    public function ordenesProduccion()
    {
        $buscar = ['EP', 'ENP'];

        $ordenes = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')
            ->join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->join('statusproduccion', 'ordentrabajos.id', '=', 'statusproduccion.idorden')->select('*', 'statusproduccion.observaciones as obser', 'articulos.nombre as articulo', 'clientes.razonsocial as rasonsocial', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
            ->whereIn('ordentrabajos.produccion', $buscar)->where('statusproduccion.estado', '=', 2)->orderBy('ordentrabajos.fecha', 'ASC')->get();
        foreach ($ordenes as $orden) {
            $detalles = Detalletrabajo::where('ordentrabajo_id', '=', $orden['idorden'])->select('*', 'detalletrabajos.titulo as titulo_detalle', 'detalletrabajos.descripcion as descripcion_detalle', 'detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles = $detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')->where('ordentrabajo_id', '=', $orden->idorden)->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')->get();
            $orden->costos = $costos;
            // if($orden->impresa==false){
            //     $impresa=0;
            // }else{
            //     $impresa=1;
            // }
            // $orden->impresa=$impresa;
        }
        return $ordenes;
    }

    public function reporteExcelOrdenes(Request $request)
    {
        $datos = json_decode($request->datos);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()->setCreator('Julian Agudelo')->setTitle('Ordenes-Produccion');
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,

                ],
            ],


        ];
        $styleArray2 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                ],
            ],
        ];

        $borde = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,

                ],
            ],
        ];

        $fecha = date('Y-m-d');
        $spreadsheet->setActiveSheetIndex(0);
        $hojaactiva = $spreadsheet->getActiveSheet();
        $hojaactiva->getColumnDimension('A')->setWidth(42);
        $hojaactiva->getColumnDimension('B')->setWidth(30);
        $hojaactiva->getColumnDimension('C')->setWidth(30);
        $i = 1;
        for ($c = 0; $c < count($datos); $c++) {
            if (isset($datos[$c]->papeles)) {
                $datos[$c]->papel = $datos[$c]->papeles;
            }
            if (is_object($datos[$c]->articulo)) {
                $datos[$c]->articulo = $datos[$c]->articulo->nombre;
            }
            if ($datos[$c]->plancha == 1) {
                $plancha = 'Existe';
            } else {
                $plancha = 'Nueva';
            }
            $or = OrdenTrabajo::find($datos[$c]->idorden);
            $or->impresa = $datos[$c]->impresa;
            $or->save();
            $t = $i + 11 + 8;
            if ($datos[$c]->prioridad == 'normal') {
                $prioridadcolor = '00CC00';
            } elseif ($datos[$c]->prioridad == 'media') {
                $prioridadcolor = 'FF9900';
            } else {
                $prioridadcolor = 'FF0000';
            }
            $hojaactiva->mergeCells('A' . $i . ':C' . $i);
            $hojaactiva->getRowDimension($i)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 1)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 2)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 3)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 4)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 5)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 6)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 7)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 8)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 9)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 10)->setRowHeight(20);
            $hojaactiva->getRowDimension($i + 11)->setRowHeight(20);
            $hojaactiva->getStyle('A' . $i . ':C' . $t)->applyFromArray($borde);
            $hojaactiva->getStyle('A' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('00CC00');
            $hojaactiva->getStyle('A' . ($i + 1) . ':C' . ($i + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            $hojaactiva->getStyle('A' . ($i + 5) . ':C' . ($i + 5))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            $hojaactiva->getStyle('A' . ($i + 3) . ':C' . ($i + 3))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            $hojaactiva->getStyle('A' . ($i + 7))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            $hojaactiva->getStyle('A' . ($i + 9))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            $hojaactiva->getStyle('A' . ($i + 11))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            $hojaactiva->getStyle('B' . ($i + 2))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($prioridadcolor);
            $hojaactiva->setCellValue('A' . $i, 'ORDEN DE PRODUCCIÓN ' . $datos[$c]->idorden)->getStyle('A' . $i . ':C' . $i)->applyFromArray($styleArray);
            $hojaactiva->setCellValue('A' . ($i + 1), 'FECHA')->getStyle('A' . ($i + 1))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('B' . ($i + 1), 'PRIORIDAD')->getStyle('B' . ($i + 1))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('C' . ($i + 1), 'FECHA ENTREGA | PLANCHA')->getStyle('C' . ($i + 1))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('A' . ($i + 3), 'CLIENTE')->getStyle('A' . ($i + 3))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('B' . ($i + 3), 'TRABAJO')->getStyle('B' . ($i + 3))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('C' . ($i + 3), 'CANTIDAD')->getStyle('C' . ($i + 3))->applyFromArray($styleArray);
            $hojaactiva->mergeCells('A' . ($i + 5) . ':B' . ($i + 5))->getStyle('A' . ($i + 5) . ':B' . ($i + 5))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('A' . ($i + 5), 'MATERIAL - MEDIDA - PLIEGOS');
            $hojaactiva->setCellValue('C' . ($i + 5), 'CANTIDAD TAMAÑOS')->getStyle('C' . ($i + 5))->applyFromArray($styleArray);
            $hojaactiva->mergeCells('B' . ($i + 7) . ':C' . ($i + 7))->getStyle('B' . ($i + 7) . ':C' . ($i + 7))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('B' . ($i + 7), 'DETALLES DE DISEÑO');
            $hojaactiva->mergeCells('A' . ($i + 7))->getStyle('A' . ($i + 7))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('A' . ($i + 7), 'MEDIDA FINAL TRABAJO');
            $hojaactiva->mergeCells('A' . ($i + 9) . ':C' . ($i + 9))->getStyle('A' . ($i + 9) . ':C' . ($i + 9))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('A' . ($i + 9), 'OBSERVACIONES');
            $hojaactiva->mergeCells('A' . ($i + 11) . ':C' . ($i + 11))->getStyle('A' . ($i + 11) . ':C' . ($i + 11))->applyFromArray($styleArray);
            $hojaactiva->setCellValue('A' . ($i + 11), 'ESPECIFICACIONES');
            $hojaactiva->setCellValue('A' . ($i + 2), 'Creción: ' . $datos[$c]->fecha . ' - Producción: ' . $fecha)->setCellValue('B' . ($i + 2), $datos[$c]->prioridad)->setCellValue('C' . ($i + 2), $datos[$c]->fecha_entrega . ' | ' . $plancha);
            $hojaactiva->setCellValue('A' . ($i + 4), $datos[$c]->rasonsocial)->setCellValue('B' . ($i + 4), $datos[$c]->articulo)->setCellValue('C' . ($i + 4), $datos[$c]->cantidad);
            $hojaactiva->setCellValue('A' . ($i + 6), $datos[$c]->papel . ' Cabida:' . $datos[$c]->cabida)->setCellValue('C' . ($i + 6), $datos[$c]->tamanos . ' + ' . $datos[$c]->carpeta_cliente . ' de sobrante');
            $hojaactiva->setCellValue('B' . ($i + 8), $datos[$c]->detalles_diseno)->getStyle('B' . ($i + 8) . ':C' . ($i + 8));
            $hojaactiva->setCellValue('A' . ($i + 8), $datos[$c]->medida_final)->getStyle('A' . ($i + 8) . ':C' . ($i + 8));
            $hojaactiva->setCellValue('A' . ($i + 10), $datos[$c]->observaciones)->getStyle('A' . ($i + 10) . ':C' . ($i + 10));
            for ($j = 0; $j < count($datos[$c]->detalles); $j++) {
                if ($datos[$c]->detalles[$j]->titulo == 'Tinta' || $datos[$c]->detalles[$j]->titulo == 'tinta') {
                    if (is_array($datos[$c]->detalles[$j]->valor)) {

                        $colores = '';
                        foreach ($datos[$c]->detalles[$j]->valor as $valor) {
                            $colores = $colores . ', Pantone ' . $valor->pantone;
                        }
                        $hojaactiva->setCellValue('A' . (($i + 12) + $j), $datos[$c]->detalles[$j]->titulo)->setCellValue('B' . (($i + 12) + $j), $colores)->setCellValue('C' . (($i + 12) + $j), $datos[$c]->detalles[$j]->descripcion);
                    }
                } else {
                    $hojaactiva->setCellValue('A' . (($i + 12) + $j), $datos[$c]->detalles[$j]->titulo)->setCellValue('B' . (($i + 12) + $j), $datos[$c]->detalles[$j]->valor)->setCellValue('C' . (($i + 12) + $j), $datos[$c]->detalles[$j]->descripcion);
                }
            }
            $e = 14 + 8;
            $i = $i + $e;

        }

        $writer = new Xlsx($spreadsheet);
        $url = "reportes/";
        $archivo = "op_" . date('Y-m-d ') . "-" . date('G') . "-" . date('i') . "-" . date('s') . ".xlsx";
        $narchivo = $url . $archivo;
        $writer->save($narchivo);

        $reporte = new Reportes();
        $reporte->iduser = 0;
        $reporte->archivo = $archivo;
        $reporte->fecha = date('Y-m-d ');
        $reporte->tipo = 'op';
        $reporte->estado = 0;
        $reporte->save();
        return $archivo;
    }
    public function reporteExcelProcesos(Request $request)
    {
        $datos = json_decode($request->datos);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Julian Agudelo')->setTitle('reporte');
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                ],
            ],

        ];
        $styleArray2 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                ],
            ],

        ];
        switch ($request->tipo) {
            case 'Papel':
                foreach ($datos as $pa) {
                    $costos = CostoProduccion::find($pa->papel->id);
                    if ($pa->papel->completado) {
                        $costos->completado = 1;
                    }
                    if ($pa->terminado) {
                        $costos->papel->terminado = 1;
                    }
                    $costos->save();
                }
                $spreadsheet->setActiveSheetIndex(0);
                $hojaactiva = $spreadsheet->getActiveSheet();
                $hojaactiva->setCellValue('A1', 'ORDEN DE CORTE');
                $hojaactiva->setCellValue('A2', 'FECHA');
                date('Y-m-d');
                $hojaactiva->setCellValue('A3', 'No. ORDEN')->setCellValue('B3', 'PLIEGOS')->setCellValue('C3', 'MATERIAL')->setCellValue('D3', 'CORTE')->setCellValue('E3', 'TAMAÑOS')->setCellValue('F3', 'REF. PRODUCTO')->setCellValue('G3', 'CLIENTE ');
                ;
                $hojaactiva->getColumnDimension('B')->setWidth(10);
                $hojaactiva->getColumnDimension('c')->setWidth(30);
                $hojaactiva->getColumnDimension('A')->setWidth(5);
                $hojaactiva->getColumnDimension('D')->setWidth(10);
                $hojaactiva->getColumnDimension('E')->setWidth(20);
                $hojaactiva->getColumnDimension('F')->setWidth(20);
                $hojaactiva->getColumnDimension('G')->setWidth(25);
                $hojaactiva->getStyle('A3')->applyFromArray($styleArray);
                $hojaactiva->getStyle('B3')->applyFromArray($styleArray);
                $hojaactiva->getStyle('C3')->applyFromArray($styleArray);
                $hojaactiva->getStyle('E3')->applyFromArray($styleArray);
                $hojaactiva->getStyle('D3')->applyFromArray($styleArray);
                $hojaactiva->getStyle('F3')->applyFromArray($styleArray);
                $hojaactiva->getStyle('G3')->applyFromArray($styleArray);
                $hojaactiva->setCellValue('B2', date('Y-m-d '));
                $i = 4;
                foreach ($datos as $cot) {

                    $hojaactiva->setCellValue('A' . $i, $cot->id)->setCellValue('B' . $i, $cot->papel->cantidad)->setCellValue('C' . $i, $cot->papel->costois->nombre)->setCellValue('D' . $i, $cot->medida_material)->setCellValue('E' . $i, $cot->papel->descripcion)->setCellValue('F' . $i, $cot->articulo->nombre)->setCellValue('G' . $i, $cot->cliente->razonsocial);
                    $i++;
                }
                $hojaactiva->getStyle('A4:G' . $i)->applyFromArray($styleArray2);

                $writer = new Xlsx($spreadsheet);
                $url = "reportes/";
                $archivo = $request->tipo . "_" . date('Y-m-d ') . "-" . date('G') . "-" . date('i') . "-" . date('s') . ".xlsx";
                $narchivo = $url . $archivo;
                $writer->save($narchivo);
                $reporte = new Reportes();
                $reporte->iduser = 0;
                $reporte->archivo = $archivo;
                $reporte->fecha = date('Y-m-d ');
                $reporte->tipo = $request->tipo;
                $reporte->estado = 0;
                $reporte->save();
                break;
            case 'Tiraje':
                foreach ($datos as $pa) {

                    if (count($pa->filas) > 0) {
                        foreach ($pa->filas as $fi) {
                            $costos = CostoProduccion::find($fi->idCosto);
                            if ($fi->completado) {
                                $costos->completado = 1;
                            }
                            if ($fi->terminado) {
                                $costos->terminado = 1;
                            }
                            $costos->save();
                        }
                    }
                }
                $i = 0;
                foreach ($datos as $ex) {
                    if (count($ex->filas) > 0) {
                        $spreadsheet->setActiveSheetIndex($i);
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('A1', 'ORDEN DE IMPRESION');
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('A2', 'FECHA:');
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('B2', date('Y-m-d h:i:s A'));
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('C2', 'IMPRESOR:');
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('D2', $ex->impreso->proveedor);
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('A3', 'CANTIDAD')->setCellValue('B3', 'REFERENCIA')->setCellValue('C3', 'MATERIAL')->setCellValue('D3', 'MEDIDA')->setCellValue('E3', 'MAQUINA')->setCellValue('F3', 'COLOR TINTA')->setCellValue('G3', 'PLANCHA')->setCellValue('H3', 'PRIORIDAD');
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('A')->setWidth(15);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('B')->setWidth(40);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('C')->setWidth(40);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('D')->setWidth(15);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('E')->setWidth(30);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('F')->setWidth(15);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('G')->setWidth(15);
                        $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('H')->setWidth(15);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('A3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('B3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('C3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('E3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('D3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('F3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('G3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('H3')->applyFromArray($styleArray);
                        $spreadsheet->setActiveSheetIndex($i)->setTitle($ex->impreso->proveedor);
                        $j = 4;
                        foreach ($ex->filas as $fi) {
                            $spreadsheet->setActiveSheetIndex($i)->setCellValue('A' . $j, $fi->cantidad)->setCellValue('B' . $j, $fi->referencia)->setCellValue('C' . $j, $fi->material)->setCellValue('D' . $j, $fi->medida)->setCellValue('E' . $j, $fi->maquina)->setCellValue('F' . $j, $fi->color)->setCellValue('G' . $j, $fi->plancha)->setCellValue('H' . $j, $fi->prioridad);
                            $spreadsheet->setActiveSheetIndex($i)->getStyle('A' . $j . ':H' . $j)->applyFromArray($styleArray2);
                            $j++;
                            $spreadsheet->createSheet();
                        }
                        // $spreadsheet->getActiveSheet()->setCellValue('A'.$i, $cot->cant)->setCellValue('B'.$i, $cot->nombre)->setCellValue('C'.$i, $cot->descripcion)->setCellValue('D'.$i, '')->setCellValue('E'.$i,'');
                        $i++;
                    }

                }

                if (empty($datos->idproveedor)) {
                    $proveedor = array();
                } else {
                    $proveedor = \App\Proveedor::where('id', $datos->idproveedor)->get();
                }
                $writer = new Xlsx($spreadsheet);
                $url = "reportes/";
                $archivo = $request->tipo . "_" . date('Y-m-d ') . "-" . date('G') . "-" . date('i') . "-" . date('s') . ".xlsx";
                $narchivo = $url . $archivo;
                $writer->save($narchivo);
                $reporte = new Reportes();
                $reporte->iduser = 0;
                $reporte->archivo = $archivo;
                $reporte->fecha = date('Y-m-d');
                $reporte->tipo = $request->tipo;
                $reporte->estado = 0;
                $reporte->save();
                break;
            case 'Troquelado':
                foreach ($datos as $pa) {
                    if (count($pa->filas) > 0) {
                        foreach ($pa->filas as $fi) {
                            $costos = CostoProduccion::find($fi->idCosto);
                            $costos->pago = $fi->pago;
                            $costos->save();
                        }
                    }
                }
                $i = 0;
                foreach ($datos as $ex) {
                    $spreadsheet->setActiveSheetIndex($i);
                    $spreadsheet->setActiveSheetIndex($i)->setCellValue('A1', 'ORDEN DE TROQUELADO');
                    $spreadsheet->setActiveSheetIndex($i)->setCellValue('A2', 'FECHA:');
                    $spreadsheet->setActiveSheetIndex($i)->setCellValue('B2', date('Y-m-d h:i:s A'));
                    $spreadsheet->setActiveSheetIndex($i)->setCellValue('C2', 'PROVEEDOR:');
                    $spreadsheet->setActiveSheetIndex($i)->setCellValue('D2', $ex->impreso->proveedor);
                    $spreadsheet->setActiveSheetIndex($i)->setCellValue('A3', 'FEHCA')->setCellValue('B3', 'CANTIDAD')->setCellValue('C3', 'REFERENCIA')->setCellValue('D3', 'MAQUINA')->setCellValue('E3', 'PRIORIDAD');
                    $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('A')->setWidth(15);
                    $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('B')->setWidth(15);
                    $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('C')->setWidth(30);
                    $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('D')->setWidth(30);
                    $spreadsheet->setActiveSheetIndex($i)->getColumnDimension('E')->setWidth(15);
                    $spreadsheet->setActiveSheetIndex($i)->getStyle('A3')->applyFromArray($styleArray);
                    $spreadsheet->setActiveSheetIndex($i)->getStyle('B3')->applyFromArray($styleArray);
                    $spreadsheet->setActiveSheetIndex($i)->getStyle('C3')->applyFromArray($styleArray);
                    $spreadsheet->setActiveSheetIndex($i)->getStyle('E3')->applyFromArray($styleArray);
                    $spreadsheet->setActiveSheetIndex($i)->getStyle('D3')->applyFromArray($styleArray);

                    $spreadsheet->setActiveSheetIndex($i)->setTitle($ex->impreso->proveedor);
                    $j = 4;
                    foreach ($ex->filas as $fi) {
                        $spreadsheet->setActiveSheetIndex($i)->setCellValue('A' . $j, $fi->fecha)->setCellValue('B' . $j, $fi->cantidad)->setCellValue('C' . $j, $fi->referencia)->setCellValue('D' . $j, $fi->maquina)->setCellValue('E' . $j, $fi->prioridad);
                        $spreadsheet->setActiveSheetIndex($i)->getStyle('A' . $j . ':E' . $j)->applyFromArray($styleArray2);
                        $j++;
                    }
                    $spreadsheet->createSheet();

                    // $spreadsheet->getActiveSheet()->setCellValue('A'.$i, $cot->cant)->setCellValue('B'.$i, $cot->nombre)->setCellValue('C'.$i, $cot->descripcion)->setCellValue('D'.$i, '')->setCellValue('E'.$i,'');
                    $i++;
                }


                $writer = new Xlsx($spreadsheet);
                $url = "reportes/";
                $archivo = $request->tipo . "_" . date('Y-m-d ') . "-" . date('G') . "-" . date('i') . "-" . date('s') . ".xlsx";
                $narchivo = $url . $archivo;
                $writer->save($narchivo);
                $reporte = new Reportes();
                $reporte->iduser = 0;
                $reporte->archivo = $archivo;
                $reporte->fecha = date('Y-m-d');
                $reporte->tipo = $request->tipo;
                $reporte->estado = 0;
                $reporte->save();
                return $datos;
                break;
            //return ['resultado'=>$resultado];
        }
    }
    public function reporteProcesos(Request $request)
    {
        $datos = json_decode($request->datos);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Julian Agudelo')->setTitle('reporte');

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                ],
            ],

        ];
        $styleArray2 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                ],
            ],

        ];
        switch ($datos->tipo) {
            case 'Papel':
                $ordenes = Ordentrabajo::select('*', 'ordentrabajos.id', 'statusproduccion.id as ids')->join('statusproduccion', 'ordentrabajos.id', '=', 'statusproduccion.idorden')->whereIn('ordentrabajos.produccion', ['ENP', 'EM', 'E', 'D', 'EP'])->where('statusproduccion.estado', 1)->get();
                foreach ($ordenes as $orden) {
                    $orden->status;
                    if (count($orden->papel) != 0) {
                        foreach ($orden->papel as $papel) {
                            $papel->costois;
                        }
                        ;
                    }

                    $orden->cliente;
                    $orden->articulo;
                }

                return $ordenes;


                break;
            case 'Tiraje':
                $finicial = $datos->fechaI;
                $ffinal = $datos->fechaF;
                $tira = Ordentrabajo::join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->join('costos', 'ordentrabajos.id', '=', 'costos.ordentrabajo_id')
                    ->join('costois', 'costos.costois_id', '=', 'costois.id')->join('proveedores', 'costois.idpersona', '=', 'proveedores.id')
                    ->select('*', 'proveedores.nombre as proveedor', 'articulos.nombre as item', 'costois.nombre as insumo', 'costos.id as idCosto', 'costos.total as totalCosto')->whereIn('ordentrabajos.produccion', ['ENP', 'EP'])
                    ->where('costos.titulo', $datos->tipo)->where('costos.completado', $datos->iniciado)->Where('costos.terminado', $datos->terminado)->orWhereBetween('fecha', [$finicial, $ffinal])->get();
                $impresor = Costois::join('proveedores', 'costois.idpersona', '=', 'proveedores.id')->where('costois.tipo_costo', $datos->tipo)->selectRaw('proveedores.nombre as proveedor')->groupBy('proveedores.nombre')->get();
                $excel = array();
                foreach ($impresor as $im) {
                    $impreso = [
                        'impreso' => $im,
                        'filas' => []
                    ];
                    foreach ($tira as $t) {

                        if ($t->proveedor == $im->proveedor) {
                            $cliente = Cliente::select('clientes.razonsocial as cliente_nom')->where('clientes.id', $t->idcliente)->get();
                            // return $cliente;
                            $datospapel = Costo::join('costois', 'costos.costois_id', '=', 'costois.id')->where('costos.ordentrabajo_id', $t->idorden)->where('costos.titulo', 'Papel')->get();
                            $plancha = 'Nueva';
                            if ($t->plancha == 1) {
                                $plancha = 'Existe';
                            }
                            if ($t->pago) {
                                $pago = 1;
                            } else {
                                $pago = 0;
                            }
                            if ($t->completado) {
                                $completado = 1;
                            } else {
                                $completado = 0;
                            }
                            if ($t->terminado) {
                                $terminado = 1;
                            } else {
                                $terminado = 0;
                            }
                            $descripcion = $datospapel[0]->descripcion;
                            $nombre = $datospapel[0]->nombre;
                            array_push($impreso['filas'], [
                                'fecha' => $t->fecha,
                                'cantidad' => $t->cantidad * 1000,
                                'referencia' => $cliente[0]->cliente_nom . ' ' . $t->item,
                                'material' => $nombre,
                                'maquina' => $t->insumo,
                                'medida' => $descripcion,
                                'color' => $t->descripcion,
                                'prioridad' => $t->prioridad,
                                'pago' => $t->pago,
                                'plancha' => $plancha,
                                'idCosto' => $t->idCosto,
                                'completado' => $completado,
                                'terminado' => $terminado,
                                'valor' => $t->totalCosto
                            ]);
                        }

                    }
                    array_push($excel, $impreso);

                }

                return $excel;
                break;
            case 'Troquelado':

                $finicial = $datos->fechaI;
                $ffinal = $datos->fechaF;
                // $finicial= new Carbon($datos->fechaI);
                // $ffinal= new Carbon($datos->fechaF);
                $tira = Ordentrabajo::join('articulos', 'ordentrabajos.articulo_id', '=', 'articulos.id')->join('costos', 'ordentrabajos.id', '=', 'costos.ordentrabajo_id')
                    ->join('costois', 'costos.costois_id', '=', 'costois.id')->join('proveedores', 'costois.idpersona', '=', 'proveedores.id')
                    ->select('*', 'costos.id as idCosto', 'proveedores.nombre as proveedor', 'articulos.nombre as item', 'costois.nombre as insumo', 'costos.total as totalCosto')
                    ->where('costos.titulo', $datos->tipo)->Where('costos.pago', $datos->pago)->orWhereBetween('fecha', [$finicial, $ffinal])->get();
                $impresor = Costois::join('proveedores', 'costois.idpersona', '=', 'proveedores.id')->where('costois.tipo_costo', $datos->tipo)->selectRaw('proveedores.nombre as proveedor')->groupBy('proveedores.nombre')->get();
                $excel = array();
                foreach ($impresor as $im) {
                    $impreso = [
                        'impreso' => $im,
                        'filas' => []
                    ];
                    foreach ($tira as $t) {

                        if ($t->proveedor == $im->proveedor) {
                            $cliente = Cliente::select('clientes.razonsocial as cliente_nom')->where('clientes.id', $t->idcliente)->get();
                            // return $cliente;
                            //$datospapel=Costo::join('costois','costos.costois_id','=','costois.id')->where('costos.ordentrabajo_id',$t->idorden)->where('costos.titulo','Papel')->get();
                            if ($t->pago == false) {
                                $pago = 0;
                            } else {
                                $pago = 1;
                            }
                            if ($t->completado == false) {
                                $completado = 0;
                            } else {
                                $completado = 1;
                            }
                            if ($t->terminado == false) {
                                $terminado = 0;
                            } else {
                                $terminado = 1;
                            }
                            array_push($impreso['filas'], [
                                'fecha' => $t->fecha,
                                'cantidad' => $t->cantidad * 1000,
                                'referencia' => $cliente[0]->cliente_nom . ' ' . $t->item,
                                'material' => 'N/A',
                                'maquina' => $t->insumo,
                                'medida' => 'N/A',
                                'prioridad' => $t->prioridad,
                                'color' => '',
                                'pago' => $pago,
                                'plancha' => '',
                                'idCosto' => $t->idCosto,
                                'completado' => $completado,
                                'terminado' => $terminado,
                                'valor' => $t->totalCosto
                            ]);
                        }

                    }
                    array_push($excel, $impreso);

                }

                return $excel;
                break;
            //return ['resultado'=>$resultado];
        }
    }
    public function listaReportesOp(Request $request)
    {
        $reportes = Reportes::select('*')->where('tipo', 'op')->orderBy('created_at', 'DESC')->get();
        return $reportes;
    }
    public function listaReportesPapel(Request $request)
    {
        $reportes = Reportes::select('*')->where('tipo', 'Papel')->orderBy('created_at', 'DESC')->get();
        return $reportes;
    }
    public function listaReportesTirajes(Request $request)
    {
        $reportes = Reportes::select('*')->where('tipo', 'Tiraje')->orderBy('created_at', 'DESC')->get();
        return $reportes;
    }
    public function listaReportesTroquelados(Request $request)
    {
        $reportes = Reportes::select('*')->where('tipo', 'Troquelado')->orderBy('created_at', 'DESC')->get();
        return $reportes;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response([]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        try {
            DB::beginTransaction();
            $mytime = Carbon::now('America/Bogota');
            $orden = new Ordentrabajo();
            if ($orden->total == 0) {
                $orden->pago = 1;
            } else {
                $orden->pago = 0;
            }
            $orden->prioridad = $request->prioridad;
            $orden->plancha = $request->plancha;
            $orden->impresa = 0;
            $orden->cliente_id = $request->cliente_id;
            $orden->articulo_id = $request->articulo_id;
            $date = date('Y-m-d');
            if ($request->fecha_entrega == '') {
                $mod_date = strtotime($date . "12 days");
                $fecha_entrega = date('Y-m-d', $mod_date);
                $orden->fecha_entrega = $fecha_entrega;

            } else {

                $orden->fecha_entrega = $request->fecha_entrega;
            }
            $orden->carpeta_cliente = $request->carpeta_cliente;
            $orden->detalles_diseno = $request->detalles_diseno;
            $orden->fecha = date('Y-m-d');
            $orden->created_at = $request->fecha_orden;
            $orden->observaciones = $request->observaciones;
            $orden->cabida = $request->unidad;
            $orden->medida_final = $request->medida_final;
            $orden->tamano = $request->tamano;
            $orden->medida_material = $request->medida_material;
            $orden->cantidad = $request->cantidad;
            $orden->valor_unitario = $request->valor_unitario;
            $orden->descuento = $request->descuento;
            $orden->impuesto = $request->impuesto;
            $orden->valor_impuesto = $request->valor_impuesto;
            $orden->totalParcial = $request->totalParcial;
            $orden->total = $request->total;
            $orden->abono = $request->abono;
            $orden->saldo = $orden->total - $orden->abono;
            $orden->estado = 'OSP';
            $orden->produccion = $request->produccion;
            $orden->save();


            $detalles = json_decode($request->detalles);
            $costos = json_decode($request->costos);
            // echo $costos;
            $detcosto = StatusProduccionController::crearCostosDetalles($detalles, $costos, $orden->id, '', 1);
            $status = new statusProduccion();
            $procesos = [
                ['proceso' => 'Espera', 'cantidad' => 0, 'fechaTermina' => date('Y-m-d'), 'hora' => date('H:i:s')],
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
            $status->estado = $this->PRIMER_ESTADO;
            $status->idorden = $orden->id;
            $status->observaciones = '';
            $status->prioridad = 0;
            $status->fecha_termina = date('Y-m-d');
            $status->hora = date('H:i:s');
            $status->save();

            $datosA = new stdClass();
            $datosA->user_id = $request->user_id;
            $datosA->actividad = 'Orden creada #' . $orden->id;
            $actividad = new ActividadController();
            $actividad->store($datosA);

            DB::commit();
            return response()->json(['status' => 'success', 'id' => $orden->id]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

        try {
            $mytime = Carbon::now('America/Bogota');
            $orden = Ordentrabajo::find($request->id);
            if ($orden->total == 0) {
                $orden->pago = 1;
            } else {
                $orden->pago = 0;
            }
            $orden->prioridad = $request->prioridad;
            $orden->plancha = $request->plancha;
            $orden->impresa = $request->impresa;
            $orden->cliente_id = $request->id_cliente;
            $orden->articulo_id = $request->id_articulo;
            $date = date('Y-m-d');
            if ($orden->produccion == 'A') {
                if ($request->produccion == 'EP' || $request->produccion == 'ENP') {
                    $mod_date = strtotime($date . "12 days");
                    $fecha_entrega = date('Y-m-d', $mod_date);
                    $orden->fecha_entrega = $fecha_entrega;
                }
            } elseif ($orden->produccion == 'D') {
                if ($request->produccion == 'EP' || $request->produccion == 'ENP') {
                    $mod_date = strtotime($date . "12 days");
                    $fecha_entrega = date('Y-m-d', $mod_date);
                    $orden->fecha_entrega = $fecha_entrega;
                }
            } else {
                if ($request->fecha_entrega == '') {
                    $mod_date = strtotime($date . "12 days");
                    $fecha_entrega = date('Y-m-d', $mod_date);
                    $orden->fecha_entrega = $fecha_entrega;
                } else {
                }
            }
            $orden->carpeta_cliente = $request->carpeta_cliente;
            $orden->detalles_diseno = $request->detalles_diseno;
            $orden->fecha = $request->fecha;
            $orden->created_at = $request->fecha_orden;
            $orden->observaciones = $request->observaciones;
            $orden->cabida = $request->unidad;
            $orden->medida_final = $request->medida_final;
            $orden->tamano = $request->tamano;
            $orden->medida_material = $request->medida_material;
            $orden->cantidad = $request->cantidad;
            $orden->valor_unitario = $request->valor_unitario;
            $orden->descuento = $request->descuento;
            $orden->impuesto = $request->impuesto;
            $orden->valor_impuesto = $request->valor_impuesto;
            $orden->totalParcial = $request->totalParcial;
            $orden->total = $request->total;
            $orden->abono = $request->abono;
            $orden->saldo = $request->saldo;
            $orden->estado = $request->estado;
            $orden->produccion = $request->estadop;
            $orden->save();

            $detalles = json_decode($request->detalles);
            $costos = json_decode($request->costos);
            if (count($detalles) > 0) {
                $detcosto = StatusProduccionController::crearCostosDetalles($detalles, $costos, $orden->id, '', $orden->user_id);
            }
            $linea = LineaComprobante::where('ordentrabajo_id', $orden->id)->get()[0];
            $linea->articulo_id = $orden->articulo_id;
            $linea->save();
            return $detcosto;

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
    public function cambiarFecha(Request $request)
    {
        $orden = Ordentrabajo::find($request->id);
        $orden->fecha_entrega = $request->fecha_entrega;
        $orden->save();
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Cambio fecha de entrega a ' . $request->fecha_entrega . ' orden #' . $orden->id;
        $actividad = new ActividadController();
        $actividad->store($datosA);

    }
    public function cambiarEstado(Request $request)
    {
        $orden = Ordentrabajo::find($request->id);
        $date = date('Y-m-d');
        if ($orden->produccion == 'A') {
            if ($request->produccion == 'EP' || $request->produccion == 'ENP') {
                $mod_date = strtotime($date . "12 days");
                $fecha_entrega = date('Y-m-d', $mod_date);
                $orden->fecha_entrega = $fecha_entrega;
            }
        } elseif ($orden->produccion == 'D') {
            if ($request->produccion == 'EP' || $request->produccion == 'ENP') {
                $mod_date = strtotime($date . "12 days");
                $fecha_entrega = date('Y-m-d', $mod_date);
                $orden->fecha_entrega = $fecha_entrega;
            }
        }
        $orden->estado = $request->estado;
        $orden->produccion = $request->produccion;
        $orden->save();
        $status = statusProduccion::where('idorden', $orden->id)->get();
        if ($orden->produccion == 'EM') {
            $status[0]->estado = $this->ESTADO_CAMBIO;
        } elseif ($orden->produccion == 'E') {
            $status[0]->estado = $this->ULTIMO_ESTADO;
        } elseif ($status[0]->estado > $this->PRIMER_ESTADO) {

        } else {
            $status[0]->estado = $this->PRIMER_ESTADO;
        }
        $status[0]->save();
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Se cambio a estado de produccion a ' . $request->produccion . ' y estado comercial a ' . $request->estado . ' orden #' . $orden->id;
        $actividad = new ActividadController();
        $actividad->store($datosA);
    }
    public function cambiarImpresa(Request $request)
    {
        $orden = Ordentrabajo::find($request->id);
        $orden->impresa = $request->impresa;
        $orden->save();

        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Cambio impresa a ' . $request->impresa;
        $actividad = new ActividadController();
        $actividad->store($datosA);

    }
    public function cambiarAbono(Request $request)
    {
        $pedido = Comprobante::find($request->id);
        $pedido->abono = $request->abono;
        $pedido->saldo = $request->saldo;
        if ($pedido->estado != 4) {
            if ($pedido->saldo == 0 && $pedido->estado == 3) {
                $pedido->estado = 4;
            } elseif ($pedido->saldo > 0) {
                $pedido->estado = 3;
            } else {
                $pedido->estado = 2;
            }
        }
        $pedido->save();

        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Cambio abono a $' . $request->abono . ' saldo quedo $' . $request->saldo . 'Pedido #' . $pedido->id;
        $actividad = new ActividadController();
        $actividad->store($datosA);

    }
    public function cambiarProceso(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

        try {
            $id = $request->id;
            $columna = $request->columna;
            $dato = $request->dato;
            $costo = CostoProduccion::findOrFail($id);
            switch ($columna) {
                case 'completado':
                    $costo->completado = $dato;
                    break;
                case 'fechaterminado':
                    $costo->fecha_termina = $dato;
                    break;
                case 'terminado':
                    $costo->terminado = $dato;
                    $costo->completado = 1;
                    break;
            }
            $costo->save();

        } catch (Exception $e) {
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ordentrabajo  $ordentrabajo
     * @return \Illuminate\Http\Response
     */
    public function duplicar(Request $request)
    {
        $id = $request->id;
        $orden = Ordentrabajo::find($id);
        $orden->estado = 'OSP';
        $orden->produccion = 'A';
        $orden->impresa = 0;
        $orden->fecha = date('Y-m-d');
        $date = date('Y-m-d');
        $mod_date = strtotime($date . "12 days");
        $fecha_entrega = date('Y-m-d', $mod_date);
        $orden->fecha_entrega = $fecha_entrega;
        $orden->created_at = date('Y-m-d');
        $newOrden = $orden->replicate();
        $newOrden->created_at = Carbon::now();
        $newOrden->save();
        $detalles = Detalletrabajo::where('ordentrabajo_id', '=', $orden->id)->get();
        $costos = Costoproduccion::where('ordentrabajo_id', '=', $orden->id)->get();
        foreach ($detalles as $det) {
            $detalle = new Detalletrabajo();
            $detalle->titulo = $det->titulo;
            $detalle->valor = $det->valor;
            $detalle->descripcion = $det->descripcion;
            $detalle->ordentrabajo_id = $newOrden->id;
            $detalle->save();
        }
        foreach ($costos as $cos) {
            $costo = new CostoProduccion();
            $costo->costois_id = $cos->costois_id;
            $costo->titulo = $cos->titulo;
            $costo->descripcion = $cos->descripcion;
            $costo->cantidad = $cos->cantidad;
            $costo->orden = $cos->orden;
            $costo->completado = 0;
            $costo->pago = 0;
            $costo->fecha_termina = date('Y-m-d');
            $costo->terminado = 0;
            $costo->valor = $cos->valor;
            $costo->total = $cos->total;
            $costo->ordentrabajo_id = $newOrden->id;
            $costo->save();
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
        $status->estado = $this->PRIMER_ESTADO;
        $status->idorden = $newOrden->id;
        $status->observaciones = '';
        $status->prioridad = 0;
        $status->fecha_termina = date('Y-m-d');
        $status->hora = date('H:i:s');
        $status->save();

        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Duplico la orden #' . $id . ' y genero la orden #' . $newOrden->id;
        $actividad = new ActividadController();
        $actividad->store($datosA);
        return response($newOrden);
    }
    public function generarOrden(Request $request)
    {
        // $data = [
        //     'title' => 'Welcome to ItSolutionStuff.com',
        //     'date' => date('m/d/Y')
        // ];

        // $pdf = PDF::loadHTML('<h1>Test</h1>');

        // return $pdf->download('itsolutionstuff.pdf');
        // return $request->guia;
        $orden = json_decode($request->orden);

        $path = public_path() . '/reportes/orden.pdf';

        $pdf = PDF::loadView('pdf.orden', compact('orden'));
        $pdf->setPaper('A4', 'portrait');

        $pdf->save($path);
        $datosA = new stdClass();
        $datosA->user_id = $orden->user_id;
        $datosA->actividad = 'Se genero orden #' . $orden->id;
        $actividad = new ActividadController();
        $actividad->store($datosA);

        return $pdf->stream();
        // return response()->download($path);
        // $pdf = App::make('dompdf.wrapper');
        // $pdf->loadHTML('<h1>Test</h1>');
        // return $pdf->stream();
    }
    public function show(Ordentrabajo $ordentrabajo)
    {
        return response($ordentrabajo);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ordentrabajo  $ordentrabajo
     * @return \Illuminate\Http\Response
     */
    public function edit(Ordentrabajo $ordentrabajo)
    {
        return response($ordentrabajo);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ordentrabajo  $ordentrabajo
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        $linea = LineaComprobante::select()->where('ordentrabajo_id', $id)->get();
        if (count($linea) > 0) {

            $idpedido = $linea[0]->comprobante_id;
            $valorLinea = $linea[0]->valor_total;
            $lineas = LineaComprobante::where('comprobante_id', $idpedido)->get();
            $pedido = Comprobante::find($idpedido);
            $n = count($lineas);
            if ($n <= 1) {
                $pedido->delete();
            } else {
                $subtotal = $pedido->subtotal - $valorLinea;
                $pedido->subtotal = $subtotal;
                $pedido->impuestos = $subtotal * $pedido->iva;
                $pedido->total = $subtotal + $pedido->impuestos;
                $pedido->saldo = $pedido->total - $pedido->abono;
                $pedido->save();
                $linea[0]->delete();
            }
        }
        $orden = Ordentrabajo::find($id);
        $orden->delete();
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;

        $datosA->actividad = 'Se elimino orden #' . $id . ',cliente ' . $request->cliente . ', producto ' . $request->producto;
        $actividad = new ActividadController();
        $actividad->store($datosA);
        return response()->json(['status' => 'success']);
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        $costo = CostoProduccion::find($id);
        $costo->costois;
        $costo->delete();
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Se elimino costo de produccion ' . $costo->titulo . ' Insumo ' . $costo->costois->nombre . ' de la orden #' . $costo->ordentrabajo_id;
        $actividad = new ActividadController();
        $actividad->store($datosA);
    }

    public function convertirAbonoEnRecibo(Request $request)
    {
        try {
            DB::beginTransaction();
            $pedido = Comprobante::findOrFail($request->id);

            $monto = (float) $request->abono;
            if ($monto <= 0) {
                return response()->json(['success' => false, 'error' => 'No hay un abono válido para convertir.']);
            }

            // Un abono migrado a recibo tendrá ya sea un cruce de cartera o un recibo pago directo ligado a este pedido_id
            $totalDirectos = \App\ReciboPago::where('pedido_id', $pedido->id)->sum('monto');
            $totalCruzados = \App\CruceCartera::where('comprobante_id', $pedido->id)->sum('monto');
            // Como las conversiones crean ambos a la vez, el saldo real en recibos puede verse en either directos o cruzados
            // Para estar seguros sumamos cruzados (que es adonde apuntaba el sistema antes),
            // pero si hay más recibos directos los sumamos de igual forma
            $totalRecibosRegistrados = max($totalDirectos, $totalCruzados);

            if ($totalRecibosRegistrados >= $monto) {
                return response()->json(['success' => false, 'error' => 'Ya existe un recibo de pago o cruce por el valor total de este abono. No se puede migrar dos veces.']);
            }

            // Recalculamos el remanente si es que sólo se había migrado una parte
            $monto = $monto - (float) $totalRecibosRegistrados;

            $userId = \Auth::id() ?: 1;

            $pago = new ReciboPago();
            $pago->cliente_id = $pedido->cliente_id;
            $pago->user_id = $userId;
            $pago->fecha = date('Y-m-d');
            $pago->monto = $monto;
            $pago->forma_pago = 'Efectivo';
            $pago->observaciones = 'Abono historico migrado a Recibo de caja';
            $pago->pedido_id = $pedido->id;

            $ultimoRecibo = ReciboPago::orderBy('id', 'desc')->first();
            $pago->num_recibo = $ultimoRecibo ? $ultimoRecibo->num_recibo + 1 : 1;
            $pago->saldo_recibo = 0; // Se aplica completo
            $pago->save();

            \App\CruceCartera::create([
                'recibo_pago_id' => $pago->id,
                'comprobante_id' => $pedido->id,
                'monto' => $monto,
                'fecha_cruce' => date('Y-m-d'),
            ]);

            // Ahora sumamos el abono recalcadamente para que la BD quede sincrona
            $this->verificarYActualizarPedido($pedido->id);

            // Registrar actividad
            $datosA = new \stdClass();
            $datosA->user_id = $userId;
            $datosA->actividad = 'Abono historico $' . number_format($monto, 0, ',', '.') . ' migrado a Recibo #' . $pago->num_recibo;
            (new ActividadController())->store($datosA);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function registrarPago(Request $request)
    {
        try {
            DB::beginTransaction();

            $montoTotal = (float) $request->monto;
            if ($montoTotal <= 0) {
                return response()->json(['success' => false, 'error' => 'El monto debe ser mayor a cero.'], 422);
            }

            $comprobante = Comprobante::findOrFail($request->comprobante_id);
            $clienteId = $comprobante->cliente_id;
            $userId = \Auth::id() ?: 1;

            // 1. Crear el recibo de pago global
            $pago = new ReciboPago();
            $pago->cliente_id = $clienteId;
            $pago->user_id = $userId;
            $pago->fecha = $request->fecha;
            $pago->monto = $montoTotal;
            $pago->forma_pago = $request->forma_pago;
            $pago->observaciones = $request->observaciones;
            $pago->pedido_id = $comprobante->getPedidoPadreId();

            $ultimoRecibo = ReciboPago::orderBy('id', 'desc')->first();
            $pago->num_recibo = $ultimoRecibo ? $ultimoRecibo->num_recibo + 1 : 1;
            $pago->save();

            $restante = $montoTotal;

            // 2. PRIORIDAD: Aplicar al documento seleccionado
            if ($comprobante->saldo > 0) {
                $aplicar = min($restante, (float) $comprobante->saldo);
                $this->aplicarPagoADocumento($pago, $comprobante, $aplicar, $request->fecha);
                $restante -= $aplicar;
            }

            // 3. WATERFALL: Si sobra, aplicar a otros documentos del cliente (FIFO)
            if ($restante > 0) {
                // Buscamos cualquier documento con saldo (CC, Factura, Pedido, Remisión)
                $otrosDocs = Comprobante::where('cliente_id', $clienteId)
                    ->where('saldo', '>', 0)
                    ->where('id', '!=', $comprobante->id)
                    ->orderBy('fecha', 'asc')
                    ->get();

                foreach ($otrosDocs as $doc) {
                    if ($restante <= 0)
                        break;
                    $aplicar = min($restante, (float) $doc->saldo);
                    $this->aplicarPagoADocumento($pago, $doc, $aplicar, $request->fecha);
                    $restante -= $aplicar;
                }
            }

            // 4. SOBRANTE: Queda como anticipo (saldo_recibo)
            $pago->saldo_recibo = $restante;
            $pago->save();

            // Registrar actividad
            $datosA = new \stdClass();
            $datosA->user_id = $userId;
            $datosA->actividad = 'Pago inteligente $' . number_format($montoTotal, 0, ',', '.') . ' registrado. Recibo #' . $pago->num_recibo;
            (new ActividadController())->store($datosA);

            DB::commit();
            return response()->json(['success' => true, 'recibo_id' => $pago->id, 'sobrante' => $restante]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Aplica una porción de un recibo a un documento específico (Interno)
     */
    private function aplicarPagoADocumento($pago, $doc, $monto, $fecha)
    {
        if ($monto <= 0)
            return;

        // Buscar relación Pedido-Remisión-CC para el cruce
        $pedidoId = $doc->getPedidoPadreId();
        $link = \App\PedidoRemision::where('pedido_id', $pedidoId)
            ->where(function ($q) use ($doc) {
                $q->where('cuentacobro_id', $doc->id)->orWhere('remision_id', $doc->id);
            })->first();

        \App\CruceCartera::create([
            'recibo_pago_id' => $pago->id,
            'comprobante_id' => $doc->id,
            'pedido_remision_id' => $link ? $link->id : null,
            'monto' => $monto,
            'fecha_cruce' => $fecha,
        ]);

        // Actualizar saldos del documento
        $doc->abono = ($doc->abono ?? 0) + $monto;
        $doc->saldo = max(0, (float) $doc->total - (float) $doc->abono);
        if ($doc->saldo <= 0) {
            $doc->saldo = 0;
            if ($doc->tipo !== 'pedido') {
                $doc->estado = 3; // 3: Pagada para otros tipos
            }
        }
        $doc->save();

        // Sincronizar con el pedido padre
        $this->propagarPagoAPedido($doc, $monto);
    }

    public function eliminarRecibo(Request $request)
    {
        try {
            DB::beginTransaction();
            $recibo = ReciboPago::findOrFail($request->id);

            // Revertir cruces
            $cruces = \App\CruceCartera::where('recibo_pago_id', $recibo->id)->get();
            $comprobantesAfectados = [];

            foreach ($cruces as $cruce) {
                $comp = Comprobante::find($cruce->comprobante_id);
                if ($comp) {
                    $montoCruce = (float) $cruce->monto;
                    $comp->abono = max(0, (float) ($comp->abono ?? 0) - $montoCruce);
                    $comp->saldo = (float) ($comp->saldo ?? 0) + $montoCruce;

                    $comp->save();
                    $comprobantesAfectados[] = $comp;
                }
            }

            // Eliminar los cruces
            \App\CruceCartera::where('recibo_pago_id', $recibo->id)->delete();

            // CASO A: El recibo estaba cruzado a documentos (CC, Factura, etc.)
            foreach ($comprobantesAfectados as $comp) {
                $this->propagarPagoAPedido($comp, 0);
            }

            // CASO B: El recibo estaba vinculado directamente a un pedido (Prepago/Abono directo)
            if ($recibo->comprobante_id) {
                $compDirecto = Comprobante::find($recibo->comprobante_id);
                if ($compDirecto) {
                    $this->propagarPagoAPedido($compDirecto, 0);
                }
            }

            // CASO NUEVO: Recalcular Pedido Asignado Directamente
            $pedidoId = $recibo->pedido_id;

            $recibo->delete();

            if ($pedidoId) {
                $this->verificarYActualizarPedido($pedidoId);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function actualizarRecibo(Request $request)
    {
        try {
            $recibo = ReciboPago::findOrFail($request->id);

            // Si intenta cambiar el monto y ya tiene cruces, por ahora lo bloqueamos por seguridad
            // o podrías implementar la lógica de re-calculo aquí.
            $tiene_cruces = \App\CruceCartera::where('recibo_pago_id', $recibo->id)->exists();
            if ($tiene_cruces && (float) $request->monto != (float) $recibo->monto) {
                return response()->json(['error' => 'No se puede cambiar el monto de un recibo que ya tiene cruces aplicados. Elimínelo y cree uno nuevo.'], 422);
            }

            $recibo->fecha = $request->fecha;
            $recibo->forma_pago = $request->forma_pago;
            $recibo->num_recibo = $request->num_recibo;
            $recibo->observaciones = $request->observaciones;
            if (!$tiene_cruces) {
                $recibo->monto = $request->monto;
                $recibo->saldo_recibo = $request->monto;
            }
            $recibo->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Propaga el abono de una CC hacia su pedido padre,
     * recalculando el saldo del pedido como total - sum(pagos en sus CCs).
     */
    public function propagarPagoAPedido(Comprobante $cc, float $montoExtra = 0)
    {
        $involvedPedidoIds = $cc->getPedidoPadreIds();

        foreach ($involvedPedidoIds as $pid) {
            $pedido = Comprobante::find($pid);
            if (!$pedido)
                continue;

            $this->verificarYActualizarPedido($pedido);
        }
    }

    public function verificarYActualizarPedido($pedido)
    {
        if (!$pedido)
            return;
        if (is_numeric($pedido))
            $pedido = Comprobante::find($pedido);

        $recibosDirectos = \App\ReciboPago::where('pedido_id', $pedido->id)->get();
        $reciboIdsCruce = \App\CruceCartera::where('comprobante_id', $pedido->id)->pluck('recibo_pago_id')->unique()->toArray();
        $recibosPorCruce = \App\ReciboPago::whereIn('id', $reciboIdsCruce)->get();

        $todosRecibos = $recibosDirectos->merge($recibosPorCruce)->unique('id');
        $abonoTotal = $todosRecibos->sum(function ($r) {
            return (float) $r->monto;
        });

        $pedido->abono = round($abonoTotal, 2);
        $pedido->saldo = max(0, round($pedido->total - $pedido->abono, 2));

        if ($pedido->estado < 5) {
            // REGLA DE REVERSIÓN: Si está listo (4) pero ahora debe dinero (> 0), vuelve a Completado (3)
            if ($pedido->estado == 4 && $pedido->saldo > 0) {
                $pedido->estado = 3;
            }

            $todasLasLineasListas = true;
            foreach ($pedido->lineas as $linea) {
                if ($linea->orden) {
                    $cumple = false;
                    $status = \App\statusProduccion::where('idorden', $linea->orden->id)->first();

                    // Cumple si el físicamente ya se entregó o si la producción está lista ("Para entregar")
                    if ($status && $status->estado == 'Para entregar') {
                        $cumple = true;
                    } elseif ($linea->orden->produccion == 'E' || $linea->orden->produccion == 'T') {
                        $cumple = true;
                    }

                    if (!$cumple) {
                        $todasLasLineasListas = false;
                        break;
                    }
                }
            }

            if ($todasLasLineasListas) {
                if ($pedido->saldo <= 0) {
                    $pedido->estado = 4; // Para entregar (Pagado y Listo)
                } else {
                    $pedido->estado = 3; // Completado (Con deuda y Listo)
                }

                // REQUERIMIENTO: Las órdenes relacionadas deben quedar en 'Para entregar'
                foreach ($pedido->lineas as $linea) {
                    if ($linea->orden) {
                        // Actualizar el campo producción en la tabla ordentrabajos
                        $linea->orden->produccion = 'E';
                        $linea->orden->save();

                        // Actualizar el estado en la tabla status_produccion
                        $statusOrd = \App\statusProduccion::where('idorden', $linea->orden->id)->first();
                        if ($statusOrd && $statusOrd->estado != 'Para entregar') {
                            $statusOrd->estado = 'Para entregar';
                            $statusOrd->save();
                        }
                    }
                }
            }
        }

        $pedido->save();
    }

    public function cruzarCarteraCliente(Request $request)
    {
        $clienteId = $request->cliente_id;
        if (!$clienteId)
            return response()->json(['error' => 'Cliente no especificado'], 400);

        try {
            DB::beginTransaction();

            // 1. Obtener deudas (CC/Factura) con saldo > 0
            $deudas = Comprobante::where('cliente_id', $clienteId)
                ->whereIn('tipo', ['cuentacobro', 'factura'])
                ->where('saldo', '>', 0)
                ->orderBy('fecha', 'asc')
                ->get();

            // 2. Obtener créditos (ReciboPago) con saldo_recibo > 0
            $creditos = \App\ReciboPago::where('cliente_id', $clienteId)
                ->where('saldo_recibo', '>', 0)
                ->orderBy('fecha', 'asc')
                ->get();

            $crucesRealizados = 0;

            foreach ($deudas as $deuda) {
                foreach ($creditos as $credito) {
                    if ($deuda->saldo <= 0)
                        break;
                    if ($credito->saldo_recibo <= 0)
                        continue;

                    $montoACruzar = min($deuda->saldo, $credito->saldo_recibo);

                    if ($montoACruzar > 0) {
                        \App\CruceCartera::create([
                            'recibo_pago_id' => $credito->id,
                            'comprobante_id' => $deuda->id,
                            'monto' => $montoACruzar,
                            'fecha_cruce' => now()->toDateString()
                        ]);

                        $deuda->abono = ($deuda->abono ?? 0) + $montoACruzar;
                        $deuda->saldo = max(0, (float) $deuda->total - (float) $deuda->abono);
                        $deuda->save();

                        $credito->saldo_recibo = max(0, (float) $credito->saldo_recibo - $montoACruzar);
                        $credito->save();

                        // Propagar hacia el pedido padre 
                        $this->propagarPagoAPedido($deuda, $montoACruzar);

                        $crucesRealizados++;
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'cruces' => $crucesRealizados]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function registrarPagoMasivo(Request $request)
    {
        try {
            DB::beginTransaction();
            $montoTotal = (float) $request->monto;
            $clienteId = $request->cliente_id;
            $userId = \Auth::id() ?: 1;

            // 1. Crear UN solo recibo de pago para el cliente
            $pago = new ReciboPago();
            $pago->comprobante_id = null;
            $pago->cliente_id = $clienteId;
            $pago->user_id = $userId;
            $pago->fecha = $request->fecha;
            $pago->monto = $montoTotal;
            $pago->saldo_recibo = $montoTotal;
            $pago->forma_pago = $request->forma_pago;
            $pago->observaciones = ($request->observaciones ?? '') . ' (Cruce masivo de cartera)';
            $ultimoRecibo = ReciboPago::orderBy('id', 'desc')->first();
            $pago->num_recibo = $ultimoRecibo ? $ultimoRecibo->num_recibo + 1 : 1;
            $pago->save();

            // 2. Buscar documentos con saldo > 0 ordenadas por fecha (FIFO)
            $documentosPendientes = Comprobante::where('cliente_id', $clienteId)
                ->where('saldo', '>', 0)
                ->orderBy('fecha', 'asc')
                ->get();

            $restante = $montoTotal;
            foreach ($documentosPendientes as $doc) {
                if ($restante <= 0)
                    break;

                $montoAAplicar = min($restante, (float) $doc->saldo);

                // Crear el cruce de cartera
                $link = \App\PedidoRemision::where('cuentacobro_id', $doc->id)
                    ->orWhere('remision_id', $doc->id)
                    ->orWhere('pedido_id', $doc->id)
                    ->first();

                \App\CruceCartera::create([
                    'recibo_pago_id' => $pago->id,
                    'comprobante_id' => $doc->id,
                    'pedido_remision_id' => $link ? $link->id : null,
                    'monto' => $montoAAplicar,
                    'fecha_cruce' => $request->fecha,
                ]);

                // Actualizar saldo y abono del comprobante
                $doc->abono = ($doc->abono ?? 0) + $montoAAplicar;
                $doc->saldo = max(0, (float) $doc->total - (float) $doc->abono);
                if ($doc->saldo <= 0) {
                    $doc->saldo = 0;
                    if ($doc->tipo !== 'pedido') {
                        $doc->estado = 3;
                    }
                }
                $doc->save();

                // Propagar al pedido padre (si no es el mismo pedido)
                $this->propagarPagoAPedido($doc, $montoAAplicar);

                $restante -= $montoAAplicar;
            }

            // 3. Si sobra saldo, guardarlo en el recibo
            $pago->saldo_recibo = $restante;
            $pago->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'recibo_id' => $pago->id,
                'sobrante' => $restante
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function registrarCruce(Request $request)
    {
        // Método para cruzar un abono existente (saldo_recibo > 0) con una factura
        try {
            DB::beginTransaction();
            $recibo = ReciboPago::findOrFail($request->recibo_id);
            $comprobante = Comprobante::findOrFail($request->comprobante_id);
            $monto = $request->monto;

            if ($monto > $recibo->saldo_recibo || $monto > $comprobante->saldo) {
                return response()->json(['error' => 'El monto excede el saldo del recibo o de la factura'], 400);
            }

            $link = \App\PedidoRemision::where('pedido_id', $recibo->pedido_id)
                ->where('cuentacobro_id', $comprobante->id)
                ->first();

            \App\CruceCartera::create([
                'recibo_pago_id' => $recibo->id,
                'comprobante_id' => $comprobante->id,
                'pedido_remision_id' => $link ? $link->id : null,
                'monto' => $monto,
                'fecha_cruce' => now()->toDateString()
            ]);

            $comprobante->abono += $monto;
            $comprobante->monto_aplicado_anticipo += $monto; // Aplicando saldo a favor
            $comprobante->saldo -= $monto;
            if ($comprobante->saldo <= 0) {
                $comprobante->saldo = 0;
                if ($comprobante->tipo !== 'pedido') {
                    $comprobante->estado = 3;
                }
            }
            $comprobante->save();

            $recibo->saldo_recibo -= $monto;
            $recibo->save();

            // Propagar el pago al pedido padre
            $this->propagarPagoAPedido($comprobante, $monto);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function comprobantesClientePendientes(Request $request)
    {
        $clienteId = $request->cliente_id;
        $comprobantes = Comprobante::where('cliente_id', $clienteId)
            ->whereIn('tipo', ['cuentacobro', 'factura', 'pedido'])
            ->where('saldo', '>', 0)
            ->orderBy('fecha', 'asc')
            ->get(['id', 'num_comprobante', 'fecha', 'total', 'abono', 'saldo', 'tipo']);
        return response()->json($comprobantes);
    }

    public function listarPagos($id)
    {
        // Buscar recibos que tengan un cruce con este comprobante
        $pagoIds = \App\CruceCartera::where('comprobante_id', $id)->pluck('recibo_pago_id');
        $pagos = ReciboPago::whereIn('id', $pagoIds)
            ->with(['usuario'])
            ->orderBy('fecha', 'desc')
            ->get();
        return $pagos;
    }

    public function reciboPdf($id)
    {
        $pago = ReciboPago::with(['comprobante', 'cliente', 'usuario'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.recibo_pago', compact('pago'));
        $pdf->setPaper([0, 0, 612, 396], 'portrait');

        return $pdf->stream('recibo_pago_' . $pago->num_recibo . '.pdf');
    }
    public function asignarCosto()
    {
        $detalles = Detalletrabajo::where('titulo', 'papel')->get();
        foreach ($detalles as $det) {
            $costo = CostoProduccion::where('ordentrabajo_id', $det->ordentrabajo_id)->where('titulo', 'Papel')->get();
            // return $costo;
            if (count($costo) != 0) {
                $det->costos_id = $costo[0]->id;
                $det->save();
                if (count($costo) > 1) {
                    $costo[1]->delete();
                }
            }
        }


    }

    public function reasignarPedido(Request $request)
    {
        $orden_id = $request->orden_id;
        $pedido_id = $request->pedido_id;

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $orden = \App\Ordentrabajo::findOrFail($orden_id);
            $pedido = \App\Comprobante::findOrFail($pedido_id);

            // 1. Actualizar el cliente de la orden al cliente del nuevo pedido
            $orden->cliente_id = $pedido->cliente_id;
            // Para asegurar compatibilidad con el modelo que tiene idcliente en su fillable
            if (isset($orden->idcliente)) {
                $orden->idcliente = $pedido->cliente_id;
            }
            $orden->save();

            // 2. Buscar si la orden ya está en una linea de algún comprobante
            $linea = \App\LineaComprobante::where('ordentrabajo_id', $orden_id)->where('articulo_id', $orden->articulo_id)->first();

            if ($linea) {
                // Si ya pertenece a un pedido, lo movemos
                $linea->comprobante_id = $pedido_id;
                $linea->save();
            } else {
                // Si no tiene linea, creamos una nueva para este pedido
                $linea = new \App\LineaComprobante();
                $linea->comprobante_id = $pedido_id;
                $linea->ordentrabajo_id = $orden_id;
                $linea->articulo_id = $orden->articulo_id;
                $linea->cantidad = $orden->cantidad;
                $linea->valor_unitario = $orden->valor_unitario ?? 0;
                $linea->subtotal = $linea->cantidad * $linea->valor_unitario;
                $linea->valor_total = $linea->subtotal;
                $linea->save();
            }

            // Recalcular el total del nuevo pedido
            $pedido->subtotal = \App\LineaComprobante::where('comprobante_id', $pedido_id)->sum('subtotal');
            $pedido->total = $pedido->subtotal + ($pedido->impuestos ?? 0);
            $pedido->saldo = $pedido->total - ($pedido->abono ?? 0);
            $pedido->save();

            \Illuminate\Support\Facades\DB::commit();
            
            // Log de actividad si se desea, similar a otros controladores
            try{
               $datosA = new \stdClass();
               $datosA->user_id = $request->user_id ?? 1;
               $datosA->actividad = 'Reasigno orden #' . $orden_id .' al pedido #' . ($pedido->num_comprobante ?? $pedido_id);
               $actividad = new \App\Http\Controllers\ActividadController();
               $actividad->store($datosA);
            }catch(\Exception $eact){}

            return response()->json(['success' => true, 'message' => 'Orden reasignada al pedido #' . ($pedido->num_comprobante ?? $pedido_id)]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

