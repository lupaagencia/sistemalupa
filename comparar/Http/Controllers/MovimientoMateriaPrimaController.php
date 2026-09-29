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

        // Registrar el movimiento
        MovimientoMateriaPrima::create([
            'inventarios_materia_prima_id' => $papel->id,
            'proveedores_id' => $costois->idproveedor ?? 0, // Fallback if idproveedor is null
            'tipo' => $datos->tipo,
            'cantidad' => $datos->cantidad,
            'costo_unitario' => 0,
            'costo_total' => 0,

        ]);

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
        return redirect()->route('MovimientoMateriaPrimas.index')->with('success', 'MovimientoMateriaPrima actualizado exitosamente.');
    }

    public function destroy(MovimientoMateriaPrima $MovimientoMateriaPrima)
    {
        $MovimientoMateriaPrima->delete();
        return redirect()->route('MovimientoMateriaPrimas.index')->with('success', 'MovimientoMateriaPrima eliminado exitosamente.');
    }
}
