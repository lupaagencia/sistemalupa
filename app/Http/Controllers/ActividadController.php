<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\CostoProduccion;

use Illuminate\Http\Request;
use stdClass;

class ActividadController extends Controller
{
    public function store($request)
    {
        $actividad = new Actividad;
        $actividad->user_id = $request->user_id ?: \Auth::id() ?: 1;
        $actividad->fecha = date('Y-m-d');
        $actividad->hora = date('H:i:s');
        $actividad->actividad = $request->actividad;
        $actividad->save();
        return $actividad;
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

