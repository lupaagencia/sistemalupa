<?php

namespace App\Http\Controllers;

use App\Costois;
use App\CostoArticulo;
use App\OpcionAtributo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CostoisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $costosp = Costois::leftJoin('proveedores', 'costois.idproveedor', '=', 'proveedores.id')
                ->select(
                    'costois.id',
                    'costois.idproveedor',
                    'costois.tipo_costo',
                    'costois.valor',
                    'costois.estado',
                    'costois.unidad_medida as cabida',
                    'costois.updated_at',
                    'costois.nombre',
                    'costois.descripcion',
                    'proveedores.contacto',
                    'proveedores.telefono_contacto',
                    'proveedores.nombre as nombre_proveedor'
                )
                ->where('costois.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('costois.id', 'desc')->paginate(100);
        }
        else{
            $costosp = Costois::leftJoin('proveedores', 'costois.idproveedor', '=', 'proveedores.id')
                ->select(
                    'costois.id',
                    'costois.idproveedor',
                    'costois.tipo_costo',
                    'costois.valor',
                    'costois.estado',
                    'costois.unidad_medida as cabida',
                    'costois.updated_at',
                    'costois.nombre',
                    'costois.descripcion',
                    'proveedores.contacto',
                    'proveedores.telefono_contacto',
                    'proveedores.nombre as nombre_proveedor'
                )
                ->where('costois.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('costois.id', 'desc')->paginate(100);
        }
        

        return [  
            'pagination' => [
                'total'        => $costosp->total(),
                'current_page' => $costosp->currentPage(),
                'per_page'     => $costosp->perPage(),
                'last_page'    => $costosp->lastPage(),
                'from'         => $costosp->firstItem(),
                'to'           => $costosp->lastItem(),
            ],
            'costop' => $costosp 
        ];
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
        if(!$request->ajax()) return redirect('/');
        $costop = new Costois();
        $costop->idproveedor = $request->idproveedor;
        $costop->idpersona = $request->idproveedor;
        $costop->tipo_costo = $request->tipo_costo;
        $costop->nombre = $request->nombre;
        $costop->descripcion = $request->descripcion;
        $costop->unidad_medida = $request->unidad;
        $costop->valor = $request->valor;
        $costop->total = 0;
        $costop->estado=1;
        $costop->save();
    }
    public function agruparTipos(){
        //if (!$request->ajax()) return redirect('/');
        $costois=Costois::select('tipo_costo', DB::raw('count(*) as tipo'))->groupBy('tipo_costo')->get();
        return $costois;
        foreach($costois as $costo){
            $costo->proveedor;
        }
        // $costois = Costois::join('proveedores','costois.idproveedor','=','proveedores.id')->join('personas','costois.idpersona','=','personas.id')
        // ->select('costois.id','costois.idproveedor','costois.tipo_costo','costois.valor','costois.estado','costois.cabida','costois.updated_at','costois.nombre','proveedores.contacto','proveedores.telefono_contacto','personas.nombre as nombre_proveedor')
        // ->where('costois.nombre', 'like', '%'. $filtro . '%')
        // ->orWhere('costois.tipo_costo', 'like', '%'. $filtro . '%')
        // ->orderBy('costois.nombre', 'asc')->get();
        

        return ['insumos' => $costois];

    }
    public function selectInsumos(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $costois=Costois::where('costois.nombre', 'like', '%'. $filtro . '%')
        ->orWhere('costois.tipo_costo', 'like', '%'. $filtro . '%')
        ->orderBy('costois.nombre', 'asc')->get();
        foreach($costois as $costo){
            $costo->proveedor;
        }
        // $costois = Costois::join('proveedores','costois.idproveedor','=','proveedores.id')->join('personas','costois.idpersona','=','personas.id')
        // ->select('costois.id','costois.idproveedor','costois.tipo_costo','costois.valor','costois.estado','costois.cabida','costois.updated_at','costois.nombre','proveedores.contacto','proveedores.telefono_contacto','personas.nombre as nombre_proveedor')
        // ->where('costois.nombre', 'like', '%'. $filtro . '%')
        // ->orWhere('costois.tipo_costo', 'like', '%'. $filtro . '%')
        // ->orderBy('costois.nombre', 'asc')->get();
        

        return ['insumos' => $costois];

    }
    /**
     * Display the specified resource.
     *
     * @param  \App\costois  $costois
     * @return \Illuminate\Http\Response
     */
    public function show(costois $costois)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\costois  $costois
     * @return \Illuminate\Http\Response
     */
    public function edit(costois $costois)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\costois  $costois
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $costop = Costois::findOrFail($request->id);
        $costop->idproveedor = $request->idproveedor;
        $costop->idpersona = $request->idproveedor;
        $costop->tipo_costo = $request->tipo_costo;
        $costop->nombre = $request->nombre;
        $costop->descripcion = $request->descripcion;
        $costop->unidad_medida = $request->unidad;
        $costop->valor = $request->valor;
        $costop->save();
        $costoa=CostoArticulo::where('idcostois','=',$request->id)->get();
        foreach($costoa as $cos){
            $costoarti=CostoArticulo::findOrFail($cos->id);
            $costoarti->valor=$costop->valor/$cos->fraccion;
            $costoarti->valorfull=$costop->valor/$cos->fraccion;
            $costoarti->save();
            $opcion=OpcionAtributo::findOrFail($cos->idopcion);
            $costosOpcion=CostoArticulo::where('idopcion','=',$cos->idopcion)->get();
            $valoropcion=0;
            foreach($costosOpcion as $co){
                $valor=$co->valor*($co->rentabilidad/100+1);
                $valoropcion=$valoropcion+$valor;
            }
            $opcion->valor=$valoropcion;
            $opcion->save();
        }
        
    }
    public function delete(Request $request){
        $costop=Costois::where('id','=',$request->id)->delete();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\costois  $costois
     * @return \Illuminate\Http\Response
     */
    public function destroy(costois $costois)
    {
        //
    }
}

