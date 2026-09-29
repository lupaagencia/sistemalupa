<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Ingresos;
use App\DetalleIngreso;

class IngresosController extends Controller
{
    public function index(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
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
                    'proveedores.nombre',
                    'users.usuario'
                )
                ->orderBy('ingresos.id', 'desc')->paginate(100);
        }
        else{
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
                    'proveedores.nombre',
                    'users.usuario'
                )
                ->where('ingresos.' . $criterio, 'like', '%' . $buscar . '%')->orderBy('ingresos.id', 'desc')->paginate(100);
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

        try{
            DB::beginTransaction();

            $mytime= Carbon::now('America/Bogota');

            $ingreso = new Ingreso();
            $ingreso->idproveedor = $request->idproveedor;
            $ingreso->idusuario = \Auth::user()->id;
            $ingreso->tipo_comprobante = $request->tipo_comprobante;
            $ingreso->serie_comprobante = $request->serie_comprobante;
            $ingreso->num_comprobante = $request->num_comprobante;
            $ingreso->fecha_hora = $mytime->toDateString();
            $ingreso->impuesto = $request->impuesto;
            $ingreso->total = $request->total;
            $ingreso->estado = 'Registrado';
            $ingreso->save();

            $detalles = $request->destalles;
            $costos=$request->costos;
            //Array de detalles
            //Recorro todos los elementos

            foreach($detalles as $ep=>$det)
            {
                $detalle = new DetalleIngreso();
                $detalle->ordentrabajo_id = $ingreso->id;
                $detalle->titulo = $det['titulo_detalle'];
                $detalle->valor = $det['valor_detalle'];
                $detalle->descripcion = $det['descripcion_detalle'];          
                $detalle->save();
            } 
            foreach($costos as $co=>$cos)
            {
                $costo=new costoProduccion();
                $costo->ordentrabajo_id=$ingreso->id;
                $costo->costois_id=$cos['id_insumo'];
                $costo->titulo=$cos['titulo_costo'];
                $costo->descripcion=$cos['descripcion_costo'];
                $costo->cantidad=$cos['cantidad_costo'];
                $costo->valor=$cos['valor_costo'];
                $costo->total=$cos['subtotal_costo'];
                $costo->save();
            }         

            DB::commit();
        } catch (Exception $e){
            DB::rollBack();
        }
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $ingreso = Ingreso::findOrFail($request->id);
        $ingreso->estado = 'Anulado';
        $ingreso->save();
    }
}