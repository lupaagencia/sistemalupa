<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Ingreso;
use App\DetalleIngreso;
use App\Articulo;
use App\CuentaPorPagar;

class IngresoController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar == '') {
            $ingresos = Ingreso::join('proveedores', 'ingresos.idproveedor', '=', 'proveedores.id')
                ->join('users', 'ingresos.idusuario', '=', 'users.id')
                ->select(
                    'ingresos.id',
                    'ingresos.tipo_comprobante',
                    'ingresos.serie_comprobante',
                    'ingresos.num_comprobante',
                    'ingresos.fecha_hora',
                    'ingresos.impuesto',
                    'ingresos.total',
                    'ingresos.estado',
                    'ingresos.forma_pago',
                    'ingresos.dias_credito',
                    'proveedores.nombre as proveedor_nombre',
                    'users.usuario'
                )
                ->orderBy('ingresos.id', 'desc')->paginate(15);
        } else {
            $ingresos = Ingreso::join('proveedores', 'ingresos.idproveedor', '=', 'proveedores.id')
                ->join('users', 'ingresos.idusuario', '=', 'users.id')
                ->select(
                    'ingresos.id',
                    'ingresos.tipo_comprobante',
                    'ingresos.serie_comprobante',
                    'ingresos.num_comprobante',
                    'ingresos.fecha_hora',
                    'ingresos.impuesto',
                    'ingresos.total',
                    'ingresos.estado',
                    'ingresos.forma_pago',
                    'ingresos.dias_credito',
                    'proveedores.nombre as proveedor_nombre',
                    'users.usuario'
                )
                ->where('ingresos.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('ingresos.id', 'desc')->paginate(15);
        }
        
        return [
            'pagination' => [
                'total'        => $ingresos->total(),
                'current_page' => $ingresos->currentPage(),
                'per_page'     => $ingresos->perPage(),
                'last_page'    => $ingresos->lastPage(),
                'from'         => $ingresos->firstItem(),
                'to'           => $ingresos->lastItem(),
            ],
            'ingresos' => $ingresos
        ];
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $this->validate($request, [
            'idproveedor' => 'required|exists:proveedores,id',
            'tipo_comprobante' => 'required|string|max:20',
            'num_comprobante' => 'required|string|max:10',
            'total' => 'required|numeric|min:0.01',
            'forma_pago' => 'required|string|in:Contado,Crédito',
            'dias_credito' => 'required_if:forma_pago,Crédito|nullable|integer|min:1',
            'detalles' => 'required|array|min:1'
        ]);

        // Validar el cupo de crédito disponible del proveedor si no se aceptó sobrecupo
        $proveedor = \App\Proveedor::findOrFail($request->idproveedor);
        if ($request->forma_pago === 'Crédito' && $proveedor->cupo_credito > 0 && !$request->input('aceptar_sobrecupo')) {
            $saldo_pendiente = CuentaPorPagar::where('proveedor_id', $request->idproveedor)
                ->where('estado', '!=', 'Pagado')
                ->sum('saldo');
            if (($saldo_pendiente + $request->total) > $proveedor->cupo_credito) {
                return response()->json([
                    'error' => 'Cupo de crédito excedido. El saldo pendiente del proveedor ($' . number_format($saldo_pendiente, 2) . ') más el total de esta compra ($' . number_format($request->total, 2) . ') supera el cupo de crédito asignado ($' . number_format($proveedor->cupo_credito, 2) . '). Debe abonar a las cuentas pendientes antes de realizar un nuevo pedido a crédito.'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $mytime = Carbon::now('America/Bogota');

            $ingreso = new Ingreso();
            $ingreso->idproveedor = $request->idproveedor;
            $ingreso->idusuario = \Auth::user()->id;
            $ingreso->tipo_comprobante = $request->tipo_comprobante;
            $ingreso->serie_comprobante = $request->serie_comprobante ?: '';
            $ingreso->num_comprobante = $request->num_comprobante;
            $ingreso->fecha_hora = $mytime->toDateTimeString();
            $ingreso->impuesto = $request->impuesto ?: 0;
            $ingreso->total = $request->total;
            $ingreso->estado = 'Registrado';
            $ingreso->forma_pago = $request->forma_pago;
            $ingreso->dias_credito = $request->forma_pago === 'Crédito' ? $request->dias_credito : null;
            $ingreso->save();

            $detalles = $request->detalles;

            foreach ($detalles as $det) {
                $detalle = new DetalleIngreso();
                $detalle->idingreso = $ingreso->id;
                $detalle->idarticulo = $det['idarticulo'];
                $detalle->cantidad = $det['cantidad'];
                $detalle->precio = $det['precio'];
                $detalle->save();

                // Aumentar stock de los artículos
                $articulo = Articulo::findOrFail($det['idarticulo']);
                $articulo->stock += $det['cantidad'];
                $articulo->save();
            }

            // Si es a Crédito, crear automáticamente la cuenta por pagar
            if ($ingreso->forma_pago === 'Crédito') {
                $fechaVencimiento = Carbon::parse($ingreso->fecha_hora)->addDays((int)$ingreso->dias_credito)->toDateString();
                
                $cuenta = new CuentaPorPagar();
                $cuenta->proveedor_id = $ingreso->idproveedor;
                $cuenta->ingreso_id = $ingreso->id;
                $cuenta->numero_factura = $ingreso->num_comprobante;
                $cuenta->descripcion = 'Factura de Compra ' . $ingreso->tipo_comprobante . ' #' . $ingreso->num_comprobante . ' - Vence: ' . $fechaVencimiento;
                $cuenta->cantidad = 1;
                $cuenta->valor_unitario = $ingreso->total;
                $cuenta->monto = $ingreso->total;
                $cuenta->saldo = $ingreso->total;
                $cuenta->estado = 'Pendiente';
                $cuenta->fecha = $mytime->toDateString();
                $cuenta->fecha_vencimiento = $fechaVencimiento;
                $cuenta->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'id' => $ingreso->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar la factura de compra: ' . $e->getMessage()], 500);
        }
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        DB::beginTransaction();
        try {
            $ingreso = Ingreso::findOrFail($request->id);
            
            // Si la compra fue a crédito, verificar si tiene abonos
            if ($ingreso->forma_pago === 'Crédito') {
                $cuenta = CuentaPorPagar::where('ingreso_id', $ingreso->id)->first();
                if ($cuenta) {
                    if ($cuenta->abonos()->count() > 0) {
                        return response()->json(['error' => 'No se puede anular esta factura de compra porque ya tiene abonos registrados en cuentas por pagar.'], 422);
                    }
                    // Si no tiene abonos, podemos borrar la cuenta por pagar
                    $cuenta->delete();
                }
            }

            $ingreso->estado = 'Anulado';
            $ingreso->save();

            // Descontar del stock de los artículos
            $detalles = DetalleIngreso::where('idingreso', $ingreso->id)->get();
            foreach ($detalles as $det) {
                $articulo = Articulo::findOrFail($det->idarticulo);
                $articulo->stock -= $det->cantidad;
                $articulo->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al anular la factura de compra: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerCabecera(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        $id = $request->id;
        $ingreso = Ingreso::join('proveedores', 'ingresos.idproveedor', '=', 'proveedores.id')
            ->join('users', 'ingresos.idusuario', '=', 'users.id')
            ->select(
                'ingresos.id',
                'ingresos.tipo_comprobante',
                'ingresos.serie_comprobante',
                'ingresos.num_comprobante',
                'ingresos.fecha_hora',
                'ingresos.impuesto',
                'ingresos.total',
                'ingresos.estado',
                'ingresos.forma_pago',
                'ingresos.dias_credito',
                'proveedores.nombre as proveedor_nombre',
                'users.usuario'
            )
            ->where('ingresos.id', '=', $id)
            ->first();
            
        return ['ingreso' => $ingreso];
    }

    public function obtenerDetalles(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        $id = $request->id;
        $detalles = DetalleIngreso::join('articulos', 'detalle_ingresos.idarticulo', '=', 'articulos.id')
            ->select(
                'detalle_ingresos.cantidad',
                'detalle_ingresos.precio',
                'articulos.nombre as articulo'
            )
            ->where('detalle_ingresos.idingreso', '=', $id)
            ->get();
            
        return ['detalles' => $detalles];
    }
}