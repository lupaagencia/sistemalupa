<?php

namespace App\Http\Controllers;
use App\InventariosMateriaPrima;
use App\MovimientoMateriaPrima;
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
use stdClass;
use App\Http\Controllers\OrdentrabajoController;

use phpDocumentor\Reflection\Types\Self_;

class InventarioController extends Controller
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
        
        $id=array();
        if ($buscar==''){
            $materias = InventariosMateriaPrima::select('*')->where('tipo','Materia Prima')
            ->orderBy('id', 'desc')->paginate(500);
            foreach ($materias as $material) {
                $material->costois;
                $material->cliente;
            }
            // $comprobantes = Comprobante::select()->orderBy('id', 'desc');
        }
        else{
           
            $materias = InventariosMateriaPrima::select('*')->where($criterio,'LIKE','%'.$buscar.'%')
            ->orderBy('id', 'desc')->paginate(500);
            foreach ($materias as $material) {
                $material->costois;
                $material->cliente;
                
            }
           
        }
        
        return [
            'pagination' => [
                'total'        => $materias->total(),
                'current_page' => $materias->currentPage(),
                'per_page'     => $materias->perPage(),
                'last_page'    => $materias->lastPage(),
                'from'         => $materias->firstItem(),
                'to'           => $materias->lastItem(),
            ],
            'materias' => $materias,
            
        ];
    }

    public function planchas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        $id=array();
        if ($criterio=='' || $buscar==''){
            $materias = InventariosMateriaPrima::select('*')->where('tipo','Plancha')
            ->orderBy('id', 'desc')->paginate(50);
            foreach ($materias as $material) {
                $material->cliente;
                
            }
            
            // $comprobantes = Comprobante::select()->orderBy('id', 'desc');
        }
        else{
            if($criterio='asignado_id'){
                $cliente=Cliente::where('razonsocial','LIKE','%'.$buscar.'%')->get();
                $buscar=$cliente[0]->id;
            }
           
            $materias = InventariosMateriaPrima::select('*')->where($criterio,$buscar)->where('tipo','Plancha')
            ->orderBy('id', 'desc')->paginate(50);
            foreach ($materias as $material) {
                $material->cliente;
                
            }
           
        }
        
        return [
            'pagination' => [
                'total'        => $materias->total(),
                'current_page' => $materias->currentPage(),
                'per_page'     => $materias->perPage(),
                'last_page'    => $materias->lastPage(),
                'from'         => $materias->firstItem(),
                'to'           => $materias->lastItem(),
            ],
            'materias' => $materias,
            
        ];
    }

    public function tipos()
    {
        $tipos = InventariosMateriaPrima::groupBy('tipo')->get();
        return $tipos;
    }
   
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cambiarEstado(Request $request){
        $comprobante = Comprobante::find($request->id);
        $comprobante->estado=$request->estado;
        $comprobante->save();
        $comprobante->lineas;
       
        
        $datosA=new stdClass();
        $datosA->user_id=$request->user_id;
        $datosA->actividad='Se cambio a estado del pedido a '.$request->estado.' comprobante #'.$comprobante->id;
        $actividad=new ActividadController();
        $actividad->store($datosA);
        return $comprobante;
    }
 
       
        public static function getNextDate($origin_date, $daysToAdd) {
            $date = new DateTime($origin_date);
            $date->modify('+' . $daysToAdd . ' days');
            return $date->format('Y-m-d');
        }
        
            
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        if($request->id==0){

            $materia = new InventariosMateriaPrima();
        }else{
            $materia=InventariosMateriaPrima::find($request->id);
        }
        $materia->referencia = $request->referencia;
        $materia->detalles = $request->detalles;
        $materia->tipo = 'Materia Prima';
        $materia->asignado_id = $request->asignado_id;
        $materia->costois_id = $request->costois_id;
        $materia->estado = $request->estado;
        $materia->uso = $request->uso;
        $materia->cantidad = $request->cantidad;
        $materia->cambio = $request->cambio;
        $materia->save();
    }
    public function nuevaPlancha(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        if($request->id==0){
            $materia = new InventariosMateriaPrima();
        }else{
            $materia=InventariosMateriaPrima::find($request->id);
        }
        $materia->referencia = $request->referencia;
        $materia->detalles = $request->detalles;
        $materia->tipo = 'Plancha';
        $materia->asignado_id = $request->asignado_id;
        $materia->costois_id = $request->costois_id;
        $materia->estado = $request->estado;
        $materia->uso = $request->uso;
        $materia->cantidad = $request->cantidad;
        $materia->cambio = 0;
        $materia->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\comprobate  $comprobate
     * @return \Illuminate\Http\Response
     */
    public function show(comprobate $comprobate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\comprobate  $comprobate
     * @return \Illuminate\Http\Response
     */
    public function edit(comprobate $comprobate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\comprobate  $comprobate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, comprobate $comprobate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\comprobate  $comprobate
     * @return \Illuminate\Http\Response
     */
    public function eliminar(Request $request)
    {
        $id=$request->id;
        $tipo=$request->tipo;
        if($tipo=='plancha'){
            $orden=Ordentrabajo::where('plancha',$id)->get();
            foreach ($orden as $or) {
                $or->plancha=0;
                $or->save();
            }
        }else{
            $movimiento=MovimientoMateriaPrima::where('inventarios_materia_prima_id', $id)->delete();
        }
        $plancha = InventariosMateriaPrima::find($id);
        $plancha->delete();
       
    }

    public function getValoracionInventario(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        $query = InventariosMateriaPrima::with('costois')
            ->where('tipo', 'Materia Prima');
            
        if (!empty($buscar)) {
            if ($criterio == 'referencia') {
                $query->where('referencia', 'LIKE', '%' . $buscar . '%');
            } elseif ($criterio == 'ubicacion') {
                $query->where('ubicacion', 'LIKE', '%' . $buscar . '%');
            }
        }
        
        $items = $query->get();
        
        $valoracionTotal = 0;
        $detallesValoracion = [];
        
        foreach ($items as $item) {
            $costoUnitario = (float)($item->costois->valor ?? 0);
            $cantidad = (float)($item->cantidad ?? 0);
            $totalVal = round($cantidad * $costoUnitario, 2);
            
            $valoracionTotal += $totalVal;
            
            $detallesValoracion[] = [
                'id' => $item->id,
                'referencia' => $item->referencia,
                'ubicacion' => $item->ubicacion,
                'cantidad' => $cantidad,
                'unidad' => $item->unidad ?? 'Und',
                'costo_unitario' => $costoUnitario,
                'valoracion' => $totalVal
            ];
        }
        
        // Sort descending by valuation
        usort($detallesValoracion, function ($a, $b) {
            return $b['valoracion'] <=> $a['valoracion'];
        });
        
        return response()->json([
            'valoracion_total' => $valoracionTotal,
            'items_count' => count($detallesValoracion),
            'detalles' => $detallesValoracion
        ]);
    }

    public function getMovimientosKardex(Request $request)
    {
        $buscar = $request->buscar;
        $tipo = $request->tipo;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        
        $query = MovimientoMateriaPrima::with(['inventario.costois', 'comprobante']);
        
        if (!empty($buscar)) {
            $query->whereHas('inventario', function ($q) use ($buscar) {
                $q->where('referencia', 'LIKE', '%' . $buscar . '%');
            });
        }
        
        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }
        
        if (!empty($fechaInicio)) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }
        
        if (!empty($fechaFin)) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }
        
        $movimientos = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return response()->json([
            'pagination' => [
                'total'        => $movimientos->total(),
                'current_page' => $movimientos->currentPage(),
                'per_page'     => $movimientos->perPage(),
                'last_page'    => $movimientos->lastPage(),
                'from'         => $movimientos->firstItem(),
                'to'           => $movimientos->lastItem(),
            ],
            'movimientos' => $movimientos->items()
        ]);
    }

    public function getDashboardCostos(Request $request)
    {
        // 1. Current stock inventory valuation
        $items = InventariosMateriaPrima::with('costois')
            ->where('tipo', 'Materia Prima')
            ->get();
            
        $valoracionTotal = 0;
        foreach ($items as $item) {
            $costoUnitario = (float)($item->costois->valor ?? 0);
            $cantidad = (float)($item->cantidad ?? 0);
            $valoracionTotal += ($cantidad * $costoUnitario);
        }
        
        // 2. COGS (Consumo) Current Month vs Previous Month
        $cMonth = Carbon::now()->month;
        $cYear = Carbon::now()->year;
        
        $pMonth = Carbon::now()->subMonth()->month;
        $pYear = Carbon::now()->subMonth()->year;
        
        $consumoMesActual = (float)MovimientoMateriaPrima::where('tipo', 'salida')
            ->whereMonth('created_at', $cMonth)
            ->whereYear('created_at', $cYear)
            ->sum('costo_total');
            
        $consumoMesAnterior = (float)MovimientoMateriaPrima::where('tipo', 'salida')
            ->whereMonth('created_at', $pMonth)
            ->whereYear('created_at', $pYear)
            ->sum('costo_total');
            
        $variacionConsumo = 0;
        if ($consumoMesAnterior > 0) {
            $variacionConsumo = (($consumoMesActual - $consumoMesAnterior) / $consumoMesAnterior) * 100;
        }
        
        // 3. Historical consumption for the last 6 months
        $historicoConsumo = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthNum = $date->month;
            $yearNum = $date->year;
            $monthName = $date->format('M');
            
            $meses = [
                'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
                'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
            ];
            $monthNameEs = $meses[$monthName] ?? $monthName;
            
            $totalCosto = (float)MovimientoMateriaPrima::where('tipo', 'salida')
                ->whereMonth('created_at', $monthNum)
                ->whereYear('created_at', $yearNum)
                ->sum('costo_total');
                
            $historicoConsumo[] = [
                'mes' => $monthNameEs . ' ' . $date->format('y'),
                'total' => $totalCosto
            ];
        }
        
        // 4. Breakdown by Raw Material Category (share of total valuation)
        $topInsumos = [];
        $materialesAgrupados = [];
        foreach ($items as $item) {
            $costoUnitario = (float)($item->costois->valor ?? 0);
            $cantidad = (float)($item->cantidad ?? 0);
            $totalVal = $cantidad * $costoUnitario;
            
            if ($totalVal > 0) {
                if (!isset($materialesAgrupados[$item->referencia])) {
                    $materialesAgrupados[$item->referencia] = 0;
                }
                $materialesAgrupados[$item->referencia] += $totalVal;
            }
        }
        
        arsort($materialesAgrupados);
        $grandTotalVal = $valoracionTotal > 0 ? $valoracionTotal : 1;
        
        foreach (array_slice($materialesAgrupados, 0, 5, true) as $nombre => $val) {
            $topInsumos[] = [
                'nombre' => $nombre,
                'valoracion' => round($val, 2),
                'porcentaje' => round(($val / $grandTotalVal) * 100, 1)
            ];
        }
        
        // 5. Total Inputs vs Outputs this month
        $entradasMes = (float)MovimientoMateriaPrima::where('tipo', 'entrada')
            ->whereMonth('created_at', $cMonth)
            ->whereYear('created_at', $cYear)
            ->sum('costo_total');
            
        $salidasMes = (float)MovimientoMateriaPrima::where('tipo', 'salida')
            ->whereMonth('created_at', $cMonth)
            ->whereYear('created_at', $cYear)
            ->sum('costo_total');
            
        return response()->json([
            'valoracion_inventario' => round($valoracionTotal, 2),
            'cogs_mes_actual' => round($consumoMesActual, 2),
            'cogs_mes_anterior' => round($consumoMesAnterior, 2),
            'variacion_consumo' => round($variacionConsumo, 1),
            'entradas_mes' => round($entradasMes, 2),
            'salidas_mes' => round($salidasMes, 2),
            'historico_consumo' => $historicoConsumo,
            'top_insumos' => $topInsumos
        ]);
    }
}
