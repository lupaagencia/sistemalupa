<?php

namespace App\Http\Controllers;
use App\TipoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Articulo;
use App\Atributo;
use App\OpcionAtributo;
use App\CostoArticulo;
use App\Costois;
use App\Imagenes;
use GuzzleHttp;

class ArticuloController extends Controller
{
    public function send(Request $request)
    {
        echo 'hola';

    }
    public function index(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $articulos = Articulo::leftJoin('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->select('articulos.*', 'categorias.nombre as nombre_categoria')
                ->where('articulos.id_item_padre', '=', 0);

            if ($request->etiqueta) {
                $articulos->where('articulos.etiquetas', 'like', '%' . $request->etiqueta . '%');
            }

            $articulos = $articulos->orderBy('articulos.id', 'desc')->paginate(1000);
        } else {
            $articulos = Articulo::leftJoin('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->select('articulos.*', 'categorias.nombre as nombre_categoria')
                ->where('articulos.' . $criterio, 'like', '%' . $buscar . '%')
                ->where('articulos.id_item_padre', '=', 0);

            if ($request->etiqueta) {
                $articulos->where('articulos.etiquetas', 'like', '%' . $request->etiqueta . '%');
            }

            $articulos = $articulos->orderBy('articulos.id', 'desc')->paginate(1000);
        }

        $articulos->getCollection()->load(['imagenes', 'subarticulo', 'tipo.costos.costois', 'tipo.atributos.opciones.costoop.costois']);

        return [
            'pagination' => [
                'total' => $articulos->total(),
                'current_page' => $articulos->currentPage(),
                'per_page' => $articulos->perPage(),
                'last_page' => $articulos->lastPage(),
                'from' => $articulos->firstItem(),
                'to' => $articulos->lastItem(),
            ],
            'articulos' => $articulos
        ];
    }
    public function crearimagenes()
    {
        $articulos = Articulo::select()->get();
        foreach ($articulos as $art) {
            $l = substr($art->imagen, 0, -4);
            $img = new Imagenes();
            $img->id_tabla = $art->id;
            $img->nombre = $l;
            $img->orden = 1;
            $img->save();
        }
        return $articulos;
    }
    public function subproducto(Request $request)
    {
        $id = $request->id;
        return $id;
        $articulos = Articulo::where('id_item_padre', '=', $id)->orderBy('id', 'asc')->get();
        $subproductos = array();
        for ($i = 0; count($articulos) > $i; $i++) {
            $at = Atributo::where('id_articulo', '=', $articulos[$i]['id'])->get();
            $atributos = array();
            foreach ($at as $a) {
                $op = OpcionAtributo::where('id_atributo', '=', $a['id'])->get();
                $opciones = array();
                foreach ($op as $o) {
                    $opcion = [
                        'id' => $o['id'],
                        'id_atributo' => $o['id_atributo'],
                        'label' => $o['label'],
                        'valor' => $o['valor'],
                        'opciones' => $o['opciones'],
                        'descripcion' => $o['descripcion'],
                        'open' => false,
                        'orden' => $o['orden'],
                    ];
                    array_push($opciones, $opcion);
                }
                $atributo = [
                    'id' => $a['id'],
                    'id_articulo' => $a['id_articulo'],
                    'valor' => $a['valor'],
                    'tipo_campo' => $a['tipo_campo'],
                    'nombre' => $a['nombre'],
                    'nota' => $a['nota'],
                    'descripcion' => $a['descripcion'],
                    'alerta' => $a['alerta'],
                    'operacion' => $a['operacion'],
                    'cabida' => $a['cabida'],
                    'minimo' => $a['minimo'],
                    'maximo' => $a['maximo'],
                    'orden' => $a['orden'],
                    'open' => false,
                    'opciones_atributo' => $opciones
                ];
                array_push($atributos, $atributo);
            }

            $subproducto = [
                'id' => $articulos[$i]['id'],
                'id_item_padre' => $articulos[$i]['id_item_padre'],
                'idcategoria' => $articulos[$i]['idcategoria'],
                'codigo' => $articulos[$i]['codigo'],
                'nombre' => $articulos[$i]['nombre'],
                'cantidades' => json_decode($articulos[$i]['tipo_cantidad']),
                'rangos' => json_decode($articulos[$i]['rangos']),
                'precio_venta' => $articulos[$i]['precio_venta'],
                'iva' => $articulos[$i]['iva'],
                'stock' => $articulos[$i]['stock'],
                'tamano' => $articulos[$i]['tamano'],
                'medida_final' => $articulos[$i]['medida_final'],
                'descripcion' => $articulos[$i]['descripcion'],
                'condicion' => $articulos[$i]['condicion'],
                'open' => false,
                'atributos' => $atributos

            ];
            array_push($subproductos, $subproducto);
        }
        return [
            'subproductos' => $subproductos
        ];

    }
    public static function getSubproducto($id)
    {
        $articulos = Articulo::where('id_item_padre', '=', $id)->orderBy('id', 'asc')->get();
        $subproductos = array();
        for ($i = 0; count($articulos) > $i; $i++) {
            $at = Atributo::where('id_articulo', '=', $articulos[$i]['id'])->get();
            $cos = CostoArticulo::where('idarticulo', '=', $articulos[$i]['id'])->whereNull('idopcion')->get();
            $img = Imagenes::where('id_tabla', '=', $articulos[$i]['id'])->get();
            $costos = array();
            if (count($cos) != 0) {
                foreach ($cos as $c) {
                    $costois = Costois::where('id', '=', $c['medida_final'])->get()[0];
                    $costo = [
                        'id' => $c['id'],
                        'nombre_insumo' => $costois['nombre'],
                        'tipo_costo' => $c['titulo'],
                        'rentabilidad' => $c['rentabilidad'],
                        'cantidad' => $c['cantidad'],
                        'id_insumo' => $c['medida_final'],
                        'valor_costo' => $costois->valor,
                        'fraccion_costo' => $c['fraccion'],
                        'orden_costo' => $c['orden_produccion'],
                        'descripcion_costo' => $c['descripcion'],
                        'subtotal_costo' => $costois->valor / $c['fraccion'],
                    ];
                    array_push($costos, $costo);
                }
            }
            $atributos = array();
            foreach ($at as $a) {
                $op = OpcionAtributo::where('id_atributo', '=', $a['id'])->get();
                $opciones = array();
                foreach ($op as $o) {
                    $costo = CostoArticulo::where('idopcion', '=', $o['id'])->get();
                    $costosop = array();
                    if (count($costo) != 0) {
                        foreach ($costo as $co) {
                            $idcostois = $co['medida_final'];
                            $costois = Costois::where('id', '=', $idcostois)->get()[0];
                            $costo = [
                                'idCosto' => $co['id'],
                                'idInsumo' => $idcostois,
                                'nombre_insumo' => $costois['nombre'],
                                'titulo' => $co['titulo'],
                                'medida_final' => $idcostois,
                                'cabida' => $costois->cabida,
                                'descripcion' => $co['descripcion'],
                                'orden_produccion' => $co['orden_produccion'],
                                'fraccion' => $co['fraccion'],
                                'rentabilidad' => $co['rentabilidad'],
                                'cantidad' => $co['cantidad'],
                                'subtotal_costo' => $costois->valor / $co['fraccion'],
                                'valor' => $costois->valor,
                            ];
                            array_push($costosop, $costo);
                        }
                    }
                    $opcion = [
                        'id' => $o['id'],
                        'labelOpAtributo' => $o['label'],
                        'valorOpAtributo' => $o['valor'],
                        'descripcionOpAtributo' => $o['descripcion'],
                        'alertaOpAtributo' => $o['alerta'],
                        'posicionOpAtributo' => $o['orden'],
                        'costos' => $costosop,
                        'editOpcion' => 0,
                    ];
                    array_push($opciones, $opcion);
                }
                $atributo = [
                    'id' => $a['id'],
                    'id_articulo' => $a['id_articulo'],
                    'valor' => $a['valor'],
                    'tipo_campo' => $a['tipo_campo'],
                    'tipo_valor' => $a['tipo_valor'],
                    'nombre' => $a['nombre'],
                    'nota' => $a['nota'],
                    'descripcion' => $a['descripcion'],
                    'alerta' => $a['alerta'],
                    'operacion' => $a['operacion'],
                    'cabida' => $a['cabida'],
                    'minimo' => $a['minimo'],
                    'maximo' => $a['maximo'],
                    'orden' => $a['orden'],
                    'open' => false,
                    'opciones_atributo' => $opciones
                ];
                array_push($atributos, $atributo);
            }

            $subproducto = [
                'id' => $articulos[$i]['id'],
                'id_item_padre' => $articulos[$i]['id_item_padre'],
                'idcategoria' => $articulos[$i]['idcategoria'],
                'codigo' => $articulos[$i]['codigo'],
                'nombre' => $articulos[$i]['nombre'],
                'imagen' => $img,
                'cantidades' => json_decode($articulos[$i]['tipo_cantidad']),
                'rangos' => json_decode($articulos[$i]['rangos']),
                'precio_venta' => $articulos[$i]['precio_venta'],
                'iva' => $articulos[$i]['iva'],
                'stock' => $articulos[$i]['stock'],
                'tamano' => $articulos[$i]['tamano'],
                'medida_final' => $articulos[$i]['medida_final'],
                'descripcion' => $articulos[$i]['descripcion'],
                'etiquetas' => $articulos[$i]['etiquetas'],
                'ancho' => $articulos[$i]['ancho'],
                'largo' => $articulos[$i]['largo'],
                'alto' => $articulos[$i]['alto'],
                'volumen' => $articulos[$i]['volumen'],
                'condicion' => $articulos[$i]['condicion'],
                'open' => false,
                'atributos' => $atributos,
                'costos' => $costos

            ];
            array_push($subproductos, $subproducto);

        }
        return [
            'subproductos' => $subproductos
        ];

    }
    public function listarArticulo(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->select('articulos.tipo_cantidad', 'articulos.id', 'articulos.idcategoria', 'articulos.codigo', 'articulos.nombre', 'articulos.imagen', 'categorias.nombre as nombre_categoria', 'articulos.precio_venta', 'articulos.stock', 'articulos.tamano', 'articulos.medida_final', 'articulos.descripcion', 'articulos.condicion', 'articulos.etiquetas', 'articulos.ancho', 'articulos.largo', 'articulos.alto', 'articulos.volumen')
                ->where('articulos.id_item_padre', '=', 0);

            if ($request->etiqueta) {
                $articulos->where('articulos.etiquetas', 'like', '%' . $request->etiqueta . '%');
            }

            $articulos = $articulos->orderBy('articulos.id', 'desc')->paginate(10);
        } else {
            $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
                ->select('articulos.tipo_cantidad', 'articulos.id', 'articulos.idcategoria', 'articulos.codigo', 'articulos.nombre', 'articulos.imagen', 'categorias.nombre as nombre_categoria', 'articulos.precio_venta', 'articulos.stock', 'articulos.tamano', 'articulos.medida_final', 'articulos.descripcion', 'articulos.condicion', 'articulos.etiquetas', 'articulos.ancho', 'articulos.largo', 'articulos.alto', 'articulos.volumen')
                ->where('articulos.' . $criterio, 'like', '%' . $buscar . '%')->where('articulos.id_item_padre', '=', 0);

            if ($request->etiqueta) {
                $articulos->where('articulos.etiquetas', 'like', '%' . $request->etiqueta . '%');
            }

            $articulos = $articulos->orderBy('articulos.id', 'desc')->paginate(10);
        }
        $items = array();
        for ($i = 0; count($articulos) > $i; $i++) {
            $subarticulos = $this->getSubproducto($articulos[$i]['id']);
            $item = [
                'id' => $articulos[$i]['id'],
                'id_item_padre' => $articulos[$i]['id_item_padre'],
                'idcategoria' => $articulos[$i]['idcategoria'],
                'codigo' => $articulos[$i]['codigo'],
                'nombre' => $articulos[$i]['nombre'],
                'imagen' => $articulos[$i]['imagen'],
                'cantidades' => json_decode($articulos[$i]['tipo_cantidad']),
                'rangos' => json_decode($articulos[$i]['rangos']),
                'precio_venta' => $articulos[$i]['precio_venta'],
                'iva' => $articulos[$i]['iva'],
                'stock' => $articulos[$i]['stock'],
                'tamano' => $articulos[$i]['tamano'],
                'medida_final' => $articulos[$i]['medida_final'],
                'descripcion' => $articulos[$i]['descripcion'],
                'etiquetas' => $articulos[$i]['etiquetas'],
                'ancho' => $articulos[$i]['ancho'],
                'largo' => $articulos[$i]['largo'],
                'alto' => $articulos[$i]['alto'],
                'volumen' => $articulos[$i]['volumen'],
                'condicion' => $articulos[$i]['condicion'],
                'subproductos' => $subarticulos['subproductos'],
                'open' => false
            ];
            array_push($items, $item);
        }
        return [
            'pagination' => [
                'total' => $articulos->total(),
                'current_page' => $articulos->currentPage(),
                'per_page' => $articulos->perPage(),
                'last_page' => $articulos->lastPage(),
                'from' => $articulos->firstItem(),
                'to' => $articulos->lastItem(),
            ],
            'articulos' => $items
        ];

    }
    public function buscarArticulo(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;
        $articulos = Articulo::where('nombre', '=', $filtro)
            ->select('id', 'nombre', 'tamano', 'medida_final')->orderBy('nombre', 'asc')->take(1)->get();

        return ['articulos' => $articulos];
    }
    public function selectArticulobyid(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');


        $id = $request->id;
        $articulo = Articulo::find($id);
        // foreach($articulos as $articulo){
        $articulo->categoria;
        $articulo->tipo;
        $cantidades = json_decode($articulo->tipo->tipo_cantidad);
        $rangos = json_decode($articulo->tipo->rangos);
        $articulo->tipo->tipo_cantidad = $cantidades;
        $articulo->tipo->rangos = $rangos;
        $articulo->tipo->costos;
        $articulo->tipo->atributos;
        $articulo->imagenes;
        if (count($articulo->tipo->costos) != 0) {
            foreach ($articulo->tipo->costos as $c) {
                $c->costois;
            }
        }

        foreach ($articulo->tipo->atributos as $a) {
            $a->opciones;
            foreach ($a->opciones as $o) {
                $o->costoop;
                if (count($o->costoop) != 0) {
                    foreach ($o->costoop as $co) {
                        $co->costois;
                    }
                }

            }

        }
        $articulo->subarticulo;

        // }
        return $articulo;
    }
    public function selectArticulo(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $filtro = $request->filtro;
        $articulos = Articulo::where('nombre', 'LIKE', '%' . $filtro . '%')
            ->select('id', 'nombre', 'precio_venta', 'tamano', 'medida_final')->orderBy('nombre', 'asc')->take(20)->get();

        return ['articulos' => $articulos];
    }
    public function importar(Request $request)
    {
        $items = json_decode($request->datos);
        foreach ($items as $item) {
            $articulo = Articulo::where('id', $item[0])->first();
            $articulo->nombre = $item[1];
            $articulo->descripcion = $item[2];
            $articulo->precio_venta = $item[3];
            $articulo->tamano = $item[4];
            if (isset($item[5])) $articulo->medida_final = $item[5];
            $articulo->save();
        }


    }
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            $id_item_padre = $request->id_item_padre ? $request->id_item_padre : 0;

