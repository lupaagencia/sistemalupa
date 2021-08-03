<?php

namespace App\Http\Controllers;

use App\Costoproduccion;
use Illuminate\Http\Request;
use GuzzleHttp;
use Storage;

class CostoproduccionController extends Controller
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
             $costosp = Costoproduccion::join('proveedores','costos.idproveedor','=','proveedores.id')->join('personas','costos.idpersona','=','personas.id')
             ->select('costos.id','costos.idproveedor','costos.tipo_costo','costos.nombre as nombre_insumo','proveedores.contacto','proveedores.telefono_contacto','personas.nombre')
             ->where('costos.tipo_costo', 'like', '%'. $buscar . '%')
             ->orderBy('costos.id', 'desc')->paginate(10);
         }
         else{
            $costosp = Costoproduccion::join('proveedores','costos.idproveedor','=','proveedores.id')->join('personas','costos.idpersona','=','personas.id')
            ->select('costos.id','costos.idproveedor','costos.tipo_costo','costos.nombre as nombre_insumo','proveedores.contacto','proveedores.telefono_contacto','personas.nombre')
            ->where('costos.tipo_costo', 'like', '%'. $buscar . '%')
            ->orderBy('costos.id', 'desc')->paginate(10);
         }
         
 
         return [  'costop' => $costosp ];
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
        //if(!$request->ajax()) return redirect('/');
        $costop = new Costoproduccion();
        $costop->idproveedor = $request->idproveedor;
        $costop->idpersona = $request->idproveedor;
        $costop->tipo_costo = $request->tipo_costo;
        $costop->nombre = $request->nombre;
        $costop->unidad_medida = $request->unidad;
        $costop->valor = $request->valor;
        $costop->total = 0;
        $costop->estado=1;
        $costop->save();
        return $costop;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Costoproduccion  $costoproduccion
     * @return \Illuminate\Http\Response
     */
    public function show(Costoproduccion $costoproduccion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Costoproduccion  $costoproduccion
     * @return \Illuminate\Http\Response
     */
    public function edit(Costoproduccion $costoproduccion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Costoproduccion  $costoproduccion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Costoproduccion $costoproduccion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Costoproduccion  $costoproduccion
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $id=$request->id;
        $costo = Costoproduccion::find($id);
        $costo->delete();
    }
}
