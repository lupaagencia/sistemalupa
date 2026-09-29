<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PagoProgramado;
use App\CuentaPorPagar;
use App\Proveedor;
use App\Cuenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagoProgramadoController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $categoria = $request->categoria;
        $frecuencia = $request->frecuencia;
        $estado = $request->estado;

        $query = PagoProgramado::with(['proveedor', 'cuenta']);

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('concepto', 'like', '%' . $buscar . '%')
                  ->orWhere('beneficiario', 'like', '%' . $buscar . '%')
                  ->orWhere('observaciones', 'like', '%' . $buscar . '%')
                  ->orWhereHas('proveedor', function($qp) use ($buscar) {
                      $qp->where('nombre', 'like', '%' . $buscar . '%');
                  });
            });
        }

        if (!empty($categoria)) {
            $query->where('categoria', $categoria);
        }

        if (!empty($frecuencia)) {
            $query->where('frecuencia', $frecuencia);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $pagos = $query->orderBy('proxima_fecha_pago', 'asc')->paginate(15);

        // KPIs calculation
        $today = Carbon::today()->format('Y-m-d');
        $in7days = Carbon::today()->addDays(7)->format('Y-m-d');

        $totalEstimado = PagoProgramado::where('estado', 'Activo')->sum('monto_estimado');
        $vencidosCount = PagoProgramado::where('estado', 'Activo')->where('proxima_fecha_pago', '<', $today)->count();
        $vencidosMonto = PagoProgramado::where('estado', 'Activo')->where('proxima_fecha_pago', '<', $today)->sum('monto_estimado');
        $porVencerCount = PagoProgramado::where('estado', 'Activo')->whereBetween('proxima_fecha_pago', [$today, $in7days])->count();
        $porVencerMonto = PagoProgramado::where('estado', 'Activo')->whereBetween('proxima_fecha_pago', [$today, $in7days])->sum('monto_estimado');
        $activosCount = PagoProgramado::where('estado', 'Activo')->count();

        $proveedores = Proveedor::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
        $cuentas = Cuenta::select('id', 'codigo', 'nombre')->orderBy('codigo', 'asc')->get();

        return [
            'pagination' => [
                'total'        => $pagos->total(),
                'current_page' => $pagos->currentPage(),
                'per_page'     => $pagos->perPage(),
                'last_page'    => $pagos->lastPage(),
                'from'         => $pagos->firstItem(),
                'to'           => $pagos->lastItem(),
            ],
            'pagos' => $pagos,
            'kpis' => [
                'total_estimado' => (float)$totalEstimado,
                'vencidos_count' => $vencidosCount,
                'vencidos_monto' => (float)$vencidosMonto,
                'por_vencer_count' => $porVencerCount,
                'por_vencer_monto' => (float)$porVencerMonto,
                'activos_count' => $activosCount
            ],
            'proveedores' => $proveedores,
            'cuentas' => $cuentas
        ];
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto_estimado' => 'required|numeric|min:0.01',
            'frecuencia' => 'required|string',
            'proxima_fecha_pago' => 'required|date'
        ]);

        $pago = new PagoProgramado();
        $pago->concepto = $request->concepto;
        $pago->categoria = $request->categoria ?? 'Préstamo / Crédito';
        $pago->proveedor_id = $request->proveedor_id ?: null;
        $pago->beneficiario = $request->beneficiario ?: null;
        $pago->monto_estimado = $request->monto_estimado;
        $pago->frecuencia = $request->frecuencia;
        $pago->proxima_fecha_pago = $request->proxima_fecha_pago;
        $pago->recordatorio_dias = $request->recordatorio_dias ?? 5;
        $pago->cuenta_id = $request->cuenta_id ?: null;
        $pago->estado = $request->estado ?? 'Activo';
        $pago->observaciones = $request->observaciones;
        $pago->save();

        return response()->json(['status' => true, 'message' => 'Pago programado registrado correctamente.']);
    }

    public function update(Request $request, $id)
    {
        if (!$request->ajax()) return redirect('/');

        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto_estimado' => 'required|numeric|min:0.01',
            'frecuencia' => 'required|string',
            'proxima_fecha_pago' => 'required|date'
        ]);

        $pago = PagoProgramado::findOrFail($id);
        $pago->concepto = $request->concepto;
        $pago->categoria = $request->categoria ?? 'Préstamo / Crédito';
        $pago->proveedor_id = $request->proveedor_id ?: null;
        $pago->beneficiario = $request->beneficiario ?: null;
        $pago->monto_estimado = $request->monto_estimado;
        $pago->frecuencia = $request->frecuencia;
        $pago->proxima_fecha_pago = $request->proxima_fecha_pago;
        $pago->recordatorio_dias = $request->recordatorio_dias ?? 5;
        $pago->cuenta_id = $request->cuenta_id ?: null;
        $pago->estado = $request->estado ?? 'Activo';
        $pago->observaciones = $request->observaciones;
        $pago->save();

        return response()->json(['status' => true, 'message' => 'Pago programado actualizado correctamente.']);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->ajax()) return redirect('/');

        $pago = PagoProgramado::findOrFail($id);
        $pago->delete();

        return response()->json(['status' => true, 'message' => 'Pago programado eliminado correctamente.']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        if (!$request->ajax()) return redirect('/');

        $pago = PagoProgramado::findOrFail($id);
        $pago->estado = $request->estado;
        $pago->save();

        return response()->json(['status' => true, 'message' => 'Estado actualizado a ' . $pago->estado]);
    }

    public function generarCuentaPorPagar(Request $request, $id)
    {
        if (!$request->ajax()) return redirect('/');

        $pago = PagoProgramado::findOrFail($id);

        $montoReal = $request->monto ? (float)$request->monto : (float)$pago->monto_estimado;
        $numeroFactura = $request->numero_factura ?: ('PROG-' . $pago->id . '-' . date('Ymd'));
        $fechaVencimiento = $request->fecha_vencimiento ?: $pago->proxima_fecha_pago;

        DB::beginTransaction();
        try {
            // 1. Crear la Cuenta por Pagar
            $cuenta = new CuentaPorPagar();
            $cuenta->proveedor_id = $pago->proveedor_id;
            $cuenta->beneficiario = $pago->beneficiario;
            $cuenta->numero_factura = $numeroFactura;
            $cuenta->descripcion = $pago->concepto . ' (Programado: ' . $pago->frecuencia . ')';
            $cuenta->monto = $montoReal;
            $cuenta->saldo = $montoReal;
            $cuenta->estado = 'Pendiente';
            $cuenta->fecha = date('Y-m-d');
            $cuenta->fecha_vencimiento = $fechaVencimiento;
            $cuenta->cuenta_id = $pago->cuenta_id;
            $cuenta->save();

            // 2. Avanzar la próxima fecha de pago del Pago Programado según su frecuencia
            $fechaActual = Carbon::parse($pago->proxima_fecha_pago);

            switch (strtolower($pago->frecuencia)) {
                case 'mensual':
                    $pago->proxima_fecha_pago = $fechaActual->addMonth()->format('Y-m-d');
                    break;
                case 'bimensual':
                    $pago->proxima_fecha_pago = $fechaActual->addMonths(2)->format('Y-m-d');
                    break;
                case 'cuatrimestral':
                    $pago->proxima_fecha_pago = $fechaActual->addMonths(4)->format('Y-m-d');
                    break;
                case 'semestral':
                    $pago->proxima_fecha_pago = $fechaActual->addMonths(6)->format('Y-m-d');
                    break;
                case 'anual':
                    $pago->proxima_fecha_pago = $fechaActual->addYear()->format('Y-m-d');
                    break;
                case 'única vez':
                case 'unica vez':
                    $pago->estado = 'Finalizado';
                    break;
                default:
                    $pago->proxima_fecha_pago = $fechaActual->addMonth()->format('Y-m-d');
                    break;
            }

            $pago->save();

            DB::commit();
            return response()->json([
                'status' => true, 
                'message' => 'Cuenta por pagar #' . $cuenta->id . ' generada exitosamente. Próxima fecha calculada: ' . $pago->proxima_fecha_pago
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error al generar la cuenta: ' . $e->getMessage()], 500);
        }
    }
}
