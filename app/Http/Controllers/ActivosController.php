<?php

namespace App\Http\Controllers;
use App\Activo;
use App\CostoProduccion;

use App\Ajustes;
use Illuminate\Http\Request;
use stdClass;
use DB;

class ActivosController extends Controller


{
    public function index(Request $request){
         //if(!$request->ajax()) return redirect('/');
         $buscar = $request->buscar;
         $criterio = $request->criterio;
         
         if ($buscar==''){
             $activos = Activo::orderBy('tipo', 'asc')->paginate(1000);
         }
         else{
             $activos = Activo::where($criterio, 'like', '%'. $buscar . '%')->orderBy('tipo', 'asc')->paginate(1000);
         }
         
         return [
             'pagination' => [
                 'total'        => $activos->total(),
                 'current_page' => $activos->currentPage(),
                 'per_page'     => $activos->perPage(),
                 'last_page'    => $activos->lastPage(),
                 'from'         => $activos->firstItem(),
                 'to'           => $activos->lastItem(),
             ],
             'activos' => $activos
         ];

    }
   
              
    public function activos(){
        $activos=Activo::all();
        $organizadoPorTipo = [];
        
        foreach ($activos as $activo) {
            
            $tipo = $activo['tipo']; // Obtener la ciudad de la persona
            // Si la ciudad aún no existe en el array, inicializarla
            if (!isset($organizadoPorTipo[$tipo])) {
                $organizadoPorTipo[$tipo] = [];
            }
        
            // Agregar la persona al array correspondiente a su ciudad
            $organizadoPorTipo[$tipo][] = $activo;
        }
        return $organizadoPorTipo;
    }   
  
    public function portipo(Request $request){
        $activo=Activo::where('tipo', $request->tipo)->get();
        return $activo;
    }   
    public function store(Request $request){
        if($request->id==0){

            $activo= new Activo();
        }else{
            $activo=Activo::find($request->id);
        }
        $activo->tipo=$request->tipo;
        $activo->activo=$request->activo;
        $activo->descripcion=$request->descripcion;
        $activo->ubicacion=$request->ubicacion;
        $activo->responsable=$request->responsable;
        $activo->clasificacion=$request->clasificacion;
        $activo->estado=$request->estado;
        $activo->grupo=$request->grupo;
        $activo->datos_activo=$request->datos_activo;
        $activo->save();
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

