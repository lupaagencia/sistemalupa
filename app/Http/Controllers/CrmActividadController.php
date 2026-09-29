<?php

namespace App\Http\Controllers;

use App\CrmActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmActividadController extends Controller
{
    public function index(Request $request)
    {
        $vendedor_id = $request->vendedor_id;
        $completada = $request->completada;
        $tipo = $request->tipo;

        $query = CrmActividad::with([
            'vendedor:id,usuario,email',
            'prospecto:id,nombre,empresa',
            'oportunidad:id,codigo,nombre',
            'cotizacion:id,numero_cotizacion'
        ])->crmScope($vendedor_id);

        if ($completada !== null && $completada !== '') {
            $query->where('completada', filter_var($completada, FILTER_VALIDATE_BOOLEAN));
        }

        if ($tipo != '') {
            $query->where('tipo', $tipo);
        }

        $actividades = $query->orderBy('fecha_vencimiento', 'asc')->paginate(30);

        return [
            'pagination' => [
                'total'        => $actividades->total(),
                'current_page' => $actividades->currentPage(),
                'per_page'     => $actividades->perPage(),
                'last_page'    => $actividades->lastPage(),
                'from'         => $actividades->firstItem(),
                'to'           => $actividades->lastItem(),
            ],
            'actividades' => $actividades
        ];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tipo' => 'required|string',
            'asunto' => 'required|string|max:191',
        ]);

        $actividad = new CrmActividad();
        $actividad->tipo = $request->tipo;
        $actividad->asunto = $request->asunto;
        $actividad->descripcion = $request->descripcion;
        $actividad->prospecto_id = $request->prospecto_id;
        $actividad->oportunidad_id = $request->oportunidad_id;
        $actividad->cotizacion_id = $request->cotizacion_id;
        $actividad->fecha_vencimiento = $request->fecha_vencimiento ?: now();

        if ($request->filled('user_id') && in_array(Auth::user()->idrol, ['Administrador', 'Gerente Comercial'])) {
            $actividad->user_id = $request->user_id;
        } else {
            $actividad->user_id = Auth::id();
        }

        $actividad->save();

        return response()->json(['status' => 'success', 'message' => 'Actividad registrada', 'actividad' => $actividad]);
    }

    public function marcarCompletada(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_actividades,id',
        ]);

        $actividad = CrmActividad::crmScope()->findOrFail($request->id);
        $actividad->completada = !$actividad->completada;
        $actividad->fecha_completada = $actividad->completada ? now() : null;
        $actividad->save();

        return response()->json(['status' => 'success', 'message' => 'Estado de actividad actualizado', 'completada' => $actividad->completada]);
    }

    public function destroy(Request $request)
    {
        $actividad = CrmActividad::crmScope()->findOrFail($request->id);
        $actividad->delete();

        return response()->json(['status' => 'success', 'message' => 'Actividad eliminada']);
    }
}
