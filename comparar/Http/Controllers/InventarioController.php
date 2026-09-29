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
use App\http\Controllers\OrdentrabajoController;

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
}
