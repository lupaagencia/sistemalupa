<?php

namespace App\Http\Controllers;
use App\InventariosMateriaPrima;
use App\MovimientoMateriaPrima;
use App\Costois;

use Illuminate\Http\Request;

class MovimientoMateriaPrimaController extends Controller
{
    public function index()
    {
        $movimiento = MovimientoMateriaPrimaMateriaPrima::with('material')->get();
        return $movimiento;
    }

    public function create()
    {
        return view('MovimientoMateriaPrimas.create');
    }
    public static function registrarMovimiento($datos)
    {

        $papel = InventariosMateriaPrima::where('costois_id', $datos->costois_id)->get();
        // Ajustar el stock
        $costois = Costois::find($datos->costois_id);

        if (!$costois) {
            // If ID is 0 or empty, it's expected for orders without paper.
            if (empty($datos->costois_id) || $datos->costois_id == 0) {
                return;
            }
            // If ID was provided but not found, that's a data consistency issue.
            \Log::warning("Insumo/Costo no encontrado para ID: " . $datos->costois_id . " - Movimiento omitido.");
            return;
        }

        if (count($papel) == 0) {
            $papel = new InventariosMateriaPrima();
            $papel->costois_id = $datos->costois_id;
            $papel->cantidad = 0;
            $papel->tipo = 'Materia Prima';
            $papel->estado = 'Disponible';
            $papel->referencia = $costois->nombre;
        }
        else {
            $papel = $papel[0];
        }
        if ($datos->tipo === 'entrada') {
            $papel->cantidad += $datos->cantidad;
        }
        elseif ($datos->tipo === 'salida') {
            $papel->cantidad -= $datos->cantidad;
        }
        $papel->save();

        $costoUnitario = (float)($costois->valor ?? 0);
        $costoTotal = $costoUnitario * $datos->cantidad;

        // Registrar el movimiento
        $movimiento = MovimientoMateriaPrima::create([
            'inventarios_materia_prima_id' => $papel->id,
            'proveedores_id' => $costois->idproveedor ?? 0, // Fallback if idproveedor is null
            'tipo' => $datos->tipo,
            'cantidad' => $datos->cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
        ]);

        if ($movimiento->tipo === 'salida') {
            (new self)->contabilizarConsumo($movimiento);
        }

    }
    public function store(Request $request)
    {
        $papel = InventariosMateriaPrima::find($request->inventarios_materia_prima_id);
        // Ajustar el stock
        $costois = Costois::find($request->costois_id);

        if (!$costois) {
            return response()->json(['error' => 'Insumo/Costo no encontrado'], 404);
        }

        if (!$papel) {
            $papel = new InventariosMateriaPrima();
            $papel->costois_id = $request->costois_id;
            $papel->cantidad = 0;
            $papel->tipo = 'Materia Prima';
            $papel->estado = 'Disponible';
            $papel->referencia = $costois->nombre;
        }
        if ($request->tipo === 'entrada') {
            $papel->cantidad += (int)$request->cantidad;
        }
        elseif ($request->tipo === 'salida') {
            $papel->cantidad -= (int)$request->cantidad;
        }
        $papel->save();

        if ($request->id == 0) {
            $movimiento = new MovimientoMateriaPrima();
        }
        else {
            $movimiento = MovimientoMateriaPrima::find($request->id);
        }
        $movimiento->inventarios_materia_prima_id = $papel->id;
        $movimiento->proveedores_id = $costois->idproveedor ?? 0;
        $movimiento->tipo = $request->tipo;
        $movimiento->cantidad = $request->cantidad;
        $movimiento->costo_unitario = $request->costo_unitario;
        $movimiento->costo_total = $request->costo_total;
        $movimiento->save();

        if ($movimiento->tipo === 'salida') {
            $this->contabilizarConsumo($movimiento);
        }
        return $movimiento;


    }

    public function show(MovimientoMateriaPrima $MovimientoMateriaPrima)
    {
        return view('MovimientoMateriaPrimas.show', compact('MovimientoMateriaPrima'));
    }

    public function edit(MovimientoMateriaPrima $MovimientoMateriaPrima)
    {
        return view('MovimientoMateriaPrimas.edit', compact('MovimientoMateriaPrima'));
    }

