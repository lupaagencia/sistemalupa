<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Ingreso;
use App\DetalleIngreso;
use App\Articulo;
use App\CuentaPorPagar;
use App\Http\Controllers\CuentasPorPagarController;

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

            // Find/create the raw material inventory account 140505
            $cuentaMateriaPrima = \App\Cuenta::where('codigo', '140505')->first();
            if (!$cuentaMateriaPrima) {
                $parent = \App\Cuenta::where('codigo', '1405')->first() ?: \App\Cuenta::where('codigo', '14')->first();
                $cuentaMateriaPrima = \App\Cuenta::create([
                    'codigo' => '140505',
                    'nombre' => 'Inventario de Materias Primas',
                    'tipo' => 'Activo',
                    'naturaleza' => 'Débito',
                    'es_detalle' => 1,
                    'padre_id' => $parent ? $parent->id : null
                ]);
            }

            $impuestoPct = (float)($ingreso->impuesto ?? 0);
            $ivaAmount = 0.00;
            if ($impuestoPct > 0) {
                $subtotal = round($ingreso->total / (1 + ($impuestoPct / 100)), 2);
                $ivaAmount = round($ingreso->total - $subtotal, 2);
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
                $cuenta->cuenta_id = $cuentaMateriaPrima->id;
                $cuenta->iva = $ivaAmount;
                $cuenta->save();

                // Contabilizar CXP
                app(CuentasPorPagarController::class)->contabilizarCuenta($cuenta);
            } else {
                // Si es a Contado, contabilizar entrada de almacén directamente
                $this->contabilizarIngreso($ingreso);
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
                    // Si no tiene abonos, podemos borrar la cuenta por pagar y su comprobante
                    $comp = $cuenta->comprobante;
                    $cuenta->delete();
                    if ($comp) {
                        $comp->delete();
                    }
                }
            } else {
                // Si es a contado, eliminar su comprobante contable asociado
                $comp = $ingreso->comprobante;
                if ($comp) {
                    $comp->delete();
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

    private function contabilizarIngreso($ingreso)
    {
        $comp = $ingreso->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Egreso')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Egreso';
            $comp->numero = $ultimoNumero + 1;
        }

        $comp->fecha = Carbon::parse($ingreso->fecha_hora)->toDateString();
        $comp->descripcion = "Compra Contado No. " . $ingreso->id . " - " . $ingreso->tipo_comprobante . " #" . $ingreso->num_comprobante;
        $comp->user_id = \Auth::id() ?: $ingreso->idusuario;
        $comp->save();

        $ingreso->comprobante_id = $comp->id;
        $ingreso->saveQuietly();

        $comp->detalles()->delete();

        // Resolve third party
        $terceroId = null;
        if ($ingreso->idproveedor) {
            $terceroId = $ingreso->idproveedor;
        }

        $cuentaMateriaPrima = \App\Cuenta::where('codigo', '140505')->first();
        if (!$cuentaMateriaPrima) {
            $parent = \App\Cuenta::where('codigo', '1405')->first() ?: \App\Cuenta::where('codigo', '14')->first();
            $cuentaMateriaPrima = \App\Cuenta::create([
                'codigo' => '140505',
                'nombre' => 'Inventario de Materias Primas',
                'tipo' => 'Activo',
                'naturaleza' => 'Débito',
                'es_detalle' => 1,
                'padre_id' => $parent ? $parent->id : null
            ]);
        }

        // Resolving Credit Account: Contado purchases credit Banks (111005) or Caja
        $cuentaCredito = \App\Cuenta::where('codigo', '111005')->first();
        if (!$cuentaCredito) {
            $cuentaCredito = \App\Cuenta::where('codigo', '110505')->first() ?: \App\Cuenta::where('codigo', '110510')->first();
        }

        if ($cuentaMateriaPrima && $cuentaCredito) {
            $impuestoPct = (float)($ingreso->impuesto ?? 0);
            if ($impuestoPct > 0) {
                $subtotal = round($ingreso->total / (1 + ($impuestoPct / 100)), 2);
                $iva = round($ingreso->total - $subtotal, 2);

                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaMateriaPrima->id,
                    'tercero_id' => $terceroId,
                    'debe' => $subtotal,
                    'haber' => 0.00,
                    'referencia' => 'Compra Contado Subtotal'
                ]);

                $cuentaIva = \App\Cuenta::where('codigo', '240810')->first();
                if (!$cuentaIva) {
                    $cuentaIva = \App\Cuenta::create([
                        'codigo' => '240810',
                        'nombre' => 'Impuesto sobre las Ventas Descontable (IVA)',
                        'tipo' => 'Activo',
                        'naturaleza' => 'Débito',
                        'es_detalle' => 1
                    ]);
                }

                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaIva->id,
                    'tercero_id' => $terceroId,
                    'debe' => $iva,
                    'haber' => 0.00,
                    'referencia' => 'IVA Descontable Compra'
                ]);
            } else {
                \App\AsientoDetalle::create([
                    'comprobante_id' => $comp->id,
                    'cuenta_id' => $cuentaMateriaPrima->id,
                    'tercero_id' => $terceroId,
                    'debe' => $ingreso->total,
                    'haber' => 0.00,
                    'referencia' => 'Compra Contado'
                ]);
            }

            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCredito->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $ingreso->total,
                'referencia' => 'Pago Compra Contado'
            ]);
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