<?php

namespace App\Http\Controllers;
use App\Actividad;
use App\CostoProduccion;

use App\Ajustes;
use Illuminate\Http\Request;
use stdClass;
use DB;

class AjustesController extends Controller


{
    public function titulosDetalle(Request $request)
    {
              
        $resultados = Ajustes::select('detalle')
        ->where('tipo', 'titulo_detalle')
        ->groupBy('detalle')
        ->orderBy('detalle', 'desc')
        ->get();
        return $resultados;
    }
    public function listAjustes(Request $request){
        return Ajustes::where('tipo', $request->tipo)->get();
    }
    public function codigos(){
        $codigos = Ajustes::where('tipo','actividad')->get();
        return $codigos;
    }
    public function store_ajuste(Request $request){
        $ajuste = new Ajustes();
        $ajuste->tipo = $request->tipo;
        $ajuste->detalle = $request->detalle;
        $ajuste->valor = $request->valor;
        $ajuste->categoria = $request->categoria;
        $ajuste->save();
        return $ajuste;
    }
    public function update_ajuste(Request $request){
        $ajuste = Ajustes::findOrFail($request->id);
        $ajuste->tipo = $request->tipo;
        $ajuste->detalle = $request->detalle;
        $ajuste->valor = $request->valor;
        $ajuste->categoria = $request->categoria;
        $ajuste->save();
        return $ajuste;
    }
    public function delete_ajuste(Request $request){
        $ajuste = Ajustes::findOrFail($request->id);
        $ajuste->delete();
        return response()->json(['message' => 'Eliminado']);
    }
    public function store($request){
        $actividad=new Actividad;
        $actividad->user_id=$request->user_id;
        $actividad->fecha=date('Y-m-d');
        $actividad->hora=date('H:i:s');
        $actividad->actividad=$request->actividad;
        $actividad->save();
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        $actividad = Actividad::find($id);
        $actividad->delete();
        $datosA = new stdClass();
        $datosA->user_id = $actividad->user_id;
        $nombre_user = $actividad->user->empleado ? ($actividad->user->empleado->nombre . ' ' . $actividad->user->empleado->apellido) : $actividad->user->usuario;
        $datosA->actividad = 'Se elimino actividad "' . $actividad->actividad . '" del Usuario ' . $actividad->user->usuario . ' Nombre ' . $nombre_user;
        $acti = new ActividadController();
        $acti->store($datosA);
        return $actividad;
    }
}