            $articulo = new Articulo();
            $articulo->id_item_padre = $id_item_padre;
            $articulo->idcategoria = $request->idcategoria;
            $articulo->codigo = 1;
            $articulo->tipo_producto_id = $request->tipo_producto_id;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta ?: 0;
            $articulo->stock = $request->stock ?: 0;
            $articulo->tamano = $request->tamano;
            $articulo->medida_final = $request->medida_final;
            $articulo->ancho_final = $request->ancho_final;
            $articulo->largo_final = $request->largo_final;
            $articulo->descripcion = $request->descripcion ?: '';
            $articulo->condicion = 1;
            $articulo->save();

            $imagen = $request->file('imagen');
            $orden = json_decode($request->orden);
            if (!empty($imagen)) {
                for ($i = 0; $i < count($imagen); $i++) {
                    $img = $imagen[$i];
                    $nombreImg = time() . '_' . $img->getclientoriginalname();
                    $ruta = public_path('img/productos');
                    $img->move($ruta, $nombreImg);

                    $imagenes = new Imagenes();
                    $imagenes->id_tabla = $articulo->id;
                    $imagenes->nombre = $nombreImg;
                    $imagenes->orden = isset($orden[$i]) ? $orden[$i]->orden : ($i + 1);
                    $imagenes->save();
                }
            }

