<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Proveedor;



class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar == '') {
            $personas = Proveedor::select('*')
                ->orderBy('id', 'desc')->paginate(20);
        } else {
            $personas = Proveedor::select('*')
                ->where($criterio, 'like', '%' . $buscar . '%')
                ->orderBy('id', 'desc')->paginate(20);
        }
        

        return [
            'pagination' => [
                'total'        => $personas->total(),
                'current_page' => $personas->currentPage(),
                'per_page'     => $personas->perPage(),
                'last_page'    => $personas->lastPage(),
                'from'         => $personas->firstItem(),
                'to'           => $personas->lastItem(),
            ],
            'personas' => $personas
        ];
    }
    public function selectProveedor(Request $request){
        if (!$request->ajax()) return redirect('/');

        $filtro = $request->filtro;
        $proveedores = Proveedor::where('nombre', 'like', '%' . $filtro . '%')
            ->orWhere('num_documento', 'like', '%' . $filtro . '%')
            ->select('id', 'nombre', 'num_documento')
            ->orderBy('nombre', 'asc')->get();

        return ['proveedores' => $proveedores];
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        try {
            DB::beginTransaction();

            $proveedor = new Proveedor();
            $proveedor->nombre = $request->nombre;
            $proveedor->tipo_documento = $request->tipo_documento;
            $proveedor->num_documento = $request->num_documento;
            $proveedor->direccion = $request->direccion;
            $proveedor->telefono = $request->telefono;
            $proveedor->email = $request->email;
            $proveedor->contacto = $request->contacto;
            $proveedor->telefono_contacto = $request->telefono_contacto;
            $proveedor->cupo_credito = $request->cupo_credito ?: 0.00;
            $proveedor->save();

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
        }
    }

    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        try {
            DB::beginTransaction();

            //Buscar primero el proveedor a modificar
            $proveedor = Proveedor::findOrFail($request->id);

            $proveedor->nombre = $request->nombre;
            $proveedor->tipo_documento = $request->tipo_documento;
            $proveedor->num_documento = $request->num_documento;
            $proveedor->direccion = $request->direccion;
            $proveedor->telefono = $request->telefono;
            $proveedor->email = $request->email;
            $proveedor->contacto = $request->contacto;
            $proveedor->telefono_contacto = $request->telefono_contacto;
            $proveedor->cupo_credito = $request->cupo_credito ?: 0.00;
            $proveedor->save();

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
        }
    }

    public function obtenerEstadoCredito(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        $id = $request->id;
        $proveedor = Proveedor::findOrFail($id);
        
        // Sum outstanding balance (saldo) of all active accounts payable
        $saldo_pendiente = \App\CuentaPorPagar::where('proveedor_id', $id)
            ->where('estado', '!=', 'Pagado')
            ->sum('saldo');
            
        return [
            'cupo_credito' => (float)$proveedor->cupo_credito,
            'saldo_pendiente' => (float)$saldo_pendiente,
            'cupo_disponible' => (float)max(0, $proveedor->cupo_credito - $saldo_pendiente)
        ];
    }

    public function eliminar(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar proveedores.'], 403);
        }
        if (!$request->ajax()) return redirect('/');

        try {
            $id = $request->id;
            $proveedor = Proveedor::findOrFail($id);

            // Verificar si el proveedor tiene registros asociados en cuentas por pagar o compras
            $tieneCuentas = \App\CuentaPorPagar::where('proveedor_id', $id)->exists();
            $tieneIngresos = DB::table('ingresos')->where('idproveedor', $id)->exists();

            if ($tieneCuentas || $tieneIngresos) {
                return response()->json([
                    'error' => 'No se puede eliminar el proveedor porque tiene cuentas por pagar o compras registradas asociadas.'
                ], 422);
            }

            $proveedor->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }
}
