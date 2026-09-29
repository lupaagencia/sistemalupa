<?php

namespace App\Http\Controllers;

use App\CrmProspecto;
use App\CrmOportunidad;
use App\CrmCotizacion;
use App\CrmActividad;
use App\CrmMetaVenta;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmDashboardController extends Controller
{
    public function getEquipo()
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['equipo' => []]);
        }

        $role = $authUser->idrol;

        if ($role === 'Vendedor') {
            // Vendedor only sees himself in the dropdown
            $vendedores = User::where('id', $authUser->id)->select('id', 'usuario', 'email', 'idrol')->get();
        } elseif ($role === 'Gerente Comercial') {
            // Gerente Comercial sees himself + all Vendedores
            $vendedores = User::where(function($q) use ($authUser) {
                $q->where('id', $authUser->id)->orWhere('idrol', 'Vendedor');
            })->where('condicion', '1')->select('id', 'usuario', 'email', 'idrol')->orderBy('usuario', 'asc')->get();
        } else {
            // Administrador / Coordinador sees all active users
            $vendedores = User::where('condicion', '1')->select('id', 'usuario', 'email', 'idrol')->orderBy('usuario', 'asc')->get();
        }

        return response()->json([
            'user_role' => $role,
            'current_user_id' => $authUser->id,
            'equipo' => $vendedores
        ]);
    }

    public function selectVendedores()
    {
        $vendedores = \App\User::where('condicion', '1')
            ->select('id', 'usuario', 'email', 'idrol')
            ->orderBy('usuario', 'asc')
            ->get();

        return response()->json(['vendedores' => $vendedores]);
    }

    public function getKpis(Request $request)
    {
        $vendedor_id = $request->vendedor_id;

        $totalProspectos = CrmProspecto::crmScope($vendedor_id)->count();
        $totalNuevosProspectos = CrmProspecto::crmScope($vendedor_id)->where('created_at', '>=', now()->subDays(30))->count();

        $oportunidadesQuery = CrmOportunidad::crmScope($vendedor_id);
        $totalOportunidades = (clone $oportunidadesQuery)->count();
        $oportunidadesAbiertas = (clone $oportunidadesQuery)->where('estado', 'Abierta')->count();
        $montoTotalEmbudo = (clone $oportunidadesQuery)->where('estado', 'Abierta')->sum('monto_estimado');
        $montoGanado = (clone $oportunidadesQuery)->where('estado', 'Ganada')->sum('monto_estimado');

        $cotizacionesQuery = CrmCotizacion::crmScope($vendedor_id);
        $totalCotizaciones = (clone $cotizacionesQuery)->count();
        $totalCotizacionesMonto = (clone $cotizacionesQuery)->sum('total');
        $cotizacionesAprobadas = (clone $cotizacionesQuery)->whereIn('estado', ['Aprobada', 'Convertida'])->count();
        $montoCotizacionesAprobadas = (clone $cotizacionesQuery)->whereIn('estado', ['Aprobada', 'Convertida'])->sum('total');

        $actividadesPendientes = CrmActividad::crmScope($vendedor_id)->where('completada', false)->count();

        // Tasa de conversión
        $tasaConversion = $totalOportunidades > 0 ? round(($oportunidadesQuery->where('estado', 'Ganada')->count() / $totalOportunidades) * 100, 1) : 0;

        // Meta del mes
        $mesActual = date('n');
        $anioActual = date('Y');
        $metaQuery = CrmMetaVenta::where('mes', $mesActual)->where('anio', $anioActual);
        if ($vendedor_id && $vendedor_id !== 'all' && $vendedor_id !== 'equipo') {
            $metaQuery->where('user_id', $vendedor_id);
        }
        $montoMeta = $metaQuery->sum('monto_meta') ?: 0;
        $porcentajeMeta = $montoMeta > 0 ? min(100, round(($montoGanado / $montoMeta) * 100, 1)) : 0;

        return response()->json([
            'kpis' => [
                'totalProspectos' => $totalProspectos,
                'totalNuevosProspectos' => $totalNuevosProspectos,
                'totalOportunidades' => $totalOportunidades,
                'oportunidadesAbiertas' => $oportunidadesAbiertas,
                'montoTotalEmbudo' => $montoTotalEmbudo,
                'montoGanado' => $montoGanado,
                'totalCotizaciones' => $totalCotizaciones,
                'totalCotizacionesMonto' => $totalCotizacionesMonto,
                'cotizacionesAprobadas' => $cotizacionesAprobadas,
                'montoCotizacionesAprobadas' => $montoCotizacionesAprobadas,
                'actividadesPendientes' => $actividadesPendientes,
                'tasaConversion' => $tasaConversion,
                'montoMeta' => $montoMeta,
                'porcentajeMeta' => $porcentajeMeta
            ]
        ]);
    }

    public function getEmpresaConfig()
    {
        $config = \App\Ajustes::where('tipo', 'empresa')->pluck('valor', 'detalle')->toArray();

        $defaults = [
            'nombre' => 'EMPAQUES LUPA S.A.S.',
            'slogan' => 'Soluciones Integrales en Empaques e Impresión',
            'nit' => '900.123.456-7',
            'telefono' => '(601) 123 4567 / 310 123 4567',
            'email' => 'contacto@empaqueslupa.com',
            'direccion' => 'Calle Principal # 12-34, Bogotá',
            'logo' => 'img/LOGO-LUPA.jpg'
        ];

        foreach ($defaults as $key => $val) {
            if (!isset($config[$key]) || $config[$key] === '') {
                $config[$key] = $val;
            }
        }

        return response()->json(['config' => $config]);
    }

    public function saveEmpresaConfig(Request $request)
    {
        $fields = ['nombre', 'slogan', 'nit', 'telefono', 'email', 'direccion', 'logo'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                \App\Ajustes::updateOrCreate(
                    ['tipo' => 'empresa', 'detalle' => $field],
                    ['valor' => $request->input($field, ''), 'categoria' => 'Empresa']
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Configuración de la empresa guardada exitosamente']);
    }
}