    public function update(Request $request, MovimientoMateriaPrima $MovimientoMateriaPrima)
    {
        $request->validate([
            'cantidad' => 'required|integer',
            'costo_total' => 'required|numeric',
        ]);

        $MovimientoMateriaPrima->update($request->all());
        if ($MovimientoMateriaPrima->tipo === 'salida') {
            $this->contabilizarConsumo($MovimientoMateriaPrima);
        }
        return redirect()->route('MovimientoMateriaPrimas.index')->with('success', 'MovimientoMateriaPrima actualizado exitosamente.');
    }

    public function destroy(MovimientoMateriaPrima $MovimientoMateriaPrima)
    {
        $comp = $MovimientoMateriaPrima->comprobante;
        $MovimientoMateriaPrima->delete();
        if ($comp) {
            $comp->delete();
        }
        return redirect()->route('MovimientoMateriaPrimas.index')->with('success', 'MovimientoMateriaPrima eliminado exitosamente.');
    }

    public function contabilizarConsumo($movimiento)
    {
        // Only journalize outputs (consumptions)
        if ($movimiento->tipo !== 'salida') {
            return;
        }

        // If costo_total is 0, let's try to calculate it from Costois
        $costoTotal = (float)$movimiento->costo_total;
        if ($costoTotal <= 0) {
            $inventario = $movimiento->inventario;
            if ($inventario) {
                $costois = Costois::find($inventario->costois_id);
                if ($costois) {
                    $costoUnitario = (float)($costois->valor ?? 0);
                    $costoTotal = round($costoUnitario * $movimiento->cantidad, 2);
                    
                    // Update movement record with calculated costs
                    $movimiento->costo_unitario = $costoUnitario;
                    $movimiento->costo_total = $costoTotal;
                    $movimiento->saveQuietly();
                }
            }
        }

        if ($costoTotal <= 0) {
            // Cannot journalize 0 value entry
            return;
        }

        $comp = $movimiento->comprobante;
        if (!$comp) {
            $ultimoNumero = \App\ComprobanteContable::where('tipo', 'Diario')->max('numero') ?: 0;
            $comp = new \App\ComprobanteContable();
            $comp->tipo = 'Diario';
            $comp->numero = $ultimoNumero + 1;
        }

        $comp->fecha = $movimiento->created_at ? $movimiento->created_at->toDateString() : date('Y-m-d');
        $comp->descripcion = "Consumo Materia Prima - Movimiento No. " . $movimiento->id;
        $comp->user_id = \Auth::id() ?: 1;
        $comp->save();

        $movimiento->comprobante_id = $comp->id;
        $movimiento->saveQuietly();

        $comp->detalles()->delete();

        // 1. Debit Account 613505 (Costo de Materia Prima e Insumos Directos)
        $cuentaCosto = \App\Cuenta::where('codigo', '613505')->first();
        if (!$cuentaCosto) {
            $parent = \App\Cuenta::where('codigo', '6135')->first() ?: \App\Cuenta::where('codigo', '61')->first();
            $cuentaCosto = \App\Cuenta::create([
                'codigo' => '613505',
                'nombre' => 'Costo de Materia Prima e Insumos Directos',
                'tipo' => 'Costo de Ventas',
                'naturaleza' => 'Débito',
                'es_detalle' => 1,
                'padre_id' => $parent ? $parent->id : null
            ]);
        }

        // 2. Credit Account 140505 (Inventario de Materias Primas)
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

        $terceroId = null;
        if ($movimiento->proveedores_id) {
            $terceroId = $movimiento->proveedores_id;
        }

        if ($cuentaCosto && $cuentaMateriaPrima) {
            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaCosto->id,
                'tercero_id' => $terceroId,
                'debe' => $costoTotal,
                'haber' => 0.00,
                'referencia' => 'Consumo MP Mov No. ' . $movimiento->id
            ]);

            \App\AsientoDetalle::create([
                'comprobante_id' => $comp->id,
                'cuenta_id' => $cuentaMateriaPrima->id,
                'tercero_id' => $terceroId,
                'debe' => 0.00,
                'haber' => $costoTotal,
                'referencia' => 'Consumo MP Mov No. ' . $movimiento->id
            ]);
        }
    }
}
