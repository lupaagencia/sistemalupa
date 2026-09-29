<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use App\TipoProducto;
use App\AtributoTienda;
use App\Atributo;
use App\CostoArticulo;
use App\Costois;
use App\OpcionAtributo;
use App\TipoProductoProceso;

class TipoProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tipoproducto = TipoProducto::orderBy('orden', 'asc')->get();
        foreach ($tipoproducto as $tipo) {
            $tipo->costos;
            $tipo->tipo_cantidad = json_decode($tipo->tipo_cantidad);
            $tipo->rangos = json_decode($tipo->rangos);
            $tipo->atributos;
            $tipo->atributosTienda;
            $tipo->procesos;

            if (count($tipo->costos) != 0) {
                foreach ($tipo->costos as $c) {
                    $c->costois;
                }
            }

            foreach ($tipo->atributos as $a) {
                $a->atributoEdit = 0;
                $a->opciones;
                foreach ($a->opciones as $o) {
                    $o->editOpcion = 0;
                    $o->costoop;
                    if (count($o->costoop) != 0) {
                        foreach ($o->costoop as $co) {
                            $co->costois;
                        }
                    }
                }
            }
        }
        return $tipoproducto;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $tipoproducto = new TipoProducto();
        $tipoproducto->nombre = $request->nombre;
        $tipoproducto->descripcion = $request->descripcion;
        $tipoproducto->orden = $request->orden ?? 1;
        $tipoproducto->valor = 0;
        $tipoproducto->tamano = $request->tamano ?? 0;
        $tipoproducto->area = $request->area ?? 0;
        $tipoproducto->cabida = $request->cabida ?? 1;
        $tipoproducto->impresiones = $request->impresiones ?? 1;
        $tipoproducto->sobrante = $request->sobrante ?? 0;
        $tipoproducto->formula_ancho = $request->formula_ancho;
        $tipoproducto->formula_largo = $request->formula_largo;
        $tipoproducto->piezas_por_pliego = $request->piezas_por_pliego ?? 0;
        $tipoproducto->gastos_fijos = $request->gastos_fijos ?? 0;
        $tipoproducto->rentabilidad = $request->rentabilidad ?? 0;
        $tipoproducto->permite_troquelado = $request->permite_troquelado ? 1 : 0;
        $tipoproducto->estado = 1;

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = public_path('img/tipos');
            $imagen->move($ruta, $nombreImagen);
            $tipoproducto->imagen = $nombreImagen;
        }

        $tipoproducto->save();

        return response()->json(['id' => $tipoproducto->id, 'status' => 'success']);
    }

    public function crearAtributos(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        try {
            DB::beginTransaction();
            $tipoproducto = TipoProducto::findOrFail($request->id);
            $tipoproducto->tipo_cantidad = $request->tipo_cantidad;
            $tipoproducto->rangos = $request->rangos;
            $tipoproducto->nombre = $request->nombre;
            $tipoproducto->valor = $request->valor;
            $tipoproducto->tamano = $request->tamano;
            $tipoproducto->area = $request->area;
            $tipoproducto->cabida = $request->cabida;
            $tipoproducto->sobrante = $request->sobrante;
            $tipoproducto->impresiones = $request->impresiones;
            $tipoproducto->formula_ancho = $request->formula_ancho;
            $tipoproducto->formula_largo = $request->formula_largo;
            $tipoproducto->piezas_por_pliego = $request->piezas_por_pliego;
            $tipoproducto->save();

            $atributos = json_decode($request->atributos);
            $costos = json_decode($request->costos);
            $procesos = json_decode($request->procesos);

            if (is_array($procesos)) {
                $tipoproducto->procesos()->delete();
                foreach ($procesos as $proc) {
                    $tipoproducto->procesos()->create([
                        'nombre' => $proc->nombre,
                        'costo_unitario' => $proc->costo_unitario
                    ]);
                }
            }

            if (is_array($atributos)) {
                foreach ($atributos as $atr) {
                    if (!isset($atr->id) || $atr->id == 0) {
                        $atributo = new Atributo();
                        $atributo->id_articulo = $tipoproducto->id;
                    } else {
                        $atributo = Atributo::find($atr->id);
                        if (!$atributo) {
                            $atributo = new Atributo();
                            $atributo->id_articulo = $tipoproducto->id;
                        }
                    }
                    $atributo->valor = $atr->valor;
                    $atributo->tipo_atributo = $atr->tipo_atributo;
                    $atributo->tipo_campo = $atr->tipo_campo;
                    $atributo->tipo_valor = $atr->tipo_valor;
                    $atributo->nombre = $atr->nombre;
                    $atributo->descripcion = $atr->descripcion ?? '';
                    $atributo->alerta = $atr->alerta ?? '';
                    $atributo->cabida = $atr->cabida ?? 'Cm';
                    $atributo->operacion = $atr->operacion ?? '+';
                    $atributo->tipo_impresion = 'h';
                    $atributo->nota = 'f';
                    $atributo->orden = $atr->orden ?? 1;
                    $atributo->minimo = $atr->minimo ?? 0;
                    $atributo->maximo = $atr->maximo ?? 0;
                    $atributo->save();

                    if (isset($atr->opciones) && is_array($atr->opciones)) {
                        foreach ($atr->opciones as $op) {
                            if (!isset($op->id) || $op->id == 0) {
                                $opcion = new OpcionAtributo();
                                $opcion->id_atributo = $atributo->id;
                            } else {
                                $opcion = OpcionAtributo::find($op->id);
                                if (!$opcion) {
                                    $opcion = new OpcionAtributo();
                                    $opcion->id_atributo = $atributo->id;
                                }
                            }
                            $opcion->label = $op->label ?? '';
                            $opcion->valor = $op->valor ?? 0;
                            $opcion->descripcion = $op->descripcion ?? '';
                            $opcion->alerta = $op->alerta ?? '';
                            $opcion->orden = $op->orden ?? 1;
                            $opcion->save();

                            if (isset($op->costos) && is_array($op->costos)) {
                                foreach ($op->costos as $co) {
                                    if (!isset($co->id) || $co->id == 0) {
                                        $costoop = new CostoArticulo();
                                        $costoop->idarticulo = $tipoproducto->id;
                                        $costoop->idopcion = $opcion->id;
                                        $costoop->medida_final = $co->medida_final;
                                    } else {
                                        $costoop = CostoArticulo::find($co->id);
                                        if (!$costoop) {
                                            $costoop = new CostoArticulo();
                                            $costoop->idarticulo = $tipoproducto->id;
                                            $costoop->idopcion = $opcion->id;
                                            $costoop->medida_final = $co->medida_final;
                                        }
                                    }
                                    $costoop->titulo = $co->titulo ?? '';
                                    $costoop->descripcion = $co->descripcion ?? '';
                                    $costoop->orden_produccion = $co->orden_produccion ?? 0;
                                    $costoop->fraccion = $co->fraccion ?? 1;
                                    $costoop->rentabilidad = $co->rentabilidad ?? 0;
                                    $costoop->cantidad = $co->cantidad ?? 1;
                                    $costoop->valor = $co->valor ?? 0;
                                    $costoop->valorfull = $co->valorfull ?? 0;
                                    $costoop->save();
                                }
                            }
                        }
                    }
                }
            }

            if (is_array($costos)) {
                foreach ($costos as $cos) {
                    if (!isset($cos->id) || $cos->id == 0) {
                        $costoa = new CostoArticulo();
                        $costoa->idarticulo = $tipoproducto->id;
                        $costoa->idopcion = 0;
                        $costoa->medida_final = $cos->medida_final;
                    } else {
                        $costoa = CostoArticulo::find($cos->id);
                        if (!$costoa) {
                            $costoa = new CostoArticulo();
                            $costoa->idarticulo = $tipoproducto->id;
                            $costoa->idopcion = 0;
                            $costoa->medida_final = $cos->medida_final;
                        }
                    }
                    $costoa->titulo = $cos->titulo ?? '';
                    $costoa->descripcion = $cos->descripcion ?? '';
                    $costoa->orden_produccion = $cos->orden_produccion ?? 0;
                    $costoa->fraccion = $cos->fraccion ?? 1;
                    $costoa->rentabilidad = $cos->rentabilidad ?? 0;
                    $costoa->cantidad = $cos->cantidad ?? 1;
                    $costoa->valor = $cos->valor ?? 0;
                    $costoa->valorfull = $cos->valorfull ?? 0;
                    $costoa->save();
                }
            }

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        try {
            if (!$request->id) {
                return response()->json(['status' => 'error', 'message' => 'Falta el ID del producto'], 400);
            }
            
            $tipoproducto = TipoProducto::findOrFail($request->id);
            $tipoproducto->nombre = $request->nombre;
            $tipoproducto->descripcion = $request->descripcion;
            $tipoproducto->orden = $request->orden;
            $tipoproducto->tamano = $request->tamano ?? 0;
            $tipoproducto->area = $request->area ?? 0;
            $tipoproducto->cabida = $request->cabida ?? 0;
            $tipoproducto->sobrante = $request->sobrante ?? 0;
            $tipoproducto->impresiones = $request->impresiones ?? 0;
            $tipoproducto->formula_ancho = $request->formula_ancho;
            $tipoproducto->formula_largo = $request->formula_largo;
            $tipoproducto->piezas_por_pliego = $request->piezas_por_pliego ?? 0;
            $tipoproducto->gastos_fijos = $request->gastos_fijos ?? 0;
            $tipoproducto->rentabilidad = $request->rentabilidad ?? 0;
            $tipoproducto->permite_troquelado = $request->permite_troquelado ? 1 : 0;
            $tipoproducto->save();

            // Sincronizar Atributos de Tienda
            if ($request->has('atributos')) {
                $tipoproducto->atributosTienda()->sync($request->atributos);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function eliminar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $TipoProducto = TipoProducto::findOrFail($request->id);
        $TipoProducto->delete();

        return response()->json(['status' => 'success']);
    }

    public function eliminarC(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $TipoProducto = TipoProducto::findOrFail($request->id);
        $TipoProducto->delete();

        return response()->json(['status' => 'success']);
    }

    public function getAtributosTienda($id)
    {
        $tipoproducto = TipoProducto::findOrFail($id);
        return response()->json($tipoproducto->atributosTienda);
    }
}

