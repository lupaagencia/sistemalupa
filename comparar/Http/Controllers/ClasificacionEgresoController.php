<?php

namespace App\Http\Controllers;

use App\ClasificacionEgreso;
use App\Egresos;
use Illuminate\Http\Request;

class ClasificacionEgresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $clasificaciones = ClasificacionEgreso::orderBy('nombre', 'asc')->get();
        return response()->json($clasificaciones);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:clasificaciones_egresos,nombre',
            'descripcion' => 'nullable|string|max:255'
        ], [
            'nombre.unique' => 'El nombre de la clasificación ya existe.'
        ]);

        $clasificacion = new ClasificacionEgreso();
        $clasificacion->nombre = $request->nombre;
        $clasificacion->descripcion = $request->descripcion;
        $clasificacion->save();

        return response()->json([
            'success' => true,
            'message' => 'Clasificación registrada correctamente.',
            'clasificacion' => $clasificacion
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $clasificacion = ClasificacionEgreso::findOrFail($id);

        // Check if there are egresos linked to this classification name
        $inUse = Egresos::where('tipo_egreso', $clasificacion->nombre)->exists();
        if ($inUse) {
            return response()->json([
                'error' => 'No se puede eliminar la clasificación "' . $clasificacion->nombre . '" porque está en uso en registros de egresos.'
            ], 422);
        }

        $clasificacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Clasificación eliminada correctamente.'
        ]);
    }
}
