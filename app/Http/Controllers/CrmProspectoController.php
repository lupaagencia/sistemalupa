<?php

namespace App\Http\Controllers;

use App\CrmProspecto;
use App\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmProspectoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $estado = $request->estado;
        $origen = $request->origen;
        $vendedor_id = $request->vendedor_id;

        $query = CrmProspecto::with(['vendedor:id,usuario,email', 'cliente:id,nombre'])
            ->crmScope($vendedor_id);

        if ($buscar != '') {
            if ($criterio == 'empresa') {
                $query->where('empresa', 'like', '%' . $buscar . '%');
            } elseif ($criterio == 'email') {
                $query->where('email', 'like', '%' . $buscar . '%');
            } elseif ($criterio == 'telefono') {
                $query->where('telefono', 'like', '%' . $buscar . '%');
            } else {
                $query->where('nombre', 'like', '%' . $buscar . '%');
            }
        }

        if ($estado != '') {
            $query->where('estado', $estado);
        }

        if ($origen != '') {
            $query->where('origen', $origen);
        }

        $prospectos = $query->orderBy('id', 'desc')->paginate(20);

        return [
            'pagination' => [
                'total'        => $prospectos->total(),
                'current_page' => $prospectos->currentPage(),
                'per_page'     => $prospectos->perPage(),
                'last_page'    => $prospectos->lastPage(),
                'from'         => $prospectos->firstItem(),
                'to'           => $prospectos->lastItem(),
            ],
            'prospectos' => $prospectos
        ];
    }

    public function selectProspectos(Request $request)
    {
        $vendedor_id = $request->vendedor_id;
        $filtro = $request->input('filtro', $request->input('buscar', ''));

        $query = CrmProspecto::select('id', 'nombre', 'empresa', 'email', 'telefono')
            ->crmScope($vendedor_id)
            ->where('estado', '!=', 'Descartado');

        if (!empty($filtro)) {
            $query->where(function($q) use ($filtro) {
                $q->where('nombre', 'like', '%' . $filtro . '%')
                  ->orWhere('empresa', 'like', '%' . $filtro . '%')
                  ->orWhere('email', 'like', '%' . $filtro . '%');
            });
        }

        $prospectos = $query->orderBy('nombre', 'asc')->limit(50)->get();

        return ['prospectos' => $prospectos];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:191',
        ]);

        $prospecto = new CrmProspecto();
        $prospecto->nombre = $request->nombre;
        $prospecto->empresa = $request->empresa;
        $prospecto->cargo = $request->cargo;
        $prospecto->email = $request->email;
        $prospecto->telefono = $request->telefono;
        $prospecto->celular = $request->celular;
        $prospecto->direccion = $request->direccion;
        $prospecto->ciudad = $request->ciudad;
        $prospecto->origen = $request->input('origen', 'Web');
        $prospecto->estado = $request->input('estado', 'Nuevo');
        $prospecto->observaciones = $request->observaciones;

        // Vendedor assignment
        if ($request->filled('user_id') && in_array(Auth::user()->idrol, ['Administrador', 'Gerente Comercial'])) {
            $prospecto->user_id = $request->user_id;
        } else {
            $prospecto->user_id = Auth::id();
        }

        $prospecto->save();

        return response()->json(['status' => 'success', 'message' => 'Prospecto registrado exitosamente', 'prospecto' => $prospecto]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_prospectos,id',
            'nombre' => 'required|string|max:191',
        ]);

        $prospecto = CrmProspecto::crmScope()->findOrFail($request->id);
        $prospecto->nombre = $request->nombre;
        $prospecto->empresa = $request->empresa;
        $prospecto->cargo = $request->cargo;
        $prospecto->email = $request->email;
        $prospecto->telefono = $request->telefono;
        $prospecto->celular = $request->celular;
        $prospecto->direccion = $request->direccion;
        $prospecto->ciudad = $request->ciudad;
        $prospecto->origen = $request->origen;
        $prospecto->estado = $request->estado;
        $prospecto->observaciones = $request->observaciones;

        if ($request->filled('user_id') && in_array(Auth::user()->idrol, ['Administrador', 'Gerente Comercial'])) {
            $prospecto->user_id = $request->user_id;
        }

        $prospecto->save();

        return response()->json(['status' => 'success', 'message' => 'Prospecto actualizado exitosamente']);
    }

    public function destroy(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar prospectos.'], 403);
        }
        $prospecto = CrmProspecto::crmScope()->findOrFail($request->id);
        $prospecto->delete();

        return response()->json(['status' => 'success', 'message' => 'Prospecto eliminado']);
    }

    public function convertirACliente(Request $request)
    {
        $prospecto = CrmProspecto::crmScope()->findOrFail($request->id);

        try {
            DB::beginTransaction();

            $persona = new Persona();
            $persona->nombre = $prospecto->nombre;
            $persona->tipo_documento = $request->input('tipo_documento', 'NIT');
            $persona->num_documento = $request->input('num_documento', '000000000');
            $persona->direccion = $prospecto->direccion ?: 'Ciudad';
            $persona->telefono = $prospecto->telefono ?: $prospecto->celular;
            $persona->email = $prospecto->email;
            $persona->save();

            $prospecto->cliente_id = $persona->id;
            $prospecto->estado = 'Convertido';
            $prospecto->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Prospecto convertido en cliente del sistema exitosamente', 'cliente' => $persona]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al convertir: ' . $e->getMessage()], 500);
        }
    }
}
