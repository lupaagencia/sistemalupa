<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cuenta;
use App\AsientoDetalle;
use Illuminate\Support\Facades\DB;

class CuentaController extends Controller
{
    /**
     * Display a listing of the accounts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $tipo = $request->tipo;
        $es_detalle = $request->es_detalle;

        $query = Cuenta::with(['padre'])->orderBy('codigo', 'asc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', $buscar . '%')
                  ->orWhere('nombre', 'like', '%' . $buscar . '%');
            });
        }

        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        if ($request->has('es_detalle')) {
            $query->where('es_detalle', $es_detalle);
        }

        $cuentas = $query->get();

        return response()->json([
            'cuentas' => $cuentas
        ]);
    }

    /**
     * Store a newly created account in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'codigo' => 'required|string|max:20|unique:cuentas,codigo',
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|in:Activo,Pasivo,Patrimonio,Ingreso,Gasto,Costo',
            'naturaleza' => 'required|in:Debito,Credito',
            'es_detalle' => 'required|boolean',
            'padre_id' => 'nullable|exists:cuentas,id'
        ]);

        DB::beginTransaction();
        try {
            $cuenta = new Cuenta();
            $cuenta->codigo = $request->codigo;
            $cuenta->nombre = $request->nombre;
            $cuenta->tipo = $request->tipo;
            $cuenta->naturaleza = $request->naturaleza;
            $cuenta->es_detalle = $request->es_detalle;
            $cuenta->padre_id = $request->padre_id;
            $cuenta->save();

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar la cuenta contable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified account in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'codigo' => 'required|string|max:20|unique:cuentas,codigo,' . $id,
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|in:Activo,Pasivo,Patrimonio,Ingreso,Gasto,Costo',
            'naturaleza' => 'required|in:Debito,Credito',
            'es_detalle' => 'required|boolean',
            'padre_id' => 'nullable|exists:cuentas,id'
        ]);

        $cuenta = Cuenta::findOrFail($id);

        DB::beginTransaction();
        try {
            $cuenta->codigo = $request->codigo;
            $cuenta->nombre = $request->nombre;
            $cuenta->tipo = $request->tipo;
            $cuenta->naturaleza = $request->naturaleza;
            $cuenta->es_detalle = $request->es_detalle;
            $cuenta->padre_id = $request->padre_id;
            $cuenta->save();

            DB::commit();
            return response()->json(['success' => true, 'cuenta' => $cuenta]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar la cuenta contable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified account from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cuenta = Cuenta::findOrFail($id);

        // Check if there are subaccounts
        $subcuentasCount = Cuenta::where('padre_id', $id)->count();
        if ($subcuentasCount > 0) {
            return response()->json([
                'error' => 'No se puede eliminar la cuenta porque tiene subcuentas hijas asociadas en el PUC.'
            ], 422);
        }

        // Check if there are accounting details/movements registered
        $asientosCount = AsientoDetalle::where('cuenta_id', $id)->count();
        if ($asientosCount > 0) {
            return response()->json([
                'error' => 'No se puede eliminar la cuenta porque posee movimientos contables (asientos) registrados.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cuenta->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la cuenta contable: ' . $e->getMessage()], 500);
        }
    }
}
