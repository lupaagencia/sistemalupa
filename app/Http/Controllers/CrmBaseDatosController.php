<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CrmBaseDatos;
use App\CrmContactoBase;
use App\CrmGestionContactoLog;
use App\CrmProspecto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmBaseDatosController extends Controller
{
    public function index(Request $request)
    {
        $vendedor_id = $request->get('vendedor_id');
        $buscar = $request->get('buscar');

        $query = CrmBaseDatos::with(['user:id,usuario,idrol'])->crmScope($vendedor_id);

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('sector', 'like', "%{$buscar}%")
                  ->orWhere('origen', 'like', "%{$buscar}%");
            });
        }

        $bases = $query->orderBy('id', 'desc')->paginate(15);

        // Enhance with real-time stats
        foreach ($bases as $base) {
            $base->total_contactos = CrmContactoBase::where('base_datos_id', $base->id)->count();
            $base->portafolios_enviados = CrmContactoBase::where('base_datos_id', $base->id)->where('portafolio_enviado', true)->count();
            $base->interesados = CrmContactoBase::where('base_datos_id', $base->id)->where('estado_gestion', 'Interesado')->count();
            $base->convertidos = CrmContactoBase::where('base_datos_id', $base->id)->where('estado_gestion', 'Convertido a Prospecto')->count();
        }

        return response()->json([
            'bases' => $bases
        ]);
    }

    public function selectBases(Request $request)
    {
        $vendedor_id = $request->get('vendedor_id');
        $bases = CrmBaseDatos::crmScope($vendedor_id)->select('id', 'nombre', 'sector')->orderBy('nombre', 'asc')->get();
        return response()->json(['bases' => $bases]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:191',
            'sector' => 'nullable|string|max:100',
            'origen' => 'nullable|string|max:100',
        ]);

        $base = new CrmBaseDatos();
        $base->user_id = $request->input('user_id', Auth::id());
        $base->nombre = $request->nombre;
        $base->sector = $request->sector;
        $base->origen = $request->origen ?: 'Cámara de Comercio';
        $base->descripcion = $request->descripcion;
        $base->estado = $request->input('estado', 'Activa');
        $base->save();

        return response()->json(['status' => 'success', 'message' => 'Base de datos creada exitosamente', 'base' => $base]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_bases_datos,id',
            'nombre' => 'required|string|max:191',
        ]);

        $base = CrmBaseDatos::crmScope()->findOrFail($request->id);
        $base->nombre = $request->nombre;
        $base->sector = $request->sector;
        $base->origen = $request->origen;
        $base->descripcion = $request->descripcion;
        $base->estado = $request->estado ?: 'Activa';
        if ($request->has('user_id') && Auth::user()->idrol !== 'Vendedor') {
            $base->user_id = $request->user_id;
        }
        $base->save();

        return response()->json(['status' => 'success', 'message' => 'Base de datos actualizada', 'base' => $base]);
    }

    public function destroy(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar bases de datos.'], 403);
        }
        $base = CrmBaseDatos::crmScope()->findOrFail($request->id);
        $base->delete();

        return response()->json(['status' => 'success', 'message' => 'Base de datos eliminada']);
    }

    // ── CONTACTOS DE LAS BASES DE DATOS ─────────────────────────────────────────

    public function getContactos(Request $request)
    {
        $base_datos_id = $request->get('base_datos_id');
        $vendedor_id = $request->get('vendedor_id');
        $sector = $request->get('sector');
        $estado_gestion = $request->get('estado_gestion');
        $portafolio_enviado = $request->get('portafolio_enviado');
        $buscar = $request->get('buscar');

        $query = CrmContactoBase::with(['baseDatos:id,nombre', 'user:id,usuario', 'logs.user:id,usuario'])
            ->crmScope($vendedor_id);

        if ($base_datos_id) {
            $query->where('base_datos_id', $base_datos_id);
        }

        if ($sector) {
            $query->where('sector', $sector);
        }

        if ($estado_gestion) {
            $query->where('estado_gestion', $estado_gestion);
        }

        if ($portafolio_enviado !== null && $portafolio_enviado !== '') {
            $query->where('portafolio_enviado', filter_var($portafolio_enviado, FILTER_VALIDATE_BOOLEAN));
        }

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('empresa', 'like', "%{$buscar}%")
                  ->orWhere('contacto_nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('ciudad', 'like', "%{$buscar}%");
            });
        }

        $contactos = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json(['contactos' => $contactos]);
    }

    public function storeContacto(Request $request)
    {
        $this->validate($request, [
            'base_datos_id' => 'required|exists:crm_bases_datos,id',
            'empresa' => 'required|string|max:191',
        ]);

        $base = CrmBaseDatos::find($request->base_datos_id);
        if (!$base) {
            return response()->json(['status' => 'error', 'message' => 'Base de datos no encontrada.'], 404);
        }

        $contacto = new CrmContactoBase();
        $contacto->base_datos_id = $base->id;
        $contacto->user_id = $base->user_id ?: Auth::id();
        $contacto->empresa = $request->empresa;
        $contacto->contacto_nombre = $request->contacto_nombre;
        $contacto->cargo = $request->cargo;
        $contacto->sector = $request->sector ?: $base->sector;
        $contacto->telefono = $request->telefono;
        $contacto->email = $request->email;
        $contacto->ciudad = $request->ciudad;
        $contacto->direccion = $request->direccion;
        $contacto->origen_detalle = $request->origen_detalle;
        $contacto->estado_gestion = $request->input('estado_gestion', 'Sin Contactar');
        $contacto->portafolio_enviado = filter_var($request->input('portafolio_enviado', false), FILTER_VALIDATE_BOOLEAN);
        if ($contacto->portafolio_enviado) {
            $contacto->fecha_envio_portafolio = now();
        }
        $contacto->save();

        // Log entry
        CrmGestionContactoLog::create([
            'contacto_id' => $contacto->id,
            'user_id' => Auth::id(),
            'tipo_accion' => 'Registro de Contacto',
            'detalle' => 'Contacto registrado en la base ' . $base->nombre
        ]);

        return response()->json(['status' => 'success', 'message' => 'Contacto registrado exitosamente', 'contacto' => $contacto]);
    }

    public function updateContacto(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_contactos_base,id',
            'empresa' => 'required|string|max:191',
        ]);

        $contacto = CrmContactoBase::crmScope()->findOrFail($request->id);
        $estadoAnterior = $contacto->estado_gestion;

        $contacto->empresa = $request->empresa;
        $contacto->contacto_nombre = $request->contacto_nombre;
        $contacto->cargo = $request->cargo;
        $contacto->sector = $request->sector;
        $contacto->telefono = $request->telefono;
        $contacto->email = $request->email;
        $contacto->ciudad = $request->ciudad;
        $contacto->direccion = $request->direccion;
        $contacto->origen_detalle = $request->origen_detalle;
        $contacto->estado_gestion = $request->estado_gestion;
        $contacto->resultado_gestion = $request->resultado_gestion;
        $contacto->fecha_ultimo_contacto = now();

        if ($request->has('portafolio_enviado')) {
            $contacto->portafolio_enviado = filter_var($request->portafolio_enviado, FILTER_VALIDATE_BOOLEAN);
            if ($contacto->portafolio_enviado && !$contacto->fecha_envio_portafolio) {
                $contacto->fecha_envio_portafolio = now();
            }
        }

        $contacto->save();

        // Log state change
        if ($estadoAnterior !== $contacto->estado_gestion || $request->filled('resultado_gestion')) {
            CrmGestionContactoLog::create([
                'contacto_id' => $contacto->id,
                'user_id' => Auth::id(),
                'tipo_accion' => 'Gestión Outbound',
                'detalle' => 'Estado: ' . $contacto->estado_gestion . ($request->resultado_gestion ? ' | Notas: ' . $request->resultado_gestion : '')
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Contacto actualizado', 'contacto' => $contacto]);
    }

    public function registrarPortafolio(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array|min:1',
            'metodo_envio' => 'required|string',
        ]);

        $ids = $request->ids;
        $metodo = $request->metodo_envio;
        $notas = $request->input('notas', 'Envío de portafolio comercial');

        $contactos = CrmContactoBase::crmScope()->whereIn('id', $ids)->get();

        foreach ($contactos as $contacto) {
            $contacto->portafolio_enviado = true;
            $contacto->fecha_envio_portafolio = now();
            $contacto->metodo_envio = $metodo;
            if ($contacto->estado_gestion === 'Sin Contactar') {
                $contacto->estado_gestion = 'Portafolio Enviado';
            }
            $contacto->fecha_ultimo_contacto = now();
            $contacto->save();

            CrmGestionContactoLog::create([
                'contacto_id' => $contacto->id,
                'user_id' => Auth::id(),
                'tipo_accion' => 'Envío de Portafolio',
                'detalle' => 'Portafolio enviado vía ' . $metodo . ($notas ? ' - ' . $notas : '')
            ]);
        }

        return response()->json(['status' => 'success', 'message' => count($contactos) . ' contacto(s) actualizado(s) con envío de portafolio']);
    }

    public function importarContactos(Request $request)
    {
        $this->validate($request, [
            'base_datos_id' => 'required|exists:crm_bases_datos,id',
            'contactos' => 'required|array|min:1',
        ]);

        $base = CrmBaseDatos::crmScope()->findOrFail($request->base_datos_id);
        $rows = $request->contactos;
        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if (empty($row['empresa'])) {
                    continue;
                }

                CrmContactoBase::create([
                    'base_datos_id' => $base->id,
                    'user_id' => $base->user_id,
                    'empresa' => trim($row['empresa']),
                    'contacto_nombre' => isset($row['contacto_nombre']) ? trim($row['contacto_nombre']) : null,
                    'cargo' => isset($row['cargo']) ? trim($row['cargo']) : null,
                    'sector' => isset($row['sector']) ? trim($row['sector']) : $base->sector,
                    'telefono' => isset($row['telefono']) ? trim($row['telefono']) : null,
                    'email' => isset($row['email']) ? trim($row['email']) : null,
                    'ciudad' => isset($row['ciudad']) ? trim($row['ciudad']) : null,
                    'direccion' => isset($row['direccion']) ? trim($row['direccion']) : null,
                    'origen_detalle' => isset($row['origen_detalle']) ? trim($row['origen_detalle']) : $base->origen,
                    'estado_gestion' => 'Sin Contactar',
                    'portafolio_enviado' => false,
                ]);

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Se importaron exitosamente {$importedCount} contactos a la base '{$base->nombre}'"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error durante la importación: ' . $e->getMessage()], 500);
        }
    }

    public function convertirAProspecto(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_contactos_base,id',
        ]);

        $contacto = CrmContactoBase::crmScope()->findOrFail($request->id);

        if ($contacto->prospecto_id) {
            return response()->json(['status' => 'warning', 'message' => 'Este contacto ya fue convertido anteriormente a prospecto.'], 400);
        }

        // Create Prospecto in crm_prospectos
        $prospecto = new CrmProspecto();
        $prospecto->user_id = $contacto->user_id;
        $prospecto->nombre = $contacto->contacto_nombre ?: $contacto->empresa;
        $prospecto->empresa = $contacto->empresa;
        $prospecto->cargo = $contacto->cargo;
        $prospecto->telefono = $contacto->telefono;
        $prospecto->email = $contacto->email;
        $prospecto->ciudad = $contacto->ciudad;
        $prospecto->direccion = $contacto->direccion;
        $prospecto->sector = $contacto->sector;
        $prospecto->origen = $contacto->baseDatos ? $contacto->baseDatos->origen : 'Base de Datos Outbound';
        $prospecto->observaciones = 'Convertido desde la base de datos: ' . ($contacto->baseDatos ? $contacto->baseDatos->nombre : '') . ($contacto->resultado_gestion ? ' | Notas: ' . $contacto->resultado_gestion : '');
        $prospecto->estado = 'Nuevo';
        $prospecto->save();

        // Update contact state
        $contacto->prospecto_id = $prospecto->id;
        $contacto->estado_gestion = 'Convertido a Prospecto';
        $contacto->save();

        CrmGestionContactoLog::create([
            'contacto_id' => $contacto->id,
            'user_id' => Auth::id(),
            'tipo_accion' => 'Conversión a Prospecto',
            'detalle' => 'Contacto convertido exitosamente a Prospecto CRM (ID #' . $prospecto->id . ')'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '¡Contacto convertido exitosamente en Prospecto del CRM!',
            'prospecto' => $prospecto
        ]);
    }

    public function getEstadisticas(Request $request)
    {
        $vendedor_id = $request->get('vendedor_id');

        $baseQuery = CrmContactoBase::crmScope($vendedor_id);

        $totalContactos = (clone $baseQuery)->count();
        $portafoliosEnviados = (clone $baseQuery)->where('portafolio_enviado', true)->count();
        $interesados = (clone $baseQuery)->where('estado_gestion', 'Interesado')->count();
        $convertidos = (clone $baseQuery)->where('estado_gestion', 'Convertido a Prospecto')->count();
        $descartados = (clone $baseQuery)->where('estado_gestion', 'Descartado')->count();

        // Group by sector
        $porSector = (clone $baseQuery)->select('sector', DB::raw('count(*) as total'), DB::raw('sum(case when portafolio_enviado = 1 then 1 else 0 end) as portafolios'))
            ->groupBy('sector')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'kpis' => [
                'totalContactos' => $totalContactos,
                'portafoliosEnviados' => $portafoliosEnviados,
                'interesados' => $interesados,
                'convertidos' => $convertidos,
                'descartados' => $descartados,
                'tasaPortafolio' => $totalContactos > 0 ? round(($portafoliosEnviados / $totalContactos) * 100, 1) : 0,
                'tasaConversion' => $totalContactos > 0 ? round(($convertidos / $totalContactos) * 100, 1) : 0,
            ],
            'porSector' => $porSector
        ]);
    }

    // ── GESTIÓN DE NICHOS (SECTORES) Y ORÍGENES CONFIGURABLES ───────────────────

    public function getCatalogos()
    {
        $sectoresMap = \App\Ajustes::where('tipo', 'crm_nicho')->pluck('valor', 'id')->toArray();
        $origenesMap = \App\Ajustes::where('tipo', 'crm_origen')->pluck('valor', 'id')->toArray();

        // Seed defaults if empty
        if (empty($sectoresMap)) {
            $defaultSectores = ['Restaurantes', 'Laboratorios', 'Alimentos y Bebidas', 'Cosmética y Cuidado Personal', 'Consumo Masivo', 'Impresiones y Empaques', 'Farmacéutico', 'Agroindustria', 'Otros'];
            foreach ($defaultSectores as $s) {
                \App\Ajustes::create(['tipo' => 'crm_nicho', 'detalle' => $s, 'valor' => $s, 'categoria' => 'CRM']);
            }
            $sectoresMap = \App\Ajustes::where('tipo', 'crm_nicho')->pluck('valor', 'id')->toArray();
        }

        if (empty($origenesMap)) {
            $defaultOrigenes = ['Cámara de Comercio', 'Prospección Directa', 'Redes Sociales', 'Sitio Web', 'Base Comprada', 'Directorio Empresarial', 'Recomendado / Referido'];
            foreach ($defaultOrigenes as $o) {
                \App\Ajustes::create(['tipo' => 'crm_origen', 'detalle' => $o, 'valor' => $o, 'categoria' => 'CRM']);
            }
            $origenesMap = \App\Ajustes::where('tipo', 'crm_origen')->pluck('valor', 'id')->toArray();
        }

        $sectoresList = [];
        foreach ($sectoresMap as $id => $val) {
            $sectoresList[] = ['id' => $id, 'nombre' => $val];
        }

        $origenesList = [];
        foreach ($origenesMap as $id => $val) {
            $origenesList[] = ['id' => $id, 'nombre' => $val];
        }

        return response()->json([
            'sectores' => $sectoresList,
            'origenes' => $origenesList
        ]);
    }

    public function storeCatalogItem(Request $request)
    {
        $this->validate($request, [
            'tipo' => 'required|in:crm_nicho,crm_origen',
            'nombre' => 'required|string|max:191'
        ]);

        $item = \App\Ajustes::create([
            'tipo' => $request->tipo,
            'detalle' => $request->nombre,
            'valor' => $request->nombre,
            'categoria' => 'CRM'
        ]);

        return response()->json(['status' => 'success', 'message' => 'Elemento creado exitosamente', 'item' => $item]);
    }

    public function updateCatalogItem(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:ajustes,id',
            'nombre' => 'required|string|max:191'
        ]);

        $item = \App\Ajustes::findOrFail($request->id);
        $item->detalle = $request->nombre;
        $item->valor = $request->nombre;
        $item->save();

        return response()->json(['status' => 'success', 'message' => 'Elemento actualizado']);
    }

    public function deleteCatalogItem(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar elementos del catálogo.'], 403);
        }
        $this->validate($request, [
            'id' => 'required|exists:ajustes,id'
        ]);

        $item = \App\Ajustes::findOrFail($request->id);
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Elemento eliminado']);
    }
}
