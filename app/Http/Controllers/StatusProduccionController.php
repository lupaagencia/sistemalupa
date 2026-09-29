<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Comprobante;
use App\Activo;
use App\LineaComprobante;
use App\Ordentrabajo;
use App\Procesos;
use App\statusProduccion;
use App\Detalletrabajo;
use App\CostoProduccion;
use App\FlujoProduccion;
use App\Costois;
use App\Actividad;
use App\InventariosMateriaPrima;
use App\Articulo;
use App\Http\Controllers\MovimientoMateriaPrimaController;
use stdClass;
use Illuminate\Http\Request;


class StatusProduccionController extends Controller
{
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
    public function index()
    {

        // $ordenes = Ordentrabajo::join('clientes','ordentrabajos.cliente_id','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        // ->join('articulos','ordentrabajos.articulo_id','=','articulos.id')->join('statusproduccion','ordentrabajos.id','=','statusproduccion.idorden')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','statusproduccion.estado as estadopro','statusproduccion.observaciones as obstatus')
        // ->whereIn('ordentrabajos.produccion',['ENP','EM','E'])->orderBy('ordentrabajos.id', 'DESC')->get();
        $ordenes = Ordentrabajo::whereIn('ordentrabajos.produccion', ['ENP', 'EM', 'E', 'EP'])
            ->where(function ($query) {
                $query->where('ordentrabajos.produccion', 'E')
                      ->orWhereDoesntHave('linea.pedido', function ($q) {
                          $q->whereIn('estado', ['0', '1', '6']);
                      });
            })
            ->get();

        $primerosPedidosMap = Comprobante::where('tipo', 'pedido')
            ->selectRaw('cliente_id, MIN(id) as primer_pedido_id')
            ->groupBy('cliente_id')
            ->pluck('primer_pedido_id', 'cliente_id')
            ->all();

        foreach ($ordenes as $orden) {
            self::sincronizarPapelesOrden($orden->id);
            $orden->detalles;
            $costos = CostoProduccion::leftJoin('costois', 'costos.costois_id', '=', 'costois.id')
                ->where('costos.ordentrabajo_id', '=', $orden->id)
                ->select('costos.*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')
                ->get();
            $orden->costos = $costos;
            $orden->producto;
            $orden->articulo;
            $orden->cliente;
            if ($orden->cliente) {
                $orden->planchas = InventariosMateriaPrima::where('asignado_id', $orden->cliente->id)->where('tipo', 'Plancha')->get();
                $orden->cliente->envios;
            }
            $orden->status;
            $orden->maquina;
            $orden->troquelado;
            $orden->terminado;
            foreach ($orden->terminado as $term) {
                $term->activo;
                $term->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $term->id)->first();
            }
            $costoTerminado = CostoProduccion::where('ordentrabajo_id', $orden->id)
                ->where('titulo', 'Terminado')
                ->first();
            $orden->cuenta_cobro = $costoTerminado ? \App\CuentaPorPagar::where('costo_id', $costoTerminado->id)->first() : null;
            if ($orden->linea) {
                $orden->pedido = Comprobante::find($orden->linea->comprobante_id);
            }

            $primerPedidoId = $primerosPedidosMap[$orden->cliente_id] ?? null;
            $orden->es_cliente_nuevo = ($orden->linea && $primerPedidoId && $orden->linea->comprobante_id == $primerPedidoId);

            foreach ($orden->detalles as $det) {
                if ($det->titulo == 'Tinta' || $det->titulo == 'tinta') {
                    $decoded = json_decode($det->valor);
                    if (is_array($decoded)) {
                        $det->valor = $decoded;
                    }
                }
            }

            // Material persistence integrity for details
            foreach ($orden->detalles as $detalle) {
                if (!is_null($detalle->costos_id) && $detalle->costos_id != 0) {
                    if (!empty($detalle->costo)) {
                        $detalle->costo->costois;
                    }
                }
            }

            if (count($orden->papel) != 0) {
                foreach ($orden->papel as $papel) {
                    $papel->costois;
                }
            }
        }

