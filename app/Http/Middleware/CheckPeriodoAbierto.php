<?php

namespace App\Http\Middleware;

use Closure;
use App\PeriodoContable;
use App\Comprobante;
use App\CuentaPorPagar;
use App\Egresos;
use App\ReciboPago;

class CheckPeriodoAbierto
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 1. Check if the input date is in a closed period
        if ($request->has('fecha')) {
            if (PeriodoContable::isClosed($request->input('fecha'))) {
                return response()->json([
                    'error' => 'El periodo contable para esta fecha está cerrado. No se permiten registros ni modificaciones.'
                ], 403);
            }
        }

        // 2. Check existing records for PUT/DELETE requests or requests passing IDs
        $id = $request->route('id') ?: $request->input('id');
        $route = $request->path();

        // Detect model type based on route path
        $dateToCheck = null;
        if (!empty($id)) {
            if (strpos($route, 'comprobante') !== false || strpos($route, 'pedido') !== false) {
                $record = Comprobante::find($id);
                if ($record) $dateToCheck = $record->fecha;
            } elseif (strpos($route, 'cuentas-por-pagar') !== false || strpos($route, 'cxp') !== false) {
                $record = CuentaPorPagar::find($id);
                if ($record) $dateToCheck = $record->fecha;
            } elseif (strpos($route, 'egreso') !== false) {
                $record = Egresos::find($id);
                if ($record) $dateToCheck = $record->fecha;
            } elseif (strpos($route, 'recibo') !== false || strpos($route, 'pago') !== false) {
                $record = ReciboPago::find($id);
                if ($record) $dateToCheck = $record->fecha;
            }
        }

        if ($dateToCheck && PeriodoContable::isClosed($dateToCheck)) {
            return response()->json([
                'error' => 'El periodo contable original para este registro está cerrado. No se permite modificar ni eliminar.'
            ], 403);
        }

        return $next($request);
    }
}
