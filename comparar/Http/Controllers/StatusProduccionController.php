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
                          $q->whereIn('estado', ['0', '1']);
                      });
            })
            ->get();

        foreach ($ordenes as $orden) {
            $orden->detalles;
            $costos = CostoProduccion::join('costois', 'costos.costois_id', '=', 'costois.id')
                ->where('ordentrabajo_id', '=', $orden->id)
                ->select('*', 'costos.titulo as titulo_costo', 'costos.costois_id as id_insumo', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'costos.id as idcosto', 'costos.valor as valor_costo', 'costos.orden as orden_costo', 'costos.cantidad as cantidad_costo', 'costos.total as subtotal_costo')
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
            if ($orden->linea) {
                $orden->pedido = Comprobante::find($orden->linea->comprobante_id);
            }

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
                // Return to "En Produccion" if not in terminal/special states
                if ($orden->produccion !== 'ENP') {
                    $orden->produccion = 'ENP';
                    $orden->save();
                }
            }

            // If moving away from 'Terminado', finalize the CuentaPorPagar
            if ($estadoAnterior === 'Terminado' && $request->estado !== 'Terminado') {
                $costoTerminado = CostoProduccion::where('ordentrabajo_id', $orden->id)
                    ->where('titulo', 'Terminado')
                    ->first();
                if ($costoTerminado) {
                    $cantidadFinal = (int)($request->cantidad_final ?: $orden->cantidad);
                    if ($cantidadFinal > 0) {
                        $costoTerminado->cantidad = $cantidadFinal;
                        $costoTerminado->total = $cantidadFinal * $costoTerminado->valor;
                        $costoTerminado->save();
                    }

                    $cuenta = \App\CuentaPorPagar::where('costo_id', $costoTerminado->id)->first();
                    if ($cuenta && $cuenta->estado === 'En Espera') {
                        $cuenta->cantidad = $costoTerminado->cantidad;
                        $cuenta->monto = $costoTerminado->total;
                        
                        $totalAbonos = $cuenta->abonos()->sum('monto');
                        $cuenta->saldo = $cuenta->monto - $totalAbonos;
                        if ($cuenta->saldo <= 0) {
                            $cuenta->estado = 'Pagado';
                        } elseif ($totalAbonos > 0) {
                            $cuenta->estado = 'Abonado';
                        } else {
                            $cuenta->estado = 'Pendiente';
                        }
                        $cuenta->save();
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

        // Extract cabida from plancha details and update order cabida
        $plancha = InventariosMateriaPrima::find($request->id);
        if ($plancha && $plancha->detalles) {
            preg_match('/[\d.]+/', $plancha->detalles, $matches);
            $numero = isset($matches[0]) ? (float)$matches[0] : 0;
            if ($numero > 0) {
                $orden->cabida = $numero;
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
                        // $costois=Costois::find($costo->costois_id);
                        // if(empty($costois)){
                        //     $cos=CostoProduccion::find($costo->id);
                        //     $cos->delete();
                        // }else{
                        $detalle->costos_id = $costo->id;
                        $detalle->save();
                        // }

                    } else {
                    }
                }
            }

        }
    }
    public function guardarMaterial(Request $request)
    {
        $orden = Ordentrabajo::find($request->id);
        $orden->tamano = (float) $request->tamano;
        $orden->medida_final = $request->medida_final;
        $orden->medida_material = $request->medida_material;
        $orden->carpeta_cliente = $request->carpeta_cliente;
        $orden->cabida = $request->cabida;
        $orden->observaciones = $request->observaciones;
        $orden->save();
        $detalle = json_decode($request->detalle);
        $costo = CostoProduccion::find($detalle->costos_id);
        $costo->cantidad = $detalle->costo->cantidad;
        $costo->descripcion = $detalle->costo->descripcion;
        $costo->save();

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
        foreach ($detalles as $det) {
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
                    $costo = CostoProduccion::findOrFail($det->costos_id);
                    $costo->costois;
                    $cambiosCostos .= ($costo->cantidad != $det->costo->cantidad) ? 'Costo ' . $costo->titulo . '-> Cantidad: ' . $det->costo->cantidad . ', ' : '';
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
            if ($det->id == 0) {

                $detalle = new Detalletrabajo();
                $detalle->ordentrabajo_id = $id;
                $detalleNuevo .= 'Titulo: ' . $det->titulo . ', Valor: ' . $valor . ', Descripcion: ' . $det->descripcion;
            } else {
                $detalle = Detalletrabajo::find($det->id);
                $cambiosDetalles .= ($detalle->descripcion != $det->descripcion) ? 'Detalle ' . $det->titulo . '-> Descripcion: ' . $det->descripcion . ', ' : '';
                // $cambiosDetalles.=($detalle->valor!=$det->valor) ? 'Detalle '.$det->titulo.'-> Valor: '.$det->valor.', ' : '';
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
        return $detalles;

    }
    public function guardarOpciones(Request $request)
    {
        $costos = json_decode($request->costo);
        $detalles = json_decode($request->detalles);

        $detco = self::crearCostosDetalles($detalles, $costos, $request->id, '', 1);
        return $detco;


    }
    public function asignarActivo(Request $request)
    {
        $activo = json_decode($request->activo);
        $verificarCosto = CostoProduccion::where('titulo', $activo->tipo)->where('ordentrabajo_id', $request->ordenid)->get();
        if (count($verificarCosto) == 0) {
            $costo = new CostoProduccion();
        } else {
            $costo = CostoProduccion::find($verificarCosto[0]->id);
        }
        $costo->titulo = $activo->tipo;
        $costo->descripcion = 0;
        $costo->costois_id = $activo->id;
        $costo->ordentrabajo_id = $request->ordenid;
        $costo->save();
        return $costo;


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
            $costoTerminado = CostoProduccion::where('ordentrabajo_id', $ord->id)
                ->where('titulo', 'Terminado')
                ->first();
            if ($costoTerminado) {
                $costoTerminado->cantidad = $ord->cantidad;
                $costoTerminado->total = $ord->cantidad * $costoTerminado->valor;
                $costoTerminado->save();

                $cuenta = \App\CuentaPorPagar::where('costo_id', $costoTerminado->id)->first();
                if ($cuenta) {
                    $cuenta->cantidad = $ord->cantidad;
                    $cuenta->monto = $costoTerminado->total;
                    
                    $totalAbonos = $cuenta->abonos()->sum('monto');
                    $cuenta->saldo = $cuenta->monto - $totalAbonos;
                    if ($cuenta->estado === 'En Espera') {
                        $cuenta->estado = 'Pendiente';
                    } else {
                        if ($cuenta->saldo <= 0) {
                            $cuenta->estado = 'Pagado';
                        } elseif ($totalAbonos > 0) {
                            $cuenta->estado = 'Abonado';
                        } else {
                            $cuenta->estado = 'Pendiente';
                        }
                    }
                    $cuenta->save();
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
            foreach ($ordenes as $orden) {
                $statu = $orden['status'];
                $status = statusProduccion::find($statu['id']);
                $status->prioridad = $statu['prioridad'];
                $status->save();
            }
        } else {
            $orden = $request->orden;
            $statu = $orden['status'];
            $status = statusProduccion::find($statu['id']);
            $status->prioridad = $statu['prioridad'];
            $status->save();
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

        if ($cuenta) {
            $cuenta->cantidad = $orden->cantidad;
            $cuenta->valor_unitario = $request->valor;
            $cuenta->monto = $costo->total;
            
            // Adjust balance (saldo) based on abonos already registered
            $totalAbonos = $cuenta->abonos()->sum('monto');
            $cuenta->saldo = $costo->total - $totalAbonos;

            if ($cuenta->saldo <= 0) {
                $cuenta->estado = 'Pagado';
            } elseif ($totalAbonos > 0) {
                $cuenta->estado = 'Abonado';
            } else {
                $cuenta->estado = $isTerminado ? 'En Espera' : 'Pendiente';
            }
            $cuenta->save();
        } else {
            $cuenta = new \App\CuentaPorPagar();
            $cuenta->activo_id = $costo->costois_id;
            $cuenta->ordentrabajo_id = $orden->id;
            $cuenta->costo_id = $costo->id;
            $cuenta->descripcion = "Terminado Orden #" . $orden->id . " (" . $nombreActivo . ")";
            $cuenta->cantidad = $orden->cantidad;
            $cuenta->valor_unitario = $request->valor;
            $cuenta->monto = $costo->total;
            $cuenta->saldo = $costo->total;
            $cuenta->estado = $isTerminado ? 'En Espera' : 'Pendiente';
            $cuenta->fecha = date('Y-m-d');
            $cuenta->save();
        }

        return response()->json([
            'success' => true, 
            'costo' => $costo,
            'cuenta' => $cuenta
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

