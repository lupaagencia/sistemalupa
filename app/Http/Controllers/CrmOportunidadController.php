<?php

namespace App\Http\Controllers;

use App\CrmOportunidad;
use App\CrmEtapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CrmOportunidadController extends Controller
{
    public function index(Request $request)
    {
        $vendedor_id = $request->vendedor_id;
        $buscar = $request->buscar;
        $estado = $request->estado;
        $etapa_id = $request->etapa_id;

        $query = CrmOportunidad::with([
            'vendedor:id,usuario,email',
            'etapa:id,nombre,color,probabilidad',
            'prospecto:id,nombre,empresa,email,telefono',
            'cliente:id,nombre'
        ])->crmScope($vendedor_id);

        if ($buscar != '') {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('codigo', 'like', '%' . $buscar . '%');
            });
        }

        if ($estado != '') {
            $query->where('estado', $estado);
        }

        if ($etapa_id != '') {
            $query->where('etapa_id', $etapa_id);
        }

        $oportunidades = $query->orderBy('id', 'desc')->paginate(20);

        return [
            'pagination' => [
                'total'        => $oportunidades->total(),
                'current_page' => $oportunidades->currentPage(),
                'per_page'     => $oportunidades->perPage(),
                'last_page'    => $oportunidades->lastPage(),
                'from'         => $oportunidades->firstItem(),
                'to'           => $oportunidades->lastItem(),
            ],
            'oportunidades' => $oportunidades
        ];
    }

    public function kanban(Request $request)
    {
        $vendedor_id = $request->vendedor_id;

        $etapas = CrmEtapa::where('activo', true)
            ->orderBy('orden', 'asc')
            ->get();

        $oportunidades = CrmOportunidad::with([
            'vendedor:id,usuario',
            'prospecto:id,nombre,empresa',
            'cliente:id,nombre'
        ])
        ->crmScope($vendedor_id)
        ->get();

        $kanbanData = $etapas->map(function ($etapa) use ($oportunidades) {
            $items = $oportunidades->where('etapa_id', $etapa->id)->values();
            $totalMonto = $items->sum('monto_estimado');
            return [
                'id' => $etapa->id,
                'nombre' => $etapa->nombre,
                'color' => $etapa->color,
                'probabilidad' => $etapa->probabilidad,
                'monto_total' => $totalMonto,
                'total_items' => $items->count(),
                'items' => $items
            ];
        });

        return ['kanban' => $kanbanData];
    }

    public function selectOportunidades(Request $request)
    {
        $vendedor_id = $request->vendedor_id;
        $oportunidades = CrmOportunidad::select('id', 'codigo', 'nombre', 'monto_estimado')
            ->crmScope($vendedor_id)
            ->where('estado', 'Abierta')
            ->orderBy('id', 'desc')
            ->get();

        return ['oportunidades' => $oportunidades];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:191',
            'etapa_id' => 'required|exists:crm_etapas,id',
            'monto_estimado' => 'required|numeric|min:0',
        ]);

        $year = date('Y');
        $count = CrmOportunidad::whereYear('created_at', $year)->count() + 1;
        $codigo = 'OPP-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $oportunidad = new CrmOportunidad();
        $oportunidad->codigo = $codigo;
        $oportunidad->nombre = $request->nombre;
        $oportunidad->prospecto_id = $request->prospecto_id;
        $oportunidad->cliente_id = $request->cliente_id;
        $oportunidad->etapa_id = $request->etapa_id;
        $oportunidad->monto_estimado = $request->monto_estimado;
        $oportunidad->probabilidad = $request->input('probabilidad', 50);
        $oportunidad->fecha_cierre_estimada = $request->fecha_cierre_estimada;
        $oportunidad->estado = $request->input('estado', 'Abierta');
        $oportunidad->observaciones = $request->observaciones;

        if ($request->filled('user_id')) {
            $oportunidad->user_id = $request->user_id;
        } else {
            $oportunidad->user_id = Auth::id();
        }

        $oportunidad->save();

        return response()->json(['status' => 'success', 'message' => 'Oportunidad de negocio creada exitosamente', 'oportunidad' => $oportunidad]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_oportunidades,id',
            'nombre' => 'required|string|max:191',
            'etapa_id' => 'required|exists:crm_etapas,id',
            'monto_estimado' => 'required|numeric|min:0',
        ]);

        $oportunidad = CrmOportunidad::crmScope()->findOrFail($request->id);
        $oportunidad->nombre = $request->nombre;
        $oportunidad->prospecto_id = $request->prospecto_id;
        $oportunidad->cliente_id = $request->cliente_id;
        $oportunidad->etapa_id = $request->etapa_id;
        $oportunidad->monto_estimado = $request->monto_estimado;
        $oportunidad->probabilidad = $request->probabilidad;
        $oportunidad->fecha_cierre_estimada = $request->fecha_cierre_estimada;
        $oportunidad->estado = $request->estado;
        $oportunidad->motivo_perdida = $request->motivo_perdida;
        $oportunidad->observaciones = $request->observaciones;

        if ($request->filled('user_id')) {
            $oportunidad->user_id = $request->user_id;
        }

        $oportunidad->save();

        return response()->json(['status' => 'success', 'message' => 'Oportunidad actualizada exitosamente']);
    }

    public function cambiarEtapa(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_oportunidades,id',
            'etapa_id' => 'required|exists:crm_etapas,id',
        ]);

        $oportunidad = CrmOportunidad::crmScope()->findOrFail($request->id);
        $etapa = CrmEtapa::findOrFail($request->etapa_id);

        $oportunidad->etapa_id = $etapa->id;
        $oportunidad->probabilidad = $etapa->probabilidad;

        if (strtolower($etapa->nombre) == 'ganado (cierre)' || $etapa->probabilidad == 100) {
            $oportunidad->estado = 'Ganada';
        } elseif (strtolower($etapa->nombre) == 'perdido' || $etapa->probabilidad == 0) {
            $oportunidad->estado = 'Perdida';
        } else {
            $oportunidad->estado = 'Abierta';
        }

        $oportunidad->save();

        return response()->json(['status' => 'success', 'message' => 'Etapa de oportunidad actualizada']);
    }

    public function destroy(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar oportunidades.'], 403);
        }
        $oportunidad = CrmOportunidad::crmScope()->findOrFail($request->id);
        $oportunidad->delete();

        return response()->json(['status' => 'success', 'message' => 'Oportunidad eliminada']);
    }
}
