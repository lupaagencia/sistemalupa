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
        $query = Ajustes::query();
        if ($request->has('tipo') && $request->tipo != '' && $request->tipo != 'todos') {
            $query->where('tipo', $request->tipo);
        }
        if ($request->has('categoria') && $request->categoria != '' && $request->categoria != 'todas') {
            $query->where('categoria', $request->categoria);
        }
        if ($request->has('buscar') && $request->buscar != '') {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('detalle', 'like', "%{$buscar}%")
                  ->orWhere('valor', 'like', "%{$buscar}%")
                  ->orWhere('categoria', 'like', "%{$buscar}%");
            });
        }
        return $query->orderBy('id', 'desc')->get();
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
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar ajustes.'], 403);
        }
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
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar actividades.'], 403);
        }
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

    public function getNominaConfig(Request $request)
    {
        $auxilio = Ajustes::getAuxilioTransporte(200000);
        $salarioMinimo = Ajustes::getSalarioMinimo(1400000);
        return response()->json([
            'auxilio_transporte' => $auxilio,
            'salario_minimo' => $salarioMinimo
        ]);
    }

    public function saveNominaConfig(Request $request)
    {
        $request->validate([
            'auxilio_transporte' => 'required|numeric|min:0',
            'salario_minimo' => 'required|numeric|min:0',
        ]);

        $auxVal = (float)$request->auxilio_transporte;
        $smVal = (float)$request->salario_minimo;

        Ajustes::updateOrCreate(
            ['tipo' => 'nomina', 'detalle' => 'auxilio_transporte'],
            ['valor' => (string)$auxVal, 'categoria' => 'nomina']
        );

        Ajustes::updateOrCreate(
            ['tipo' => 'nomina', 'detalle' => 'salario_minimo'],
            ['valor' => (string)$smVal, 'categoria' => 'nomina']
        );

        $afectados = 0;
        if ($request->input('aplicar_a_empleados', true)) {
            $afectados = \App\Empleado::where(function($q) {
                $q->whereNull('tipo_contrato')
                  ->orWhere('tipo_contrato', '!=', 'Prestación de servicios');
            })->update(['auxilio_transporte' => $auxVal]);
        }

        return response()->json([
            'message' => 'Configuración guardada correctamente.',
            'auxilio_transporte' => $auxVal,
            'salario_minimo' => $smVal,
            'empleados_actualizados' => $afectados
        ]);
    }
}

