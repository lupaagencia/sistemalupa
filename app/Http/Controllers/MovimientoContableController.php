<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ComprobanteContable;
use App\AsientoDetalle;
use Illuminate\Support\Facades\DB;

class MovimientoContableController extends Controller
{
    /**
     * Display a listing of the accounting journals (Comprobantes) and details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $tipo = $request->tipo;
        $buscar = $request->buscar;

        $query = ComprobanteContable::with(['detalles.cuenta', 'detalles.tercero', 'user'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }

        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhere('numero', 'like', $buscar . '%')
                  ->orWhereHas('detalles.cuenta', function ($qCuenta) use ($buscar) {
                      $qCuenta->where('codigo', 'like', $buscar . '%')
                              ->orWhere('nombre', 'like', '%' . $buscar . '%');
                  })
                  ->orWhereHas('detalles.tercero', function ($qTercero) use ($buscar) {
                      $qTercero->where('nombre', 'like', '%' . $buscar . '%');
                  });
            });
        }

        // Pagination or simple list? Since accounting ledgers can be large, let's paginate
        $perPage = $request->input('per_page', 15);
        $comprobantes = $query->paginate($perPage);

        // Calculate totals for the current query (without pagination limit) to show in summary cards
        $totalsQuery = ComprobanteContable::query();
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $totalsQuery->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        if (!empty($tipo)) {
            $totalsQuery->where('tipo', $tipo);
        }
        if (!empty($buscar)) {
            $totalsQuery->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhere('numero', 'like', $buscar . '%')
                  ->orWhereHas('detalles.cuenta', function ($qCuenta) use ($buscar) {
                      $qCuenta->where('codigo', 'like', $buscar . '%')
                              ->orWhere('nombre', 'like', '%' . $buscar . '%');
                  })
                  ->orWhereHas('detalles.tercero', function ($qTercero) use ($buscar) {
                      $qTercero->where('nombre', 'like', '%' . $buscar . '%');
                  });
            });
        }

        $comprobantesIds = $totalsQuery->pluck('id');
        
        $totalDebe = AsientoDetalle::whereIn('comprobante_id', $comprobantesIds)->sum('debe');
        $totalHaber = AsientoDetalle::whereIn('comprobante_id', $comprobantesIds)->sum('haber');
        $comprobantesCount = $comprobantesIds->count();

        return response()->json([
            'comprobantes' => $comprobantes,
            'summary' => [
                'total_debe' => (float)$totalDebe,
                'total_haber' => (float)$totalHaber,
                'comprobantes_count' => $comprobantesCount,
                'diferencia' => abs((float)$totalDebe - (float)$totalHaber)
            ]
        ]);
    }
}
