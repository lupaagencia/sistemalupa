<?php

namespace App\Http\Controllers;
use App\RegistroProduccion;
use App\Ordentrabajo;
use Illuminate\Http\Request;


class RegistroProduccionController extends Controller
{
    public function registroEmpleado(Request $request)
    {
        $fecha=date('Y-m-d');
        try {
            $registros = RegistroProduccion::where('fecha',$fecha)->where('empleado_id',$request->ide)
                ->orderBy('hora_fin', 'desc')
                ->get();
                return $registros;
           foreach ($registros as $registro) {
                $registro->codigo=0;
           }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los registros',
                'error' => $e->getMessage()
            ], 500);
        }
         return $registros;
    }
     public function crearRegistro(Request $request)
    {
        $registros=json_decode($request->registros);
        foreach ($registros as $registro) {
            if($registro->id==0){
                $reg= new RegistroProduccion();
            }else{
                $reg=RegistroProduccion::find($registro->id);
            }
            $reg->orden_trabajo_id=$registro->orden_trabajo_id;
            $reg->empleado_id=$registro->empleado_id;
            $reg->actividad=$registro->actividad;
            $reg->elemento=$registro->elemento;
            $reg->fecha=$registro->fecha;
            $reg->hora_inicio=$registro->hora_inicio;
            $reg->hora_fin=$registro->hora_fin;
            $reg->minutos=$registro->minutos;
            $reg->cantidad=$registro->cantidad;
            $reg->unidad=$registro->unidad;
            $reg->observaciones=$registro->observaciones;
            $reg->save();
        }
    }

    public function show($id)
    {
        return RegistroProduccion::with(['ordenTrabajo', 'cliente', 'empleado'])->findOrFail($id);
    }

    public function borrar(Request $request)
    {
        $registro = RegistroProduccion::findOrFail($request->id);
        $registro->delete();

        return response()->json(['message' => 'Registro eliminado.']);
    }
}
