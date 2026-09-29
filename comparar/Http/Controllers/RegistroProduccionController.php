<?php

namespace App\Http\Controllers;
use App\RegistroProduccion;
use App\Ordentrabajo;
use Illuminate\Http\Request;


class RegistroProduccionController extends Controller
{
    public function registroEmpleado(Request $request)
    {
        $fecha = $request->fecha ? $request->fecha : date('Y-m-d');
        try {
            $registros = RegistroProduccion::where('fecha',$fecha)->where('empleado_id',$request->ide)
                ->orderBy('hora_fin', 'desc')
                ->with(['ordenTrabajo.cliente', 'ordenTrabajo.articulo'])
                ->get();
            return $registros;
        } catch (\Exception $e) {
            \Log::error('Error en registroEmpleado: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los registros',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function crearRegistro(Request $request)
    {
        \Log::debug('Registros RAW recibidos: ' . $request->registros);
        try {
            $registros = json_decode($request->registros);
            if (!$registros) {
                return response()->json(['error' => 'No se pudo decodificar el JSON'], 400);
            }
            foreach ($registros as $registro) {
                if ($registro->id == 0) {
                    $reg = new RegistroProduccion();
                } else {
                    $reg = RegistroProduccion::find($registro->id);
                    if (!$reg) continue;
                }
                
                // Conversión segura de campos numéricos
                $reg->orden_trabajo_id = (isset($registro->orden_trabajo_id) && is_numeric($registro->orden_trabajo_id) && $registro->orden_trabajo_id > 0) ? (int)$registro->orden_trabajo_id : null;
                $reg->empleado_id = $registro->empleado_id;
                $reg->actividad = $registro->actividad;
                $reg->elemento = $registro->elemento;
                $reg->fecha = $registro->fecha;
                $reg->hora_inicio = (!empty($registro->hora_inicio)) ? $registro->hora_inicio : null;
                $reg->hora_fin = (!empty($registro->hora_fin)) ? $registro->hora_fin : null;
                $reg->minutos = (isset($registro->minutos) && is_numeric($registro->minutos)) ? (int)$registro->minutos : 0;
                $reg->cantidad = (isset($registro->cantidad) && is_numeric($registro->cantidad)) ? (int)$registro->cantidad : 0;
                $reg->unidad = $registro->unidad;
                $reg->observaciones = $registro->observaciones;
                $reg->save();
                // Registro guardado con éxito
            }
            return response()->json(['message' => 'Guardado con éxito'], 200);
        } catch (\Throwable $e) {
            \Log::error('Error al guardar producción: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return RegistroProduccion::with(['ordenTrabajo', 'cliente', 'empleado'])->findOrFail($id);
    }
    public function resumenPorEmpleado(Request $request)
    {
        $inicio = $request->inicio;
        $fin = $request->fin;

        $data = \DB::table('registros_produccion')
            ->select(
                'empleado_id',
                \DB::raw('SUM(minutos) as total_minutos'),
                \DB::raw('SUM(cantidad) as total_cantidad'),
                \DB::raw('ROUND(SUM(minutos)/NULLIF(SUM(cantidad),0),2) as min_por_unidad')
            )
            ->when($inicio && $fin, function($q) use ($inicio, $fin) {
                return $q->whereBetween('fecha', [$inicio, $fin]);
            })
            ->groupBy('empleado_id')
            ->get();

        return $data;
    }

    public function getEstadisticas(Request $request) 
    {
        $inicio = $request->inicio;
        $fin = $request->fin;
        $empleado_id = $request->empleado_id;

        $query = RegistroProduccion::select(
                'registros_produccion.*',
                'empleados.nombre as nombre_emp',
                'empleados.apellido as apellido_emp'
            )
            ->join('empleados', 'registros_produccion.empleado_id', '=', 'empleados.id')
            ->when($inicio, function($q) use ($inicio) {
                return $q->where('fecha', '>=', $inicio);
            })
            ->when($fin, function($q) use ($fin) {
                return $q->where('fecha', '<=', $fin);
            })
            ->when($empleado_id, function($q) use ($empleado_id) {
                return $q->where('empleado_id', $empleado_id);
            });

        // Group by Employee
        $porEmpleado = (clone $query)
            ->select(
                'empleado_id',
                \DB::raw("CONCAT(empleados.nombre, ' ', empleados.apellido) as nombre_empleado"),
                \DB::raw('count(*) as total_registros'),
                \DB::raw('SUM(minutos) as total_minutos'),
                \DB::raw('SUM(cantidad) as total_cantidad')
            )
            ->groupBy('empleado_id', 'empleados.nombre', 'empleados.apellido')
            ->get();

        // Group by Activity
        $porActividad = (clone $query)
            ->select(
                'actividad',
                \DB::raw('count(*) as total_registros'),
                \DB::raw('SUM(minutos) as total_minutos'),
                \DB::raw('SUM(cantidad) as total_cantidad'),
                \DB::raw('ROUND(AVG(NULLIF(minutos, 0)), 2) as avg_minutos')
            )
            ->groupBy('actividad')
            ->orderBy('total_minutos', 'desc')
            ->get();

        // Summary
        $resumen = [
            'total_minutos' => $query->sum('minutos'),
            'total_cantidad' => $query->sum('cantidad'),
            'count_registros' => $query->count(),
            'avg_eficiencia' => $query->avg('minutos') // Just a placeholder
        ];

        return response()->json([
            'resumen' => $resumen,
            'por_empleado' => $porEmpleado,
            'por_actividad' => $porActividad
        ]);
    }
    public function borrar(Request $request)
    {
        $registro = RegistroProduccion::findOrFail($request->id);
        $registro->delete();

        return response()->json(['message' => 'Registro eliminado.']);
    }
}
