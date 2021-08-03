<?php

namespace App\Http\Controllers;

use App\Costois;
use Illuminate\Http\Request;
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
            $costosp = Costois::join('proveedores','costois.idproveedor','=','proveedores.id')->join('personas','costois.idpersona','=','personas.id')
            ->select('costois.id','costois.idproveedor','costois.tipo_costo','costois.valor','costois.estado','costois.unidad_medida','costois.updated_at','costois.nombre','proveedores.contacto','proveedores.telefono_contacto','personas.nombre as nombre_proveedor')
            ->where('costois.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('costois.id', 'desc')->paginate(20);
        }
        else{
           $costosp = Costois::join('proveedores','costois.idproveedor','=','proveedores.id')->join('personas','costois.idpersona','=','personas.id')
           ->select('costois.id','costois.idproveedor','costois.tipo_costo','costois.valor','costois.estado','costois.unidad_medida','costois.updated_at','costois.nombre','proveedores.contacto','proveedores.telefono_contacto','personas.nombre as nombre_proveedor')
           ->where('costois.'.$criterio, 'like', '%'. $buscar . '%')
           ->orderBy('costois.id', 'desc')->paginate(20);
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
        $costop->unidad_medida = $request->unidad;
        $costop->valor = $request->valor;
        $costop->total = 0;
        $costop->estado=1;
        $costop->save();
    }
    public function selectInsumos(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $costois = Costois::join('proveedores','costois.idproveedor','=','proveedores.id')->join('personas','costois.idpersona','=','personas.id')
        ->select('costois.id','costois.idproveedor','costois.tipo_costo','costois.valor','costois.estado','costois.unidad_medida','costois.updated_at','costois.nombre','proveedores.contacto','proveedores.telefono_contacto','personas.nombre as nombre_proveedor')
        ->where('costois.nombre', 'like', '%'. $filtro . '%')
        ->orWhere('costois.tipo_costo', 'like', '%'. $filtro . '%')
        ->orderBy('costois.nombre', 'asc')->get();
        

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
    public function update(Request $request, costois $costois)
    {
        if (!$request->ajax()) return redirect('/');
        $costop = Costois::findOrFail($request->id);
        $costop->idproveedor = $request->idproveedor;
        $costop->idpersona = $request->idproveedor;
        $costop->tipo_costo = $request->tipo_costo;
        $costop->nombre = $request->nombre;
        $costop->unidad_medida = $request->unidad;
        $costop->valor = $request->valor;
        $costop->save();
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
