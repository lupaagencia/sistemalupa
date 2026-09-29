<?php

namespace App\Http\Controllers;
use App\Ordentrabajo;
use App\CostoProduccion;
use App\Detalletrabajo;
use App\statusProduccion;
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
use App\http\Controllers\OrdentrabajoController;
use App\Http\Controllers\StatusProduccionController;
use phpDocumentor\Reflection\Types\Self_;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $id = array();
        if ($criterio == 'cliente_id' && $buscar != '' && !is_numeric($buscar)) {
            $cliente = \App\Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->first();
            if ($cliente) {
                $buscar = $cliente->id;
            }
        }

        $estadoFiltro = $request->estado_filtro;

        $query = Comprobante::with([
            'cliente.empresas',
            'cliente.envios',
            'cliente.contactos',
            'lineas.articulo.tipo.atributos',
            'lineas.orden.detalles'
        ])->where('tipo', 'pedido');

        if ($buscar != '' && $buscar != 'todos') {
            if ($criterio == 'estado') {
                $query->where('estado', (int) $buscar);
            } else if ($criterio == 'cliente_id') {
                $query->where('cliente_id', $buscar);
            } else if (!empty($criterio)) {
                $query->where($criterio, 'LIKE', '%' . $buscar . '%');
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
                        $linea->detalles = $linea->orden->detalles;
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

        $query = Comprobante::with(['cliente', 'lineas.articulo'])
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
        $cotizacion = Comprobante::findOrFail($request->id);
        if ($cotizacion->tipo != 'cotizacion') {
            return response()->json(['error' => 'El documento no es una cotización'], 400);
        }

        $cotizacion->tipo = 'pedido';
        $cotizacion->estado = 1; // Estado inicial de pedido
        $cotizacion->save();

        return $cotizacion;
    }

    public function facturas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $per_page = $request->per_page;
        $id = array();
        if ($buscar == '') {
            $comprobantes = Comprobante::with([
                'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
                'lineas.articulo.tipo.atributos',
                'lineas.orden.detalles.costo.costois',
                'lineas.orden.costos.costois',
                'lineas.orden.status'
            ])->where('tipo', 'factura')
                ->orderBy('comprobantes.id', 'desc')->paginate($per_page);
        } else {
            if ($criterio == 'cliente_id' && !is_numeric($buscar)) {
                $cliente = Cliente::where('razonsocial', 'LIKE', '%' . $buscar . '%')->first();
                if ($cliente) {
                    $buscar = $cliente->id;
                }
            }
            if (!empty($criterio)) {
                $comprobantes = Comprobante::with([
                    'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
                    'lineas.articulo.tipo.atributos',
                    'lineas.orden.detalles.costo.costois',
                    'lineas.orden.costos.costois',
                    'lineas.orden.status'
                ])->where($criterio, 'LIKE', '%' . $buscar . '%')
                    ->where('tipo', 'factura')
                    ->orderBy('comprobantes.id', 'desc')->paginate($per_page);
            } else {
                $comprobantes = Comprobante::with([
                    'cliente.empresas', 'cliente.envios', 'cliente.contactos', 'razonsocial',
                    'lineas.articulo.tipo.atributos',
                    'lineas.orden.detalles.costo.costois',
                    'lineas.orden.costos.costois',
                    'lineas.orden.status'
                ])->where('tipo', 'factura')
                    ->orderBy('comprobantes.id', 'desc')->paginate($per_page);
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

    public function cuentasCobro(Request $request)
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
            ->where('tipo', 'cuentacobro');

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
    public function eliminar(Request $request)
    {
        $id = $request->id;
        $lineas = LineaComprobante::where('comprobante_id', $id)->get();
        foreach ($lineas as $linea) {
            if (isset($linea->orden)) {
                $linea->orden->delete();
            }
        }
        $comprobante = Comprobante::find($id);
        $comprobante->delete();
        $datosA = new stdClass();
        $datosA->user_id = $request->user_id;
        $datosA->actividad = 'Se elimino pedido #' . $id;
        $actividad = new ActividadController();
        $actividad->store($datosA);
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

        $nuevoEstado = $request->estado;
        if (($nuevoEstado == 3 || $nuevoEstado == 4 || $nuevoEstado == 5) && ($comprobante->tipo == 'pedido')) {
            // Regla de saldo para estados finales de pedido
            if ($comprobante->saldo > 0) {
                if ($request->estado == 4 || $request->estado == 5) {
                    $nuevoEstado = $request->estado; // Permitir pasar a para entregar (4) o entregado (5) aunque haya saldo
                } else {
                    $nuevoEstado = 3; // Completado (con deuda)
                }
            } else {
                // Si el saldo es 0, respetamos 5 si se pidió 5, o 4 si se pidió 4
                if ($request->estado >= 5) {
                    $nuevoEstado = 5; // Entregado final
                } else {
                    $nuevoEstado = 4; // Para entregar (paid but needs confirmation in list)
                }
            }
        }

        $comprobante->estado = $nuevoEstado;
        $comprobante->save();
        $comprobante->lineas;
        foreach ($comprobante->lineas as $linea) {
            if (!$linea->orden)
                continue;
            $status = statusProduccion::where('idorden', $linea->orden->id)->get();
            if ($request->estado == 2) {
                $linea->orden->produccion = 'ENP';
                $linea->orden->estado = 'VC';
                $status[0]->estado = $this->PRIMER_ESTADO;
            } elseif ($request->estado == 1) {
                $linea->orden->estado = 'PA';
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
            $orden->plancha = $request->orden->plancha ?? null;
            $orden->impresa = 0;
            $orden->cliente_id = $pedido->cliente_id;
            $orden->articulo_id = $request->articulo_id;
            $orden->carpeta_cliente = $request->orden->carpeta_cliente ?? null;
            $date = date('Y-m-d');
            $mod_date = strtotime($date . "12 days");
            $orden->fecha_entrega = Carbon::parse($pedido->fecha)->addDays(12);
            $fecha_entrega = date('Y-m-d', $mod_date);
            $orden->detalles_diseno = !empty($request->orden->detalles_diseno) ? $request->orden->detalles_diseno : null;
            $orden->fecha = $pedido->fecha;
            $orden->observaciones = !empty($request->orden->observaciones) ? $request->orden->observaciones : null;
            $orden->cabida = $request->cabida ?? $request->orden->cabida ?? null;
            $orden->medida_final = $request->medida_final ?? $request->orden->medida_final ?? null;
            $orden->tamano = $request->tamano ?? $request->orden->tamano ?? null;
            $orden->medida_material = !empty($request->medida_material) ? $request->medida_material : (!empty($request->orden->medida_material) ? $request->orden->medida_material : null);
            $orden->cantidad = $request->cantidad;
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
        foreach ($detalles as $det) {
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
            if ($det->id == 0) {

                $detalle = new Detalletrabajo();
                $detalle->ordentrabajo_id = $orden->id;

                $detalleNuevo .= 'Titulo: ' . $det->titulo . ', Valor: ' . $valor . ', Descripcion: ' . $det->descripcion;
            } else {
                $detalle = Detalletrabajo::find($det->id);
                $cambiosDetalles .= ($detalle->descripcion != $det->descripcion) ? 'Detalle ' . $det->titulo . '-> Descripcion: ' . $det->descripcion . ', ' : '';
                $cambiosDetalles .= ($detalle->valor != $det->valor) ? 'Detalle ' . $det->titulo . '-> Valor: ' . $valor . ', ' : '';
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
            if ($edit == 1) {
                $pedi = Comprobante::find($pedido->id);
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
            $pedi->impuestos = $pedido->impuestos;
            $pedi->fuente_id = 0;
            $pedi->iva = $pedido->iva;
            $pedi->save();
            foreach ($pedido->lineas as $pro) {
                if ($pro->ordentrabajo_id == 0) {
                    $orden = self::crearOrden($pro, $pedi);
                    if (!$orden || !$orden->id) {
                        throw new Exception("No se pudo crear la Orden de Trabajo.");
                    }
                } else {
                    $ord = $pro->orden;

                    $orden = Ordentrabajo::find($pro->ordentrabajo_id);
                    if (!$orden) {
                        throw new Exception("No se encontró la Orden de Trabajo relacionada (#" . $pro->ordentrabajo_id . ").");
                    }
                    $orden->detalles_diseno = $ord->detalles_diseno ?? null;
                    $orden->observaciones = $ord->observaciones ?? null;
                    $orden->tamano = $ord->tamano ?? null;
                    $orden->medida_material = $ord->medida_material ?? null;
                    $orden->cabida = $ord->cabida ?? null;
                    $orden->medida_final = $ord->medida_final ?? null;
                    $orden->carpeta_cliente = $ord->carpeta_cliente ?? null;
                    $orden->plancha = $ord->plancha ?? null;
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
                    if ($pedido->estado != 1) {
                        $orden->estado = 'VC';
                    } else {
                        $orden->estado = 'PA';
                    }
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
                $linea->save();

            }
            DB::commit();
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

        $v = new CifrasEnLetras();
        //Convertimos el total en letras
        $pedido->letras = ($v->convertirEurosEnLetras(number_format($pedido->total, 0)));
        $id = array();
        foreach ($pedido->lineas as $linea) {
            if ($pedido->impuestos > 0) {
                $linea->valorconiva = $linea->valor_unitario * 1.19;
                $linea->valortotalconiva = $linea->valor_total * 1.19;
            } else {
                $linea->valorconiva = $linea->valor_unitario;
                $linea->valortotalconiva = $linea->valor_total;
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

        // Asignar cliente desde razonsocial o cliente segun disponibilidad
        $factura->cliente = $factura->razonsocial ?? $factura->cliente;

        if ($factura->cliente) {
            $factura->cliente->empresas;
            $factura->cliente->envios;
            $factura->cliente->contactos;
        }

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
                $linea->detalles = $linea->orden->detalles;
            } else {
                $linea->detalles = [];
            }
        }

        $pdf = PDF::loadView('pdf.proforma', compact('factura'));
        $pdf->setPaper('carta', 'portrait');

        return $pdf->stream('comprobante_' . $factura->num_comprobante . '.pdf');
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
            if (isset($comprobanteData->transportadora)) {
                $comprobante->transportadora = $comprobanteData->transportadora;
            }
            $comprobante->save();

            // Update lines
            foreach ($comprobanteData->lineas as $lineaData) {
                $linea = LineaComprobante::findOrFail($lineaData->id);
                $cantAnterior = $linea->cantidad;

                $linea->cantidad = $lineaData->cantidad;
                $linea->valor_unitario = $lineaData->valor_unitario;
                $linea->subtotal = $lineaData->subtotal;
                $linea->valor_total = $lineaData->valor_total;
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
        //
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
        $comprobante->delete();
        return response(['success' => true]);
    }
}