            DB::commit();
            return response()->json(['id' => $articulo->id, 'message' => 'Artículo guardado con éxito']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar artículo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar el artículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            $id_item_padre = $request->id_item_padre ? $request->id_item_padre : 0;

            $articulo = Articulo::findOrFail($request->id);
            $articulo->id_item_padre = $id_item_padre;
            $articulo->idcategoria = $request->idcategoria;
            $articulo->codigo = 1;
            $articulo->tipo_producto_id = $request->tipo_producto_id;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta ?: 0;
            $articulo->stock = $request->stock ?: 0;
            $articulo->tamano = $request->tamano;
            $articulo->medida_final = $request->medida_final;
            $articulo->ancho_final = $request->ancho_final;
            $articulo->largo_final = $request->largo_final;
            $articulo->descripcion = $request->descripcion ?: '';
            $articulo->condicion = 1;
            $articulo->save();

            $imagenesActual = json_decode($request->imagenes);
            if (!empty($imagenesActual)) {
                foreach ($imagenesActual as $imgData) {
                    $imagene = Imagenes::findOrFail($imgData->id);
                    $imagene->orden = $imgData->orden;
                    $imagene->save();
                }
            }

            if ($request->hasFile('imagen')) {
                $imagenes = $request->file('imagen');
                $orden = json_decode($request->orden);
                foreach ($imagenes as $index => $img) {
                    $nombreImg = time() . '_' . $img->getClientOriginalName();
                    $ruta = public_path('img/productos');
                    $img->move($ruta, $nombreImg);

                    $nuevaImagen = new Imagenes();
                    $nuevaImagen->id_tabla = $articulo->id;
                    $nuevaImagen->nombre = $nombreImg;
                    $nuevaImagen->orden = isset($orden[$index]) ? $orden[$index]->orden : ($index + 1);
                    $nuevaImagen->save();
                }
            }

            DB::commit();
            return response()->json(['id' => $articulo->id, 'message' => 'Artículo actualizado con éxito']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar artículo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el artículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->delete();
        $subarticulo = Articulo::where('id_item_padre', $articulo->id);
        $subarticulo->delete();
        return $request->id;
    }
    public function deleteAtributo(Request $request)
    {
        $id = $request->id;
        $atributo = Atributo::find($id);
        $atributo->delete();
    }
    public function deleteOpcion(Request $request)
    {
        $id = $request->id;
        $opcion = OpcionAtributo::find($id);
        $opcion->delete();
    }

    public function activar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '1';
        $articulo->save();
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '0';
        $articulo->save();
    }

