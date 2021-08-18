<?php

namespace App\Http\Controllers;

use App\Ordentrabajo;
use App\CostoProduccion;
use App\Detalletrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdentrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador=$request->operador;
        
        if ($criterio=='' || $buscar==''){
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
            ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(30);
        }
        else{
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
            ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->where($criterio,$operador,$buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(30);
        }
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function cartera(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador=$request->operador;
        
        if ($criterio=='' || $buscar==''){
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
            ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->where('ordentrabajos.saldo', '>', 0)->whereNotIn('ordentrabajos.estado',  ['C','PC','PA','P','AN'])->orderBy('ordentrabajos.fecha', 'desc')->paginate(50);
        }
        else{
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
            ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->where($criterio,$operador,$buscar)->where('ordentrabajos.saldo', '>', 0)->whereNotIn('ordentrabajos.estado', ['C','PC','PA','P','AN'])->orderBy('ordentrabajos.fecha', 'ASC')->paginate(30);
        }
        foreach($ordenes as $orden){
            $costos=CostoProduccion::select(DB::raw('SUM(total) as totalcosto'))->where('idorden','=', $orden->idorden)->get();
            $orden->costos+=$costos[0]->totalcosto;
        }
        
        return [
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function filtrarCartera(Request $request)
    {
        if($request->buscar=='todos'){
            $buscar=['D','A','EP','ENP','E'];
        }else{
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
        ->whereIn('ordentrabajos.produccion', $buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(20);
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'buscar'=>$buscar,
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function ventas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $operador=$request->operador;
        $fechai = Carbon::now()->startOfMonth()->toDateString();
        $fechaf = Carbon::now()->endOfMonth()->toDateString();
        if ($criterio=='' || $buscar==''){
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
            ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->whereBetween('ordentrabajos.fecha', [$fechai, $fechaf])->whereNotIn('ordentrabajos.estado',  ['C','PC','PA','P','AN'])->orderBy('ordentrabajos.fecha', 'desc')->paginate(50);
        }
        else{
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
            ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->where($criterio,$operador,$buscar)->whereBetween('ordentrabajos.fecha', [$fechai, $fechaf])->whereNotIn('ordentrabajos.estado', ['C','PC','PA','P','AN'])->orderBy('ordentrabajos.fecha', 'ASC')->paginate(30);
        }
        foreach($ordenes as $orden){
            $costos=CostoProduccion::select(DB::raw('SUM(total) as totalcosto'))->where('idorden','=', $orden->idorden)->get();
            $orden->costos+=$costos[0]->totalcosto;
        }
        
        return [
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function filtrarFechaVentas(Request $request)
    {
        $filtroFecha = $request->filtroFecha;
        switch($filtroFecha){
            case 'hoy':
                $fechai=date('Y-m-d');
                $fechaf=date('Y-m-d');
            break;
            case 'ayer':
                $date=date('Y-m-d');
                $mod_date = strtotime($date."- 1 days");
                $fechai= date('Y-m-d',$mod_date);
                $fechaf=date('Y-m-d',$mod_date);
                break;
            case 'ultimos7':
                $date=date('Y-m-d');
                $mod_date = strtotime($date."- 7 days");
                $fechai= date('Y-m-d',$mod_date);
                $fechaf=date('Y-m-d');
                break;
            case 'ultimos30':
                $date=date('Y-m-d');
                $mod_date = strtotime($date."- 30 days");
                $fechai= date('Y-m-d',$mod_date);
                $fechaf=date('Y-m-d');
                break;
            case 'mes':
                $diaSemana = date("m");
                # Calcular el tiempo (no la fecha) de cuándo fue el inicio de semana
                $tiempoDeInicioDeSemana = strtotime("-" . $diaSemana . " days"); # Restamos -X days
                # Y formateamos ese tiempo
                $fechai= date("Y-m-d", $tiempoDeInicioDeSemana);
                # Ahora para el fin, sumamos
                $tiempoDeFinDeSemana = strtotime("+" . $diaSemana . " days", $tiempoDeInicioDeSemana); # Sumamos +X days, pero partiendo del tiempo de inicio
                # Y formateamos
                $fechaf = date("Y-m-d", $tiempoDeFinDeSemana);
                break;
            case 'semana':
                $diaSemana = date("w");
                # Calcular el tiempo (no la fecha) de cuándo fue el inicio de semana
                $tiempoDeInicioDeSemana = strtotime("-" . $diaSemana . " days"); # Restamos -X days
                # Y formateamos ese tiempo
                $fechai= date("Y-m-d", $tiempoDeInicioDeSemana);
                # Ahora para el fin, sumamos
                $tiempoDeFinDeSemana = strtotime("+" . $diaSemana . " days", $tiempoDeInicioDeSemana); # Sumamos +X days, pero partiendo del tiempo de inicio
                # Y formateamos
                $fechaf = date("Y-m-d", $tiempoDeFinDeSemana);
                break;
                default:
                $intervalo = explode(",", $filtroFecha);
                $fechai=$intervalo[0];
                $fechaf=$intervalo[1];;
        }
        $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
        ->whereBetween('ordentrabajos.created_at', [$fechai, $fechaf])->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(10);
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function filtrarVentas(Request $request)
    {
        if($request->buscar=='todos'){
            $buscar=['D','A','EP','ENP','E'];
        }else{
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
        ->whereIn('ordentrabajos.produccion', $buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(20);
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'buscar'=>$buscar,
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    
    public function filtrarFecha(Request $request)
    {
        $filtroFecha = $request->filtroFecha;
        switch($filtroFecha){
            case 'hoy':
                $fechai=date('Y-m-d');
                $fechaf=date('Y-m-d');
            break;
            case 'ayer':
                $date=date('Y-m-d');
                $mod_date = strtotime($date."- 1 days");
                $fechai= date('Y-m-d',$mod_date);
                $fechaf=date('Y-m-d',$mod_date);
                break;
            case 'ultimos7':
                $date=date('Y-m-d');
                $mod_date = strtotime($date."- 7 days");
                $fechai= date('Y-m-d',$mod_date);
                $fechaf=date('Y-m-d');
                break;
            case 'ultimos30':
                $date=date('Y-m-d');
                $mod_date = strtotime($date."- 30 days");
                $fechai= date('Y-m-d',$mod_date);
                $fechaf=date('Y-m-d');
                break;
            case 'mes':
                $diaSemana = date("m");
                # Calcular el tiempo (no la fecha) de cuándo fue el inicio de semana
                $tiempoDeInicioDeSemana = strtotime("-" . $diaSemana . " days"); # Restamos -X days
                # Y formateamos ese tiempo
                $fechai= date("Y-m-d", $tiempoDeInicioDeSemana);
                # Ahora para el fin, sumamos
                $tiempoDeFinDeSemana = strtotime("+" . $diaSemana . " days", $tiempoDeInicioDeSemana); # Sumamos +X days, pero partiendo del tiempo de inicio
                # Y formateamos
                $fechaf = date("Y-m-d", $tiempoDeFinDeSemana);
                break;
            case 'semana':
                $diaSemana = date("w");
                # Calcular el tiempo (no la fecha) de cuándo fue el inicio de semana
                $tiempoDeInicioDeSemana = strtotime("-" . $diaSemana . " days"); # Restamos -X days
                # Y formateamos ese tiempo
                $fechai= date("Y-m-d", $tiempoDeInicioDeSemana);
                # Ahora para el fin, sumamos
                $tiempoDeFinDeSemana = strtotime("+" . $diaSemana . " days", $tiempoDeInicioDeSemana); # Sumamos +X days, pero partiendo del tiempo de inicio
                # Y formateamos
                $fechaf = date("Y-m-d", $tiempoDeFinDeSemana);
                break;
                default:
                $intervalo = explode(",", $filtroFecha);
                $fechai=$intervalo[0];
                $fechaf=$intervalo[1];;
        }
        $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
        ->whereBetween('ordentrabajos.created_at', [$fechai, $fechaf])->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(10);
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function filtrarOrdenes(Request $request)
    {   
        if($request->buscar=='todos'){
            $buscar=['D','A','EP','ENP','E'];
        }else{
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
        ->whereIn('ordentrabajos.produccion', $buscar)->orderBy('ordentrabajos.fecha_entrega', 'ASC')->paginate(50);
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    public function filtrarEstadoc(Request $request)
    {
        if($request->buscar=='todos'){
            $buscar=['C','PC','PA','VC','P','A'];
        }else{
            $buscar = [$request->buscar];
        }
        $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')
        ->join('articulos','ordentrabajos.idarticulo','=','articulos.id')->select('*','articulos.nombre as articulo','personas.nombre as rasonsocial','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
        ->whereIn('ordentrabajos.estado', $buscar)->orderBy('ordentrabajos.created_at', 'ASC')->paginate(20);
        foreach($ordenes as $orden){
            $detalles=Detalletrabajo::where('idorden','=', $orden['idorden'])->select('*','detalletrabajos.titulo as titulo_detalle','detalletrabajos.descripcion as descripcion_detalle','detalletrabajos.valor as valor_detalle')->get();
            $orden->detalles=$detalles;
            $costos=CostoProduccion::join('costois','costos.idcostois','=','costois.id')->where('idorden','=', $orden->idorden)->select('*','costos.titulo as titulo_costo','costos.idcostois as id_insumo','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','costos.id as idcosto','costos.valor as valor_costo','costos.orden as orden_costo','costos.cantidad as cantidad_costo','costos.total as subtotal_costo')->get();
            $orden->costos=$costos;
        }
        
        return [
            'buscar'=>$buscar,
            'pagination' => [
                'total'        => $ordenes->total(),
                'current_page' => $ordenes->currentPage(),
                'per_page'     => $ordenes->perPage(),
                'last_page'    => $ordenes->lastPage(),
                'from'         => $ordenes->firstItem(),
                'to'           => $ordenes->lastItem(),
            ],
            'ordenes' => $ordenes
        ];
    }
    
    public function procesos(Request $request)
    {
        $buscar = json_decode($request->buscar);
        $operador=$request->operador;
        $procesos=array();
        for($i=0; $i<count($buscar);$i++){
            
            $ordenes = Ordentrabajo::join('clientes','ordentrabajos.idcliente','=','clientes.id')->join('personas','clientes.id','=','personas.id')->join('costos','ordentrabajos.id','=','costos.idorden')->join('costois','costos.idcostois','=','costois.id')->join('articulos','ordentrabajos.idarticulo','=','articulos.id')
            ->select('*','articulos.nombre as articulo','personas.nombre as cliente','costos.id as idcosto','costos.descripcion as descripcion_costo','costois.nombre as nombre_insumo','ordentrabajos.created_at as fechaorden','ordentrabajos.updated_at as updateorden','ordentrabajos.id as idorden','ordentrabajos.estado as estadoc','ordentrabajos.produccion as estadop')
            ->whereNotIn('costos.terminado',[1, true])->whereIn('ordentrabajos.produccion', ['EP','ENP'])->where('costos.titulo',$buscar[$i])->orderBy('ordentrabajos.id', 'desc')->get();
            array_push($procesos, $ordenes );
        }
        return ['procesos'=>$procesos[0],'buscar'=>$buscar];
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   
    public function store(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

            try{
                DB::beginTransaction();
                $mytime= Carbon::now('America/Bogota');
                $orden = new Ordentrabajo();
                $orden->idcliente = $request->id_cliente;
                $orden->idarticulo = $request->id_articulo;
                $orden->fecha_entrega = $request->fecha_entrega;
                $orden->carpeta_cliente = $request->carpeta_cliente;
                $orden->detalles_diseno = $request->detalles_diseno;
                $orden->fecha = $request->fecha;
                $orden->created_at=$request->fechaorden;
                $orden->observaciones = $request->observaciones;
                $orden->unidad_medida = $request->unidad_medida;
                $orden->ancho_material = $request->ancho_material;
                $orden->largo_material = $request->largo_material;
                $orden->cantidad = $request->cantidad;
                $orden->descuento = $request->descuento;
                $orden->impuesto = $request->impuesto;
                $orden->totalParcial = $request->totalParcial;
                $orden->total = $request->total;
                $orden->abono = $request->abono;
                $orden->saldo = $request->saldo;
                $orden->estado = $request->estadoc;
                $orden->produccion = $request->estadop;
                $result= $orden->save();
                //Array de detalles
                //Recorro todos los elementos
                $detalles = json_decode($request->detalles);
                $costos=json_decode($request->costos);
                // echo $costos;
              
                foreach($detalles as $det)
                {
                    $detalle = new Detalletrabajo();
                    $detalle->idorden = $orden->id;
                    $detalle->titulo = $det->titulo_detalle;
                    $detalle->valor = $det->valor_detalle;
                    $detalle->descripcion = $det->descripcion_detalle;          
                    $detalle->save();
                } 
                
                foreach($costos as $cos)
                {
                    
                    $costo=new CostoProduccion();
                    $costo->idorden=$orden->id;
                    $costo->idcostois=$cos->id_insumo;
                    $costo->titulo=$cos->tipo_costo;
                    $costo->descripcion=$cos->descripcion_costo;
                    $costo->cantidad=$cos->cantidad_costo;
                    $costo->orden=$cos->orden_costo;
                    $costo->completado=0;
                    $costo->fecha_termina=date('Y-m-d');
                    $costo->terminado=0;
                    $costo->valor=$cos->valor_costo;
                    $costo->total=$cos->subtotal_costo;
                    $costo->save();
                }         

                DB::commit();
            } catch (Exception $e){
                DB::rollBack();
            }
    }
    public function update(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

            try{
                DB::beginTransaction();
                $mytime= Carbon::now('America/Bogota');
                $orden = Ordentrabajo::find($request->id);
                $orden->idcliente = $request->id_cliente;
                $orden->idarticulo = $request->id_articulo;
                $orden->fecha_entrega = $request->fecha_entrega;
                $orden->carpeta_cliente = $request->carpeta_cliente;
                $orden->detalles_diseno = $request->detalles_diseno;
                $orden->fecha = $request->fecha;
                $orden->created_at=$request->fechaorden;
                $orden->observaciones = $request->observaciones;
                $orden->unidad_medida = $request->unidad_medida;
                $orden->ancho_material = $request->ancho_material;
                $orden->largo_material = $request->largo_material;
                $orden->cantidad = $request->cantidad;
                $orden->descuento = $request->descuento;
                $orden->impuesto = $request->impuesto;
                $orden->totalParcial = $request->totalParcial;
                $orden->total = $request->total;
                $orden->abono = $request->abono;
                $orden->saldo = $request->saldo;
                $orden->estado = $request->estadoc;
                $orden->produccion = $request->estadop;
                $result= $orden->save();
                //Array de detalles
                //Recorro todos los elementos
                $detalles = json_decode($request->detalles);
                $costos=json_decode($request->costos);
                // echo $costos;
                foreach($detalles as $det)
                {
                    if($det->id==0){
                        $detalle = new Detalletrabajo();
                        $detalle->idorden = $orden->id;
                    }else{
                        $detalle = Detalletrabajo::findOrFail($det->id);
                    }
                    $detalle->titulo = $det->titulo_detalle;
                    $detalle->valor = $det->valor_detalle;
                    $detalle->descripcion = $det->descripcion_detalle;          
                    $detalle->save();
                } 
                foreach($costos as $cos)
                {
                    if(!isset($cos->idcosto) || $cos->idcosto==0){
                        $costo=new CostoProduccion();
                        $costo->idorden=$orden->id;
                    }else{
                        $costo = CostoProduccion::findOrFail($cos->idcosto);
                    }
                    $costo->idcostois=$cos->id_insumo;
                    $costo->titulo=$cos->tipo_costo;
                    $costo->descripcion=$cos->descripcion_costo;
                    $costo->cantidad=$cos->cantidad_costo;
                    $costo->orden=$cos->orden_costo;
                    $costo->completado=0;
                    $costo->fecha_termina=date('Y-m-d');
                    $costo->terminado=0;
                    $costo->valor=$cos->valor_costo;
                    $costo->total=$cos->subtotal_costo;
                    $costo->save();
                }         

                DB::commit();
            } catch (Exception $e){
                DB::rollBack();
            }
    }
    public function cambiarFecha(Request $request){
        $orden = Ordentrabajo::find($request->id);
        $orden->fecha_entrega=$request->fecha_entrega;
        $orden->save();
        
    }
    public function cambiarEstado(Request $request){
        $orden = Ordentrabajo::find($request->id);
        $orden->estado=$request->estadoc;
        $orden->produccion=$request->estadop;
        $orden->save();
        
    }
    public function cambiarAbono(Request $request){
        $orden = Ordentrabajo::find($request->id);
        $orden->abono=$request->abono;
        $orden->saldo=$request->saldo;
        $orden->save();
        
    }
    public function cambiarProceso(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

            try{
                $id=$request->id;
                $columna=$request->columna;
                $dato=$request->dato;
                $costo = CostoProduccion::findOrFail($id);
                switch($columna){
                    case 'completado':
                        $costo->completado=$dato;
                    break;
                    case 'fechaterminado':
                        $costo->fecha_termina=$dato;
                    break;
                    case 'terminado':
                        $costo->terminado=$dato;
                        $costo->completado=1;
                    break;
                }
                $costo->save();

            } catch (Exception $e){
            }
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Ordentrabajo  $ordentrabajo
     * @return \Illuminate\Http\Response
     */
    public function duplicar(Request $request){
        $id=$request->id;
        $orden = Ordentrabajo::find($id);
        $newOrden = $orden->replicate();
        $newOrden->created_at = Carbon::now();
        $newOrden->save();
        $detalles=Detalletrabajo::where('idorden','=',$orden->id)->get();
        $costos=Costoproduccion::where('idorden','=',$orden->id)->get();
        foreach($detalles as $det){
            $detalle = new Detalletrabajo();
            $detalle->titulo = $det->titulo;
            $detalle->valor = $det->valor;
            $detalle->descripcion = $det->descripcion;          
            $detalle->idorden = $newOrden->id;
            $detalle->save();
        }
        foreach($costos as $cos){
            $costo=new CostoProduccion();
            $costo->idcostois=$cos->idcostois;
            $costo->titulo=$cos->titulo;
            $costo->descripcion=$cos->descripcion;
            $costo->cantidad=$cos->cantidad;
            $costo->orden=$cos->orden;
            $costo->completado=0;
            $costo->fecha_termina=date('Y-m-d');
            $costo->terminado=0;
            $costo->valor=$cos->valor;
            $costo->total=$cos->total;
            $costo->idorden = $newOrden->id;
            $costo->save();
        }
    }

     public function show(Ordentrabajo $ordentrabajo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ordentrabajo  $ordentrabajo
     * @return \Illuminate\Http\Response
     */
    public function edit(Ordentrabajo $ordentrabajo)
    {
        //
    }

   
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ordentrabajo  $ordentrabajo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id=$request->id;
        $orden = Ordentrabajo::find($id);
        $orden->delete();
    }
}