        return $ordenes;
    }

    public function imprimirStatus($estado)
    {
        $ordenes = Ordentrabajo::whereIn('ordentrabajos.produccion', ['ENP', 'EM', 'E', 'EP'])
            ->where(function ($query) {
                $query->where('ordentrabajos.produccion', 'E')
                      ->orWhereDoesntHave('linea.pedido', function ($q) {
                          $q->whereIn('estado', ['0', '1', '6']);
                      });
            })
            ->get();

        $ordenesFiltradas = [];

        foreach ($ordenes as $orden) {
            // Load status history
            $orden->status;
            $latestStatus = $orden->status->sortByDesc('id')->first();
            $ordenEstado = $latestStatus ? trim($latestStatus->estado) : 'Sin iniciar';

            if ($ordenEstado !== $estado) {
                // Fallback / mapping for variations in stage names (same as frontend / ProduccionSeguimientoController)
                $mappedEstado = $ordenEstado;
                if (stripos($ordenEstado, 'plancha') !== false) {
                    $mappedEstado = 'Planchas';
                } elseif (stripos($ordenEstado, 'corte') !== false) {
                    $mappedEstado = 'Corte material';
                } elseif (stripos($ordenEstado, 'impre') !== false) {
                    $mappedEstado = 'Impresión';
                } elseif (stripos($ordenEstado, 'plast') !== false) {
                    $mappedEstado = 'Plastificado';
                } elseif (stripos($ordenEstado, 'troque') !== false) {
                    $mappedEstado = 'Troquelado';
                } elseif (stripos($ordenEstado, 'espera') !== false) {
                    $mappedEstado = 'Espera Terminado';
                } elseif (stripos($ordenEstado, 'entrega') !== false) {
                    $mappedEstado = 'Para entregar';
                }

                if ($mappedEstado !== $estado) {
                    continue;
                }
            }

            $orden->detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')
                ->where('ordentrabajo_id', '=', $orden->id)
                ->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')
                ->get();
            $orden->costos = $costos;
            $orden->producto;
            $orden->articulo;
            $orden->cliente;

            if (count($orden->papel) != 0) {
                foreach ($orden->papel as $papel) {
                    $papel->costois;
                }
            }

            $pliegos = 0;
            $tamanos = 0;
            $sobrante = (int) $orden->carpeta_cliente;

            if ($orden->costos) {
                foreach ($orden->costos as $e) {
                    if (strcasecmp($e->titulo_costo ?? $e->titulo ?? '', 'Papel') === 0) {
                        $pliegos = $e->cantidad_costo ?? $e->cantidad ?? 0;
                        $descripcionVal = $e->descripcion_costo ?? $e->descripcion ?? 0;
                        $tamanos = (int)$descripcionVal - $sobrante;
                    }
                }
            }

            $orden->pliegos = $pliegos;
            $orden->tamanos = $tamanos;
            $orden->sobrante = $sobrante;

            $ordenesFiltradas[] = $orden;
        }

        // Sort matching screen order: VIP first, priority (ascending), then delivery date (descending), then ID (descending)
        usort($ordenesFiltradas, function ($a, $b) {
            $isVipA = (strtolower((string)$a->prioridad) === 'vip');
            $isVipB = (strtolower((string)$b->prioridad) === 'vip');

            if ($isVipA && !$isVipB) return -1;
            if (!$isVipA && $isVipB) return 1;

            $latestStatusA = $a->status ? $a->status->sortByDesc('id')->first() : null;
            $latestStatusB = $b->status ? $b->status->sortByDesc('id')->first() : null;

            $prioridadA = $latestStatusA ? (int)$latestStatusA->prioridad : 999;
            $prioridadB = $latestStatusB ? (int)$latestStatusB->prioridad : 999;

            if ($prioridadA !== $prioridadB) {
                return $prioridadA <=> $prioridadB;
            }

            $dateA = $a->fecha_entrega ? strtotime($a->fecha_entrega) : 0;
            $dateB = $b->fecha_entrega ? strtotime($b->fecha_entrega) : 0;

            if ($dateA !== $dateB) {
                return $dateB <=> $dateA;
            }

            return $b->id <=> $a->id;
        });

        $pdf = \PDF::loadView('pdf.status_report', [
            'estado' => $estado,
            'ordenes' => $ordenesFiltradas,
            'fecha' => date('Y-m-d h:i:s A')
        ]);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('reporte_produccion_' . str_replace(' ', '_', strtolower($estado)) . '.pdf');
    }

    public function procesos()
    {
        $procesos = Procesos::orderBy('posicion')->get();
        return $procesos;


    }
    public function estadosdestatus()
    {
        $status = Procesos::select('estado')->groupBy('estado')->orderBy('posicion')->get();
        return $status;
    }
    public function verificarEnvio(Request $request)
    {
        $orden = Ordentrabajo::join('clientes', 'ordentrabajos.cliente_id', '=', 'clientes.id')->join('datosenvio', 'clientes.id', '=', 'datosenvio.idcliente')
            ->select('ordentrabajos.id', 'clientes.id as idcliente')->where('ordentrabajos.id', '=', $request->id)->get();
        return count($orden);
    }
    public static function cambiarEstadoPedido($orden, $estado, $estadoActual)
    {
        $pedidos = Comprobante::where('cliente_id', $orden->cliente_id)->whereIn('estado', $estadoActual)->get();
        foreach ($pedidos as $pedido) {
            $completado = 0;
            $lineas = $pedido->lineas->where('comprobante_id', $pedido->id);
            foreach ($lineas as $linea) {
                if ($linea->orden->produccion == 'E') {
                    $completado++;
                }
                ;
            }
            if ($completado == count($lineas)) {
                $pedido->estado = $estado;
                $pedido->save();
            }
        }
    }
    public function crearStatus()
    {

        $ordenes = Ordentrabajo::orderBy('id', 'DESC')->get();
        foreach ($ordenes as $orden) {
            $status = new statusProduccion();
            $status->estado = $this->PRIMER_ESTADO;
            $status->idorden = $orden->id;
            $status->observaciones = '';
            $status->fecha_termina = date('Y-m-d');
            $status->hora = date('H:i:s');
            $status->save();
        }
    }
    public function flujo(Request $request)
    {
        $ordenes = json_decode($request->data);
        foreach ($ordenes as $orden) {
            $flujo = new FlujoProduccion();
            $flujo->fecha_inicia = date('Y-m-d');
            $flujo->hora_inicia = date('H:i:s');
            $flujo->orden_trabajo_id = $orden->status->idorden;
            $flujo->proceso = $orden->status->estado;
            $flujo->usuario = $request->activo;
            $flujo->save();
        }

    }
    public function cambiarEstado(Request $request)
    {
        // Find by status id or fallback to orden_id if not found/provided
        $status = null;
        if ($request->id) {
            $status = statusProduccion::find($request->id);
        }

        if (!$status && $request->orden_id) {
            $status = statusProduccion::where('idorden', $request->orden_id)->first();
        }

        if (!$status) {
            $status = new statusProduccion();
            $status->idorden = $request->orden_id;
            $status->prioridad = 1;
        }

        if ($request->tipo != '') {
            MovimientoMateriaPrimaController::registrarMovimiento($request);
        }

        $estadoAnterior = $status->estado;

        $status->estado = $request->estado;
        $status->save();

        $flujo = new FlujoProduccion();
        $flujo->fecha_inicia = date('Y-m-d');
        $flujo->hora_inicia = date('H:i:s');
        $flujo->orden_trabajo_id = $status->idorden;
        $flujo->proceso = $request->estado;
        $flujo->save();

        $flujoa = FlujoProduccion::where('orden_trabajo_id', $status->idorden)->orderBy('id', 'desc')
            ->skip(1)
            ->first();
        if ($flujoa) {
            $flujoa->fecha_termina = date('Y-m-d');
            $flujoa->hora_termina = date('H:i:s');
            $flujoa->cantidad = $request->cantidad_final ?: 0;
            $flujoa->usuario = $request->operario;
            $flujoa->observaciones = $request->observaciones;
            $flujoa->save();
        }

        $orden = Ordentrabajo::find($status->idorden);
        if ($orden) {
            if ($request->estado == $this->ESTADO_CAMBIO) {
                $orden->produccion = 'EM';
                $orden->save();
                self::cambiarEstadoPedido($orden, 2, [0, 1]);
            } elseif ($request->estado == $this->ULTIMO_ESTADO) {
                $orden->produccion = 'E';
                $orden->save();
                self::cambiarEstadoPedido($orden, 3, [1, 2]);
            } else {
                // Return to "En Produccion" if not in terminal/special states, EXCEPT if order or parent pedido is Pendiente ('P')
                if ($orden->produccion !== 'ENP' && $orden->produccion !== 'P') {
                    $isPendingPedido = \App\LineaComprobante::where('ordentrabajo_id', $orden->id)
                        ->whereHas('comprobante', function ($q) {
                            $q->where('tipo', 'pedido')->whereIn('estado', [0, 1]);
                        })->exists();

                    if (!$isPendingPedido) {
                        $orden->produccion = 'ENP';
                        $orden->save();
                    }
                }
            }

            // If moving away from 'Terminado', finalize the CuentaPorPagar for all operarias
            if (stripos((string)$estadoAnterior, 'Terminado') !== false && stripos((string)$request->estado, 'Terminado') === false) {
                $costosTerminados = CostoProduccion::where('ordentrabajo_id', $orden->id)
                    ->where('titulo', 'Terminado')
                    ->get();

                $cantidadFinal = (int)($request->cantidad_final ?: $orden->cantidad);

                foreach ($costosTerminados as $costoTerminado) {
                    if ($cantidadFinal > 0) {
                        $costoTerminado->cantidad = $cantidadFinal;
                        $costoTerminado->total = $cantidadFinal * $costoTerminado->valor;
                        $costoTerminado->save();
                    }

                    $cuenta = \App\CuentaPorPagar::where('costo_id', $costoTerminado->id)->first();
                    if ($cuenta) {
                        $cuenta->cantidad = $costoTerminado->cantidad;
                        $cuenta->cantidad_entregada = $costoTerminado->cantidad;
                        $cuenta->valor_unitario = $costoTerminado->valor;
                        $this->recalcularCuentaPorPagarTerminado($cuenta, false);
                    }
                }
            }
        }

        if (isset($orden) && $orden && $orden->linea && $orden->linea->comprobante_id) {
            $otController = new \App\Http\Controllers\OrdentrabajoController();
            $otController->verificarYActualizarPedido($orden->linea->comprobante_id);
        }

        return response()->json(['success' => true, 'status' => $status]);
    }
    public function cambiarPlancha(Request $request)
    {
        $orden = Ordentrabajo::find($request->id_orden);
        $orden->plancha = $request->id;

        // Synchronize final size from product if missing
        $articulo = Articulo::find($orden->articulo_id);
        if ($articulo && $articulo->medida_final && empty($orden->medida_final)) {
            $orden->medida_final = $articulo->medida_final;
        }

        // Extract cabida from plancha details and update order cabida
        $plancha = InventariosMateriaPrima::find($request->id);
        if ($plancha && $plancha->detalles) {
            preg_match('/[\d.]+/', $plancha->detalles, $matches);
            $numero = isset($matches[0]) ? (float)$matches[0] : 0;
            if ($numero > 0) {
                $orden->cabida = $numero;

                // Lookup matching medida_material based on cabida configuration of Articulo
                if ($articulo && !empty($articulo->cabidas_materiales)) {
                    $cabidasRaw = is_string($articulo->cabidas_materiales) ? json_decode($articulo->cabidas_materiales, true) : $articulo->cabidas_materiales;
                    if (is_array($cabidasRaw) || is_object($cabidasRaw)) {
                        foreach ($cabidasRaw as $cm) {
                            $cmArr = (array)$cm;
                            $cVal = isset($cmArr['cabida']) ? (float)$cmArr['cabida'] : 0;
                            $mVal = isset($cmArr['medida_material']) ? $cmArr['medida_material'] : '';
                            if ($cVal == $numero) {
                                $orden->medida_material = $mVal;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $orden->save();

        // Recalculate paper costs in the database
        $cabida = (float)$orden->cabida;
        $tamano = (float)$orden->tamano;
        $cantidad = (float)$orden->cantidad;
        $carpeta_cliente = (float)$orden->carpeta_cliente;

        $tamanos = ($cabida != 0) ? ($cantidad / $cabida) + $carpeta_cliente : $carpeta_cliente;
        $pliegos = ($cabida != 0 && $tamano != 0) ? $tamanos / $tamano : 0;

        $costoPapel = CostoProduccion::where('ordentrabajo_id', $orden->id)->where('titulo', 'Papel')->first();
        if ($costoPapel) {
            $costoPapel->descripcion = $tamanos;
            $costoPapel->cantidad = $pliegos;
            $costoPapel->total = $pliegos * $costoPapel->valor;
            $costoPapel->save();
        }
    }
    public function llenarProcesos()
    {
        $status = statusProduccion::select('observaciones', 'id')->whereNotNull('observaciones')->where('observaciones', '!=', '')->get();

        foreach ($status as $statu) {
            if ($statu->observaciones != '') {
                $procesos = json_decode($statu->observaciones);
                if (is_array($procesos)) {
                    foreach ($procesos as $p) {
                        $proceso = new Procesos();
                        $proceso->statusproduccion_id = $statu->id;
                        $proceso->proceso = $p->proceso;
                        $proceso->cantidad = $p->cantidad;
                        $proceso->fecha_termina = $p->fechaTermina;
                        $proceso->save();
                    }
                }

            } else {

            }
        }
    }
    public function asignarCostos(Request $request)
    {
        $costos = CostoProduccion::all();
        foreach ($costos as $costo) {
            $detalles = Detalletrabajo::where('ordentrabajo_id', $costo->ordentrabajo_id)->get();
            foreach ($detalles as $detalle) {
                if ($costo->titulo == 'Papel') {
                    if (strcasecmp(strtolower($detalle->titulo), strtolower('Papel')) === 0) {
                        $detalle->costos_id = $costo->id;
                        $detalle->save();
                    }
                }
            }
        }
    }

    public static function sincronizarPapelesOrden($ordenId)
    {
        $orden = Ordentrabajo::find($ordenId);
        if (!$orden) return;

        // 1. Obtener todos los detalles de papel de la orden
        $detallesPapel = Detalletrabajo::where('ordentrabajo_id', $ordenId)
            ->where(function($q) {
                $q->where('titulo', 'Papel')->orWhere('titulo', 'papel');
            })
            ->get();

        // 2. Obtener todos los costos de papel de la orden
        $costosPapel = CostoProduccion::where('ordentrabajo_id', $ordenId)
            ->where(function($q) {
                $q->where('titulo', 'Papel')->orWhere('titulo', 'papel');
            })
            ->get();

        // Si no hay detalles de papel pero SÍ hay costos de papel, crear los detalles de papel faltantes (soporte ordenes legacy)
        if ($detallesPapel->count() === 0 && $costosPapel->count() > 0) {
            foreach ($costosPapel as $c) {
                $det = new Detalletrabajo();
                $det->ordentrabajo_id = $ordenId;
                $det->titulo = 'Papel';
                $det->valor = ($c->costois && $c->costois->nombre) ? $c->costois->nombre : ($c->descripcion ? $c->descripcion : 'Papel');
                $det->costos_id = $c->id;
                $det->save();
            }
            return;
        }

        if ($detallesPapel->count() === 0 && $costosPapel->count() === 0) {
            return;
        }

        $costosProcesadosIds = [];

        // 3. Garantizar que cada detalle de papel tenga 1 costo de papel emparejado
        foreach ($detallesPapel as $idx => $det) {
            $costo = null;
            if (!empty($det->costos_id) && $det->costos_id > 0) {
                $costo = $costosPapel->where('id', $det->costos_id)->first();
            }
            if (!$costo && isset($costosPapel[$idx])) {
                $costo = $costosPapel[$idx];
            }

            if (!$costo) {
                $costo = new CostoProduccion();
                $costo->ordentrabajo_id = $ordenId;
                $costo->costois_id = 0;
                $costo->titulo = 'papel';
                $costo->cantidad = $orden->cantidad;
                if (\Illuminate\Support\Facades\Schema::hasColumn('costos', 'medida_material')) {
                    $costo->medida_material = $orden->medida_material ?? '';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('costos', 'tamano')) {
                    $costo->tamano = $orden->tamano ?? '0';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('costos', 'cabida')) {
                    $costo->cabida = $orden->cabida ?? '0';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('costos', 'sobrante')) {
                    $costo->sobrante = $orden->carpeta_cliente ?? '0';
                }
                $costo->descripcion = is_numeric($orden->cantidad) ? $orden->cantidad : 0;
                $costo->save();
            }

            // Vincular bidireccionalmente
            $det->costos_id = $costo->id;
            $det->save();

            $costosProcesadosIds[] = $costo->id;
        }

        // 4. PURGAR: Eliminar cualquier registro de costo de papel huérfano que no esté en costosProcesadosIds
        CostoProduccion::where('ordentrabajo_id', $ordenId)
            ->where(function($q) {
                $q->where('titulo', 'Papel')->orWhere('titulo', 'papel');
            })
            ->whereNotIn('id', $costosProcesadosIds)
            ->delete();
    }

    public function guardarMaterial(Request $request)
    {
        $orden = Ordentrabajo::find($request->id);
        if ($orden) {
            if ($request->has('tamano')) $orden->tamano = $request->tamano;
            if ($request->has('medida_final')) $orden->medida_final = $request->medida_final;
            if ($request->has('medida_material')) $orden->medida_material = $request->medida_material;
            if ($request->has('carpeta_cliente')) $orden->carpeta_cliente = $request->carpeta_cliente;
            if ($request->has('cabida')) $orden->cabida = $request->cabida;
            if ($request->has('observaciones')) $orden->observaciones = $request->observaciones;
            $orden->save();
        }

        if ($request->has('papeles')) {
            $papeles = json_decode($request->papeles, true);
            if (is_array($papeles)) {
                foreach ($papeles as $pData) {
                    $costo = null;
                    if (!empty($pData['costos_id']) && $pData['costos_id'] > 0) {
                        $costo = CostoProduccion::find($pData['costos_id']);
                    }
                    if (!$costo && !empty($pData['id']) && $pData['id'] > 0) {
                        $costo = CostoProduccion::find($pData['id']);
                    }
                    if (!$costo) {
                        $costo = CostoProduccion::where('ordentrabajo_id', $orden->id)
                            ->where(function($q) {
                                $q->where('titulo', 'Papel')->orWhere('titulo', 'papel');
                            })
                            ->first();
                    }
                    if (!$costo) {
                        $costo = new CostoProduccion();
                        $costo->ordentrabajo_id = $orden->id;
                        $costo->costois_id = 0;
                        $costo->titulo = 'papel';
                    }

                    if (isset($pData['corte']) && \Illuminate\Support\Facades\Schema::hasColumn('costos', 'medida_material')) $costo->medida_material = $pData['corte'];
                    if (isset($pData['tamano']) && \Illuminate\Support\Facades\Schema::hasColumn('costos', 'tamano')) $costo->tamano = $pData['tamano'];
                    if (isset($pData['cabida']) && \Illuminate\Support\Facades\Schema::hasColumn('costos', 'cabida')) $costo->cabida = $pData['cabida'];
                    if (isset($pData['sobrante']) && \Illuminate\Support\Facades\Schema::hasColumn('costos', 'sobrante')) $costo->sobrante = $pData['sobrante'];
                    if (isset($pData['pliegos'])) $costo->cantidad = $pData['pliegos'];
                    if (isset($pData['tamanosSobrante']) && is_numeric($pData['tamanosSobrante'])) $costo->descripcion = $pData['tamanosSobrante'];
                    if (!empty($pData['costois_id']) && $pData['costois_id'] > 0) {
                        $costo->costois_id = $pData['costois_id'];
                    }
                    if (isset($pData['componente']) && \Illuminate\Support\Facades\Schema::hasColumn('costos', 'componente')) {
                        $costo->componente = $pData['componente'];
                    }
                    $costo->save();

                    // Actualizar o crear Detalletrabajo vinculado
                    $det = null;
                    if (!empty($pData['detalle_id']) && $pData['detalle_id'] > 0) {
                        $det = Detalletrabajo::find($pData['detalle_id']);
                    }
                    if (!$det) {
                        $det = Detalletrabajo::where('ordentrabajo_id', $orden->id)
                            ->where('costos_id', $costo->id)
                            ->first();
                    }
                    if (!$det) {
                        $det = new Detalletrabajo();
                        $det->ordentrabajo_id = $orden->id;
                        $det->costos_id = $costo->id;
                        $det->titulo = 'Papel';
                    }
                    if (isset($pData['nombre'])) {
                        $det->valor = $pData['nombre'];
                    }
                    $det->save();
                }
            }
        } elseif ($request->has('detalle')) {
            $detalle = json_decode($request->detalle);
            if (isset($detalle->costos_id) && $detalle->costos_id > 0) {
                $costo = CostoProduccion::find($detalle->costos_id);
                if ($costo) {
                    if (isset($detalle->costo->cantidad)) $costo->cantidad = $detalle->costo->cantidad;
                    if (isset($detalle->costo->descripcion)) $costo->descripcion = $detalle->costo->descripcion;
                    $costo->save();
                }
            }
        }

        // Ejecutar purga y sincronización 1-a-1 de papeles
        if ($orden) {
            self::sincronizarPapelesOrden($orden->id);
        }
        return response()->json(['status' => 'success']);
    }
    public static function crearCostosDetalles($detalles, $costos, $id, $cambios, $user_id)
    {
        $cambiosCostos = '';
        $cambiosDetalles = '';
        $detalleNuevo = '';
        $costoNuevo = '';
        $costo_id = 0;
        $cambios_base = 'Se actualizo la orden #' . $id;
        if (!empty($cambios)) {
            $cambios_base .= ', Cambios: ' . $cambios;
        }
        $cambios = $cambios_base;
        foreach ($detalles as $index => $det) {
            if ($det->id == 0 && strtolower($det->titulo) == 'papel') {
                $deta = Detalletrabajo::where('ordentrabajo_id', $id)->where('titulo', 'papel')->get();
                $co = CostoProduccion::where('ordentrabajo_id', $id)->where('titulo', 'Papel')->get();
                if (count($deta) > 1) {
                    foreach ($deta as $d) {
                        if ($d->costo) {
                            $d->costo->delete();
                        }
                        $d->delete();
                    }
                    foreach ($co as $c) {
                        $c->delete();
                    }
                }
            }

            // Always process costs if they are provided, regardless of detail title
            if (isset($det->costo) && is_object($det->costo)) {
                if ($det->costos_id == 0) {
                    $costo = new CostoProduccion();
                    $costo->ordentrabajo_id = $id;
                    $costoNuevo .= 'Costo: ' . $det->costo->titulo . ', ';
                } else {
                    $costo = CostoProduccion::find($det->costos_id);
                    if (!$costo) {
                        $costo = new CostoProduccion();
                        $costo->ordentrabajo_id = $id;
                    } else {
                        $costo->costois;
                        $cambiosCostos .= ($costo->cantidad != $det->costo->cantidad) ? 'Costo ' . $costo->titulo . '-> Cantidad: ' . $det->costo->cantidad . ', ' : '';
                    }
                }
                $costo->costois_id = $det->costo->costois_id;
                $costo->titulo = $det->costo->titulo;
                $costo->descripcion = $det->costo->descripcion;
                $costo->cantidad = $det->costo->cantidad;
                $costo->valor = $det->costo->valor ?? 0;
                $costo->pago = 0;
                $costo->total = $costo->cantidad * $costo->valor;
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
                    $valor .= isset($val->pantone) ? $val->pantone . ' ' : '';
                }
            } else {
                $valor = $det->valor;
            }

            $detalle = null;
            if (isset($det->id) && $det->id != 0) {
                $detalle = Detalletrabajo::find($det->id);
            }

            if (!$detalle) {
                $detalle = new Detalletrabajo();
                $detalle->ordentrabajo_id = $id;
                $detalleNuevo .= 'Titulo: ' . $det->titulo . ', Valor: ' . $valor . ', Descripcion: ' . $det->descripcion;
            } else {
                $cambiosDetalles .= ($detalle->descripcion != $det->descripcion) ? 'Detalle ' . $det->titulo . '-> Descripcion: ' . $det->descripcion . ', ' : '';
                $cambiosDetalles .= ($detalle->titulo != $det->titulo) ? 'Detalle ' . $det->titulo . '-> Titulo: ' . $det->titulo . ', ' : '';
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

        self::sincronizarPapelesOrden($id);
        return $detalles;

    }
    public function guardarOpciones(Request $request)
    {
        $costos = json_decode($request->costo);
        $detalles = json_decode($request->detalles);

        $detco = self::crearCostosDetalles($detalles, $costos, $request->id, '', 1);
        self::sincronizarPapelesOrden($request->id);
        return $detco;


    }
    public function asignarActivo(Request $request)
    {
        $activo = json_decode($request->activo);
        
        if ($activo->tipo == 'Terminado') {
            // Check if any existing Terminado cost has a CuentaPorPagar with abonos
            $existentes = CostoProduccion::where('titulo', 'Terminado')
                ->where('ordentrabajo_id', $request->ordenid)
                ->get();
            
            foreach ($existentes as $e) {
                $cuenta = \App\CuentaPorPagar::where('costo_id', $e->id)->first();
                if ($cuenta && $cuenta->abonos()->count() > 0) {
                    return response()->json([
                        'error' => 'No se puede reasignar la operaria porque ya existen abonos registrados en la cuenta de cobro de una de las operarias.'
                    ], 422);
                }
            }

            // Delete existing accounts and cost records for Terminado
            foreach ($existentes as $e) {
                $cuenta = \App\CuentaPorPagar::where('costo_id', $e->id)->first();
                if ($cuenta) {
                    $cuenta->delete();
                }
                $e->delete();
            }

            // Create a new one
            $costo = new CostoProduccion();
        } else {
            $verificarCosto = CostoProduccion::where('titulo', $activo->tipo)->where('ordentrabajo_id', $request->ordenid)->get();
            if (count($verificarCosto) == 0) {
                $costo = new CostoProduccion();
            } else {
                $costo = CostoProduccion::find($verificarCosto[0]->id);
            }
        }

        $costo->titulo = $activo->tipo;
        $costo->descripcion = 0;
        $costo->costois_id = $activo->id;
        $costo->ordentrabajo_id = $request->ordenid;
        $costo->save();

        if ($activo->tipo === 'Troquelado') {
            $troquelados = CostoProduccion::where('ordentrabajo_id', $request->ordenid)
                ->where('titulo', 'Troquelado')
                ->get();
            foreach ($troquelados as $t) {
                $t->activo;
                $t->costois;
            }
            return response()->json([
                'success' => true,
                'tipo' => 'Troquelado',
                'troquelado' => $troquelados
            ]);
        } else if ($activo->tipo === 'Terminado') {
            $terminados = CostoProduccion::where('ordentrabajo_id', $request->ordenid)
                ->where('titulo', 'Terminado')
                ->get();
            foreach ($terminados as $t) {
                $t->activo;
                $t->costois;
                $t->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $t->id)->first();
            }
            return response()->json([
                'success' => true,
                'tipo' => 'Terminado',
                'terminado' => $terminados
            ]);
        }

        return response()->json([
            'success' => true,
            'costo' => $costo
        ]);
    }
    public function cambiarDatosEstado(Request $request)
    {
        $orden = $request->orden;
        $statu = $request->orden['status'];
        $status = statusProduccion::where('idorden', '=', $request->id)->first();
        if (!$status)
            return response()->json(['error' => 'Status not found'], 404);
        $status->observaciones = $statu['observaciones'];
        $status->fecha_termina = $statu['fecha_termina'];
        $status->hora = $statu['hora'];
        $status->save();
        if ($status->estado == 'Para entregar') {
            $ord = Ordentrabajo::find($orden['id']);
            if ($ord->valor_unitario == 0) {
                $valor_unitario = $ord->totalParcial / $ord->cantidad;
                $ord->valor_unitario = $valor_unitario;
            }
            $ord->cantidad = $status->observaciones;
            $ord->totalParcial = $ord->valor_unitario * $ord->cantidad;
            $ord->total = $ord->totalParcial;
            $ord->save();

            $linea = LineaComprobante::where('ordentrabajo_id', $orden['id'])->get()[0];
            $linea->valor_unitario = $ord->valor_unitario;
            $linea->cantidad = $status->observaciones;
            $linea->subtotal = $ord->totalParcial;
            $linea->valor_total = $ord->total;
            $linea->save();
            $lineas = LineaComprobante::where('comprobante_id', $linea->comprobante_id)->get();
            $subtotal = 0;
            foreach ($lineas as $li) {
                $subtotal = $subtotal + $li->valor_total;
            }
            $pedido = Comprobante::find($linea->comprobante_id);
            $pedido->subtotal = $subtotal;
            $pedido->total = $subtotal - $pedido->descuento;
            $pedido->save();

            // Sync with terminados CostoProduccion and CuentaPorPagar
            $costosTerminados = CostoProduccion::where('ordentrabajo_id', $ord->id)
                ->where('titulo', 'Terminado')
                ->get();
            foreach ($costosTerminados as $costoTerminado) {
                if ((int)$ord->cantidad > 0) {
                    $costoTerminado->cantidad = $ord->cantidad;
                    $costoTerminado->total = $ord->cantidad * $costoTerminado->valor;
                    $costoTerminado->save();
                }

                $cuenta = \App\CuentaPorPagar::where('costo_id', $costoTerminado->id)->first();
                if ($cuenta) {
                    $cuenta->cantidad = $costoTerminado->cantidad;
                    $cuenta->cantidad_entregada = $costoTerminado->cantidad;
                    $cuenta->valor_unitario = $costoTerminado->valor;
                    $this->recalcularCuentaPorPagarTerminado($cuenta, false);
                }
            }
        }
        // $proceso=new Procesos();
        // $proceso->cantidad=$status->observaciones;
        // $proceso->hora=$status->hora;
        // $proceso->save();

    }
    public function cambiarPrioridad(Request $request)
    {
        $ordenes = $request->ordenes;
        if (empty($request->id)) {
            if (is_array($ordenes)) {
                foreach ($ordenes as $orden) {
                    if (empty($orden['status'])) {
                        continue;
                    }
                    $statu = $orden['status'];
                    $status = null;
                    if (!empty($statu['id'])) {
                        $status = statusProduccion::find($statu['id']);
                    }
                    if (!$status && !empty($orden['id'])) {
                        $status = statusProduccion::where('idorden', $orden['id'])->first();
                    }
                    if (!$status) {
                        $status = new statusProduccion();
                        $status->idorden = $orden['id'] ?? null;
                        $status->estado = $statu['estado'] ?? 'Sin Estado';
                    }
                    if ($status->idorden) {
                        $status->prioridad = $statu['prioridad'] ?? 1;
                        $status->save();
                    }
                }
            }
        } else {
            $orden = $request->orden;
            if ($orden && !empty($orden['status'])) {
                $statu = $orden['status'];
                $status = null;
                if (!empty($statu['id'])) {
                    $status = statusProduccion::find($statu['id']);
                }
                if (!$status && !empty($orden['id'])) {
                    $status = statusProduccion::where('idorden', $orden['id'])->first();
                }
                if (!$status) {
                    $status = new statusProduccion();
                    $status->idorden = $orden['id'] ?? null;
                    $status->estado = $statu['estado'] ?? 'Sin Estado';
                }
                if ($status->idorden) {
                    $status->prioridad = $statu['prioridad'] ?? 1;
                    $status->save();
                }
            }
        }

    }
    public function actualizarValorTerminado(Request $request)
    {
        $this->validate($request, [
            'orden_id' => 'required|integer|exists:ordentrabajos,id',
            'valor' => 'required|numeric|min:0'
        ]);

        $orden = Ordentrabajo::findOrFail($request->orden_id);
        
        // Find if a finishing cost record exists (created when assigning operaria)
        $costo = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->first();

        if (!$costo) {
            return response()->json([
                'success' => false, 
                'error' => 'Debe asignar una operaria de terminado primero en la orden.'
            ], 422);
        }

        $costo->cantidad = $orden->cantidad;
        $costo->valor = $request->valor;
        $costo->total = $orden->cantidad * $request->valor;
        $costo->save();

        // Sync with CuentaPorPagar
        $activo = Activo::find($costo->costois_id);
        $nombreActivo = $activo ? $activo->activo : 'Sin Nombre';

        $status = \App\statusProduccion::where('idorden', $orden->id)->first();
        $isTerminado = ($status && $status->estado === 'Terminado');

        $cuenta = \App\CuentaPorPagar::where('costo_id', $costo->id)->first();
        $actualizarCuenta = $request->input('actualizar_cuenta', true);

        if ($cuenta) {
            if ($actualizarCuenta) {
                $cuenta->activo_id = $costo->costois_id;
                $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $nombreActivo . ")";
            }
            $cuenta->cantidad = $orden->cantidad;
            $cuenta->valor_unitario = $request->valor;
            $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);
        } else {
            $cuenta = new \App\CuentaPorPagar();
            $cuenta->activo_id = $costo->costois_id;
            $cuenta->ordentrabajo_id = $orden->id;
            $cuenta->costo_id = $costo->id;
            $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $nombreActivo . ")";
            $cuenta->cantidad = $orden->cantidad;
            $cuenta->cantidad_entregada = 0;
            $cuenta->valor_unitario = $request->valor;
            $cuenta->fecha = date('Y-m-d');
            $cuenta->save();
            $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);
        }

        $terminados = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->get();
        foreach ($terminados as $term) {
            $term->activo;
            $term->costois;
            $term->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $term->id)->first();
        }

        return response()->json([
            'success' => true,
            'terminado' => $terminados
        ]);
    }

    private function recalcularCuentaPorPagarTerminado($cuenta, $isTerminado = false)
    {
        $totalAbonos = (float) $cuenta->abonos()->sum('monto');
        $cantEntregada = (int) ($cuenta->cantidad_entregada ?? 0);
        $cantTotal = (int) ($cuenta->cantidad ?? 0);
        $valUnit = (float) ($cuenta->valor_unitario ?? 0);

        if ($cantEntregada > 0 && $cantEntregada < $cantTotal) {
            $montoLiquidadas = $cantEntregada * $valUnit;
            $cuenta->monto = $montoLiquidadas;
            $cuenta->saldo = max(0.0, $montoLiquidadas - $totalAbonos);
        } else {
            $montoTotal = $cantTotal * $valUnit;
            $cuenta->monto = $montoTotal;
            $cuenta->saldo = max(0.0, $montoTotal - $totalAbonos);
        }

        if ($cuenta->saldo <= 0 && $cantEntregada >= $cantTotal && $cantTotal > 0) {
            $cuenta->estado = 'Pagado';
        } elseif ($totalAbonos > 0) {
            $cuenta->estado = 'Abonado';
        } else {
            $cuenta->estado = $isTerminado ? 'En Espera' : 'Pendiente';
        }
        $cuenta->save();
    }

    public function actualizarCantidadEntregada(Request $request)
    {
        $this->validate($request, [
            'orden_id' => 'required|integer|exists:ordentrabajos,id',
            'cantidad_entregada' => 'required|integer|min:0'
        ]);

        $orden = Ordentrabajo::findOrFail($request->orden_id);
        $costo = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->first();

        if (!$costo) {
            return response()->json([
                'success' => false, 
                'error' => 'Debe asignar una operaria de terminado primero en la orden.'
            ], 422);
        }

        $cuenta = \App\CuentaPorPagar::where('costo_id', $costo->id)->first();
        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'error' => 'No se encontró la cuenta de cobro asociada.'
            ], 422);
        }

        if ($request->cantidad_entregada > $cuenta->cantidad) {
            return response()->json([
                'success' => false,
                'error' => 'La cantidad entregada no puede ser mayor a la cantidad total de la orden (' . $cuenta->cantidad . ').'
            ], 422);
        }

        $cuenta->cantidad_entregada = $request->cantidad_entregada;
        $cuenta->save();

        $status = statusProduccion::where('idorden', $orden->id)->first();
        $isTerminado = ($status && $status->estado === 'Terminado');
        $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);

        $terminados = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->get();
        foreach ($terminados as $term) {
            $term->activo;
            $term->costois;
            $term->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $term->id)->first();
        }

        return response()->json([
            'success' => true,
            'terminado' => $terminados
        ]);
    }
    public function asignarOperariaTerminado(Request $request)
    {
        $this->validate($request, [
            'orden_id' => 'required|integer|exists:ordentrabajos,id',
            'activo_id' => 'required|integer|exists:activos,id',
            'cantidad' => 'required|numeric|min:0',
            'valor' => 'required|numeric|min:0'
        ]);

        $orden = Ordentrabajo::findOrFail($request->orden_id);
        $activo = Activo::findOrFail($request->activo_id);

        $costo = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->where('costois_id', $activo->id)
            ->first();

        if (!$costo) {
            $costo = new CostoProduccion();
            $costo->ordentrabajo_id = $orden->id;
            $costo->costois_id = $activo->id;
            $costo->titulo = 'Terminado';
            $costo->descripcion = 0;
        }

        $costo->cantidad = $request->cantidad;
        $costo->valor = $request->valor;
        $costo->total = $request->cantidad * $request->valor;
        $costo->save();

        $status = statusProduccion::where('idorden', $orden->id)->first();
        $isTerminado = ($status && $status->estado === 'Terminado');

        $cuenta = \App\CuentaPorPagar::where('costo_id', $costo->id)->first();
        if ($cuenta) {
            $cuenta->activo_id = $activo->id;
            $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $activo->activo . ")";
            $cuenta->cantidad = $request->cantidad;
            $cuenta->valor_unitario = $request->valor;
            $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);
        } else {
            $cuenta = new \App\CuentaPorPagar();
            $cuenta->activo_id = $activo->id;
            $cuenta->ordentrabajo_id = $orden->id;
            $cuenta->costo_id = $costo->id;
            $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $activo->activo . ")";
            $cuenta->cantidad = $request->cantidad;
            $cuenta->cantidad_entregada = 0;
            $cuenta->valor_unitario = $request->valor;
            $cuenta->fecha = date('Y-m-d');
            $cuenta->save();
            $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);
        }

        $terminados = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->get();
        foreach ($terminados as $term) {
            $term->activo;
            $term->costois;
            $term->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $term->id)->first();
        }

        return response()->json([
            'success' => true,
            'terminado' => $terminados
        ]);
    }

    public function actualizarOperariaTerminado(Request $request)
    {
        $this->validate($request, [
            'costo_id' => 'required|integer|exists:costos,id',
            'cantidad' => 'required|numeric|min:0',
            'valor' => 'required|numeric|min:0',
            'cantidad_entregada' => 'required|numeric|min:0'
        ]);

        $costo = CostoProduccion::findOrFail($request->costo_id);
        $orden = Ordentrabajo::findOrFail($costo->ordentrabajo_id);
        $activo = Activo::find($costo->costois_id);
        $nombreActivo = $activo ? $activo->activo : 'Sin Nombre';

        if ($request->cantidad_entregada > $request->cantidad) {
            return response()->json([
                'success' => false,
                'error' => 'La cantidad entregada no puede ser mayor a la cantidad asignada (' . $request->cantidad . ').'
            ], 422);
        }

        $costo->cantidad = $request->cantidad;
        $costo->valor = $request->valor;
        $costo->total = $request->cantidad * $request->valor;
        $costo->save();

        $status = statusProduccion::where('idorden', $orden->id)->first();
        $isTerminado = ($status && $status->estado === 'Terminado');

        $cuenta = \App\CuentaPorPagar::where('costo_id', $costo->id)->first();
        if ($cuenta) {
            $cuenta->activo_id = $costo->costois_id;
            $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $nombreActivo . ")";
            $cuenta->cantidad = $request->cantidad;
            $cuenta->cantidad_entregada = $request->cantidad_entregada;
            $cuenta->valor_unitario = $request->valor;
            $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);
        } else {
            $cuenta = new \App\CuentaPorPagar();
            $cuenta->activo_id = $costo->costois_id;
            $cuenta->ordentrabajo_id = $orden->id;
            $cuenta->costo_id = $costo->id;
            $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $nombreActivo . ")";
            $cuenta->cantidad = $request->cantidad;
            $cuenta->cantidad_entregada = $request->cantidad_entregada;
            $cuenta->valor_unitario = $request->valor;
            $cuenta->fecha = date('Y-m-d');
            $cuenta->save();
            $this->recalcularCuentaPorPagarTerminado($cuenta, $isTerminado);
        }

        $terminados = CostoProduccion::where('ordentrabajo_id', $orden->id)
            ->where('titulo', 'Terminado')
            ->get();
        foreach ($terminados as $term) {
            $term->activo;
            $term->costois;
            $term->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $term->id)->first();
        }

        return response()->json([
            'success' => true,
            'terminado' => $terminados
        ]);
    }

    public function eliminarOperariaTerminado(Request $request)
    {
        $this->validate($request, [
            'costo_id' => 'required|integer|exists:costos,id'
        ]);

        $costo = CostoProduccion::findOrFail($request->costo_id);
        $cuenta = \App\CuentaPorPagar::where('costo_id', $costo->id)->first();

        if ($cuenta) {
            if ($cuenta->abonos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede eliminar esta operaria porque ya registra abonos en su cuenta de cobro.'
                ], 422);
            }
            $cuenta->delete();
        }

        $costo->delete();

        $terminados = CostoProduccion::where('ordentrabajo_id', $costo->ordentrabajo_id)
            ->where('titulo', 'Terminado')
            ->get();
        foreach ($terminados as $term) {
            $term->activo;
            $term->costois;
            $term->cuenta_cobro = \App\CuentaPorPagar::where('costo_id', $term->id)->first();
        }

        return response()->json([
            'success' => true,
            'terminado' => $terminados
        ]);
    }

    public function checkUpdates()
    {
        $tables = ["statusproduccion", "ordentrabajos", "flujo_produccion", "detalletrabajos", "costos", "comprobantes", "linea_comprobantes", "costois"];
        $fingerprintSeed = "";
        foreach ($tables as $table) {
            $fingerprintSeed .= \Illuminate\Support\Facades\DB::table($table)->max("updated_at") . \Illuminate\Support\Facades\DB::table($table)->count();
        }
        $fingerprint = md5($fingerprintSeed);
        return response()->json(["last_update" => $fingerprint, "timestamp" => now()->toDateTimeString()]);
    }
}