    // --- MÉTODOS PARA LÍNEAS DE TROQUEL POR CABIDA ---

    public function listarTroqueles(Request $request)
    {
        $articulo_id = $request->articulo_id;
        $troqueles = \App\ArticuloTroquel::where('articulo_id', $articulo_id)
            ->orderBy('cabida', 'asc')
            ->get();
        return ['troqueles' => $troqueles];
    }

    public function guardarTroquel(Request $request)
    {
        try {
            $articulo_id = $request->articulo_id;
            $cabida = $request->cabida;
            $id = $request->id;
            
            // Si viene un ID, buscamos por ID. Si no, buscamos por cabida (para compatibilidad)
            if ($id) {
                $troquel = \App\ArticuloTroquel::find($id);
            } else {
                $troquel = \App\ArticuloTroquel::where('articulo_id', $articulo_id)
                    ->where('cabida', $cabida)
                    ->first();
            }
                
            if (!$troquel) {
                $troquel = new \App\ArticuloTroquel();
                $troquel->articulo_id = $articulo_id;
                $troquel->cabida = $cabida;
            }

            $troquel->ancho_impresion = $request->ancho_impresion;
            $troquel->largo_impresion = $request->largo_impresion;
            $troquel->tamano = $request->tamano;
            $troquel->mostrar = $request->mostrar ?? 1;

            if ($request->hasFile('imagen')) {
                // Eliminar anterior si existe
                if ($troquel->imagen) {
                    Storage::disk('public')->delete('troqueles/' . $troquel->imagen);
                }
                
                $img = $request->file('imagen');
                $nombreImg = time() . '_troquel_' . $articulo_id . '_' . $cabida . '.' . $img->getClientOriginalExtension();
                $img->storeAs('troqueles', $nombreImg, 'public');
                $troquel->imagen = $nombreImg;
            }

            $troquel->save();
            return ['status' => 'success', 'troquel' => $troquel];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eliminarTroquel(Request $request)
    {
        $id = $request->id;
        $troquel = \App\ArticuloTroquel::findOrFail($id);
        if ($troquel->imagen) {
            Storage::disk('public')->delete('troqueles/' . $troquel->imagen);
        }
        $troquel->delete();
        return ['status' => 'success'];
    }

    public function listarTodosLosTroqueles(Request $request)
    {
        $buscar = $request->buscar;
        $troqueles = \App\ArticuloTroquel::join('articulos', 'articulo_troquels.articulo_id', '=', 'articulos.id')
            ->select('articulo_troquels.*', 'articulos.nombre as nombre_articulo');

        if ($buscar != '') {
            $troqueles->where('articulos.nombre', 'like', '%' . $buscar . '%');
        }

        $troqueles = $troqueles->orderBy('articulos.nombre', 'asc')
            ->orderBy('articulo_troquels.cabida', 'asc')
            ->paginate(20);

        return [
            'pagination' => [
                'total'        => $troqueles->total(),
                'current_page' => $troqueles->currentPage(),
                'per_page'     => $troqueles->perPage(),
                'last_page'    => $troqueles->lastPage(),
                'from'         => $troqueles->firstItem(),
                'to'           => $troqueles->lastItem(),
            ],
            'troqueles' => $troqueles
        ];
    }

    public function getPopulares(Request $request)
    {
        $limit = $request->limit ?: 6;
        $popularesIds = \Illuminate\Support\Facades\DB::table('ordentrabajos')
            ->select('articulo_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('articulo_id')
            ->orderBy('total', 'desc')
            ->whereNotNull('articulo_id')
            ->pluck('articulo_id');

        $articulos = Articulo::join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
            ->select('articulos.tipo_cantidad', 'articulos.id', 'articulos.idcategoria', 'articulos.codigo', 'articulos.nombre', 'articulos.imagen', 'categorias.nombre as nombre_categoria', 'articulos.precio_venta', 'articulos.stock', 'articulos.tamano', 'articulos.medida_final', 'articulos.descripcion', 'articulos.condicion', 'articulos.etiquetas', 'articulos.ancho', 'articulos.largo', 'articulos.alto', 'articulos.volumen')
            ->whereIn('articulos.id', $popularesIds)
            ->where('articulos.id_item_padre', '=', 0)
            ->has('imagenes')
            ->get();
        
        $articulos->load(['imagenes']);
            
        $orderedArticulos = $articulos->sortBy(function($model) use ($popularesIds){
            return array_search($model->id, $popularesIds->toArray());
        })->take($limit)->values();

        $items = array();
        foreach ($orderedArticulos as $art) {
            $subarticulos = $this->getSubproducto($art['id']);
            $item = [
                'id' => $art['id'],
                'id_item_padre' => $art['id_item_padre'],
                'idcategoria' => $art['idcategoria'],
                'codigo' => $art['codigo'],
                'nombre' => $art['nombre'],
                'imagen' => $art['imagen'],
                'cantidades' => json_decode($art['tipo_cantidad']),
                'rangos' => json_decode($art['rangos']),
                'precio_venta' => $art['precio_venta'],
                'iva' => $art['iva'],
                'stock' => $art['stock'],
                'tamano' => $art['tamano'],
                'medida_final' => $art['medida_final'],
                'descripcion' => $art['descripcion'],
                'etiquetas' => $art['etiquetas'],
                'ancho' => $art['ancho'],
                'largo' => $art['largo'],
                'alto' => $art['alto'],
                'volumen' => $art['volumen'],
                'condicion' => $art['condicion'],
                'imagenes' => $art['imagenes'],
                'subproductos' => $subarticulos['subproductos'],
                'open' => false
            ];
            array_push($items, $item);
        }
        return ['articulos' => $items];
    }
}

