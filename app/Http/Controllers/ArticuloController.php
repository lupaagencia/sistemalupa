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

        $articulos->getCollection()->load(['categorias', 'imagenes', 'subarticulo', 'subproductos', 'tipo.costos.costois', 'tipo.atributos.opciones.costoop.costois']);

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
        
        if (!$articulo) {
            return response()->json(['error' => 'Artículo no encontrado'], 404);
        }
        $articulo->categoria;
        $articulo->categorias;
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

            $categoriasInput = $request->categorias;
            if (is_string($categoriasInput)) {
                $categoriasInput = json_decode($categoriasInput, true);
            }

            $articulo = new Articulo();
            $articulo->id_item_padre = $id_item_padre;
            if (is_array($categoriasInput) && count($categoriasInput) > 0) {
                $articulo->idcategoria = $categoriasInput[0];
            } else {
                $articulo->idcategoria = $request->idcategoria ?: 0;
            }
            $articulo->codigo = 1;
            $articulo->tipo_producto_id = $request->tipo_producto_id;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta ?: 0;
            $articulo->stock = $request->stock ?: 0;
            $articulo->tamano = $request->tamano;
            $articulo->medida_final = $request->medida_final;
            $articulo->ancho_final = $request->ancho_final;
            $articulo->largo_final = $request->largo_final;
            $articulo->cabidas_materiales = is_string($request->cabidas_materiales) ? json_decode($request->cabidas_materiales, true) : $request->cabidas_materiales;
            $articulo->descripcion = $request->descripcion ?: '';
            $articulo->condicion = 1;
            $articulo->save();

            if (is_array($categoriasInput) && count($categoriasInput) > 0) {
                $articulo->categorias()->sync($categoriasInput);
            } else if ($request->idcategoria) {
                $articulo->categorias()->sync([$request->idcategoria]);
            }

            $imagen = $request->file('imagen');
            $orden = json_decode($request->orden);
            if (!empty($imagen)) {
                $ruta = public_path('img/productos');
                for ($i = 0; $i < count($imagen); $i++) {
                    $img = $imagen[$i];
                    $nombreImg = time() . '_' . $i . '_' . str_replace(' ', '_', $img->getClientOriginalName());
                    $this->optimizarYGuardarImagen($img, $ruta, $nombreImg);

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

            $categoriasInput = $request->categorias;
            if (is_string($categoriasInput)) {
                $categoriasInput = json_decode($categoriasInput, true);
            }

            $articulo = Articulo::findOrFail($request->id);
            $articulo->id_item_padre = $id_item_padre;
            if (is_array($categoriasInput) && count($categoriasInput) > 0) {
                $articulo->idcategoria = $categoriasInput[0];
            } else if ($request->idcategoria) {
                $articulo->idcategoria = $request->idcategoria;
            }
            $articulo->codigo = 1;
            $articulo->tipo_producto_id = $request->tipo_producto_id;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta ?: 0;
            $articulo->stock = isset($request->stock) ? $request->stock : 0;
            $articulo->tamano = $request->tamano;
            $articulo->medida_final = $request->medida_final;
            $articulo->ancho_final = $request->ancho_final;
            $articulo->largo_final = $request->largo_final;
            $articulo->cabidas_materiales = is_string($request->cabidas_materiales) ? json_decode($request->cabidas_materiales, true) : $request->cabidas_materiales;
            $articulo->descripcion = $request->descripcion ?: '';
            $articulo->condicion = 1;
            $articulo->save();

            if (is_array($categoriasInput) && count($categoriasInput) > 0) {
                $articulo->categorias()->sync($categoriasInput);
            } else if ($request->idcategoria) {
                $articulo->categorias()->sync([$request->idcategoria]);
            }

            $imagenesActual = json_decode($request->imagenes);
            if (!empty($imagenesActual)) {
                foreach ($imagenesActual as $imgData) {
                    if (isset($imgData->id)) {
                        $imagene = Imagenes::find($imgData->id);
                        if ($imagene) {
                            $imagene->orden = $imgData->orden;
                            $imagene->save();
                        }
                    }
                }
            }

            if ($request->hasFile('imagen')) {
                $imagenes = $request->file('imagen');
                $orden = json_decode($request->orden);
                if (!is_array($imagenes)) {
                    $imagenes = [$imagenes];
                }
                $ruta = public_path('img/productos');
                foreach ($imagenes as $index => $img) {
                    $nombreImg = time() . '_' . $index . '_' . str_replace(' ', '_', $img->getClientOriginalName());
                    $this->optimizarYGuardarImagen($img, $ruta, $nombreImg);

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

    public function borrarImagen(Request $request)
    {
        try {
            $id = $request->id;
            $idTabla = $request->idtabla;

            $imagen = Imagenes::find($id);
            if ($imagen) {
                $rutaArchivo = public_path('img/productos/' . $imagen->nombre);
                if (file_exists($rutaArchivo)) {
                    @unlink($rutaArchivo);
                }
                $imagen->delete();
            }

            $imagenesRestantes = Imagenes::where('id_tabla', $idTabla)->orderBy('orden', 'asc')->get();
            return response()->json($imagenesRestantes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function optimizarYGuardarImagen($fileInput, $destinationPath, $filename, $quality = 75, $maxDimension = 1024)
    {
        @ini_set('memory_limit', '512M');
        if (!file_exists($destinationPath)) {
            @mkdir($destinationPath, 0755, true);
        }
        $fullPath = $destinationPath . '/' . $filename;

        if (extension_loaded('gd')) {
            try {
                $mime = $fileInput->getMimeType();
                $realPath = $fileInput->getRealPath();

                list($origW, $origH) = @getimagesize($realPath);

                if ($origW && $origH) {
                    $newW = $origW;
                    $newH = $origH;

                    if ($origW > $maxDimension || $origH > $maxDimension) {
                        if ($origW >= $origH) {
                            $newW = $maxDimension;
                            $newH = (int)round(($origH / $origW) * $maxDimension);
                        } else {
                            $newH = $maxDimension;
                            $newW = (int)round(($origW / $origH) * $maxDimension);
                        }
                    }

                    $srcImg = null;
                    if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                        $srcImg = @imagecreatefromjpeg($realPath);
                    } elseif ($mime === 'image/png') {
                        $srcImg = @imagecreatefrompng($realPath);
                    } elseif ($mime === 'image/webp') {
                        $srcImg = @imagecreatefromwebp($realPath);
                    }

                    if ($srcImg) {
                        $dstImg = imagecreatetruecolor($newW, $newH);

                        if ($mime === 'image/png' || $mime === 'image/webp') {
                            imagealphablending($dstImg, false);
                            imagesavealpha($dstImg, true);
                            $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                            imagefilledrectangle($dstImg, 0, 0, $newW, $newH, $transparent);
                        }

                        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                        if ($mime === 'image/png') {
                            imagepng($dstImg, $fullPath, 6);
                        } elseif ($mime === 'image/webp') {
                            imagewebp($dstImg, $fullPath, $quality);
                        } else {
                            imagejpeg($dstImg, $fullPath, $quality);
                        }

                        imagedestroy($srcImg);
                        imagedestroy($dstImg);
                        return;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error al redimensionar/optimizar imagen con GD: ' . $e->getMessage());
            }
        }

        try {
            $fileInput->move($destinationPath, $filename);
        } catch (\Exception $e) {
            \Log::error('Error al mover archivo de imagen: ' . $e->getMessage());
        }
    }

    public function eliminar(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar artículos.'], 403);
        }
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
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar atributos.'], 403);
        }
        $id = $request->id;
        $atributo = Atributo::find($id);
        $atributo->delete();
    }
    public function deleteOpcion(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar opciones.'], 403);
        }
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

    public function importarImagenesMasivas(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');
        if (!$request->hasFile('imagenes')) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se han seleccionado imágenes para subir.'
            ], 400);
        }

        try {
            $files = $request->file('imagenes');
            if (!is_array($files)) {
                $files = [$files];
            }

            $ruta = public_path('img/productos');
            if (!file_exists($ruta)) {
                @mkdir($ruta, 0755, true);
            }

            $articulosModificados = [];
            $articulosLimpiados = $request->has('articulos_ya_limpiados')
                ? (is_array($request->articulos_ya_limpiados) ? $request->articulos_ya_limpiados : json_decode($request->articulos_ya_limpiados, true))
                : [];
            if (!is_array($articulosLimpiados)) {
                $articulosLimpiados = [];
            }

            $procesados = [];
            $noEncontrados = [];

            // Cargar todos los artículos en memoria una sola vez para búsqueda ultrarrápida
            $todosLosArticulos = Articulo::select('id', 'codigo', 'nombre', 'imagen')->get();

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);

                // Analizar patrón: p.ej. "REF123-1", "BOLSA_TELA_2", "LS16-1", "caja ls16-2"
                $refStr = $filenameWithoutExt;
                $posicion = 1;

                if (preg_match('/^(.*?)[-_\s]+(\d+)$/', $filenameWithoutExt, $matches)) {
                    $refStr = trim($matches[1]);
                    $posicion = (int)$matches[2];
                } else {
                    $refStr = trim($filenameWithoutExt);
                }

                $lowerRef = strtolower($refStr);
                $refClean = strtolower(trim(str_replace(['-', '_'], ' ', $refStr)));

                $articuloTarget = null;

                // Nivel 1: Coincidencia exacta por código o nombre (case insensitive)
                foreach ($todosLosArticulos as $art) {
                    $cCode = strtolower(trim((string)$art->codigo));
                    $cName = strtolower(trim((string)$art->nombre));
                    if (($cCode !== '' && $cCode === $lowerRef) || ($cName !== '' && $cName === $lowerRef)) {
                        $articuloTarget = $art;
                        break;
                    }
                }

                // Nivel 2: Coincidencia limpia (reemplazando guiones y guiones bajos por espacios)
                if (!$articuloTarget) {
                    foreach ($todosLosArticulos as $art) {
                        $cCodeClean = strtolower(trim(str_replace(['-', '_'], ' ', (string)$art->codigo)));
                        $cNameClean = strtolower(trim(str_replace(['-', '_'], ' ', (string)$art->nombre)));
                        if (($cCodeClean !== '' && $cCodeClean === $refClean) || ($cNameClean !== '' && $cNameClean === $refClean)) {
                            $articuloTarget = $art;
                            break;
                        }
                    }
                }

                // Nivel 3: Búsqueda parcial (ej: "LS16" dentro de "Caja LS16" o "Bolsa LS16")
                if (!$articuloTarget && strlen($lowerRef) >= 2) {
                    foreach ($todosLosArticulos as $art) {
                        $cCode = strtolower(trim((string)$art->codigo));
                        $cName = strtolower(trim((string)$art->nombre));
                        if (($cCode !== '' && strpos($cCode, $lowerRef) !== false) || ($cName !== '' && strpos($cName, $lowerRef) !== false)) {
                            $articuloTarget = $art;
                            break;
                        }
                    }
                }

                // Nivel 4: Búsqueda inversa (ej: archivo "caja_ls16-1.jpg" y artículo en BD se llama "LS16")
                if (!$articuloTarget && strlen($lowerRef) >= 3) {
                    foreach ($todosLosArticulos as $art) {
                        $cCode = strtolower(trim((string)$art->codigo));
                        $cName = strtolower(trim((string)$art->nombre));
                        if (($cCode !== '' && strlen($cCode) >= 3 && strpos($lowerRef, $cCode) !== false) || 
                            ($cName !== '' && strlen($cName) >= 3 && strpos($lowerRef, $cName) !== false)) {
                            $articuloTarget = $art;
                            break;
                        }
                    }
                }

                if (!$articuloTarget) {
                    $noEncontrados[] = [
                        'archivo' => $originalName,
                        'codigo_buscado' => $refStr,
                        'posicion' => $posicion,
                        'motivo' => 'No se encontró ningún artículo coincidente por código o nombre'
                    ];
                    continue;
                }

                $artId = $articuloTarget->id;

                // Si es la PRIMERA imagen de este artículo en este lote/sesión, eliminar fotos antiguas
                if (!in_array($artId, $articulosLimpiados)) {
                    $viejasImagenes = Imagenes::where('id_tabla', $artId)->get();
                    foreach ($viejasImagenes as $vImg) {
                        $oldPath = $ruta . '/' . $vImg->nombre;
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    Imagenes::where('id_tabla', $artId)->delete();
                    $articulosLimpiados[] = $artId;
                }

                // Guardar nueva imagen
                $extension = strtolower($file->getClientOriginalExtension());
                if (!$extension) $extension = 'jpg';
                $nombreImg = 'img_' . $artId . '_' . $posicion . '_' . time() . '_' . str_replace(' ', '_', $originalName);

                $this->optimizarYGuardarImagen($file, $ruta, $nombreImg);

                // Crear registro en tabla imagenes
                $nuevaImagen = new Imagenes();
                $nuevaImagen->id_tabla = $artId;
                $nuevaImagen->nombre = $nombreImg;
                $nuevaImagen->orden = $posicion;
                $nuevaImagen->save();

                // Si la posición es 1 o la foto principal está vacía, actualizar campo 'imagen' en articulos
                if ($posicion == 1 || empty($articuloTarget->imagen)) {
                    Articulo::where('id', $artId)->update(['imagen' => $nombreImg]);
                    $articuloTarget->imagen = $nombreImg;
                }

                if (!in_array($artId, $articulosModificados)) {
                    $articulosModificados[] = $artId;
                }

                $procesados[] = [
                    'archivo' => $originalName,
                    'articulo_id' => $artId,
                    'nombre_articulo' => $articuloTarget->nombre,
                    'codigo_articulo' => $articuloTarget->codigo,
                    'posicion' => $posicion,
                    'nueva_imagen' => $nombreImg
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Carga e importación masiva finalizada con éxito.',
                'total_archivos' => count($files),
                'articulos_actualizados_count' => count($articulosModificados),
                'articulos_limpiados' => array_values(array_unique($articulosLimpiados)),
                'procesados' => $procesados,
                'no_encontrados' => $noEncontrados
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en importarImagenesMasivas: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar imágenes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function asignarCategoriasMasivas(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            $articuloIds = $request->articulo_ids;
            $categoriaIds = $request->categoria_ids;
            $modo = $request->modo ?: 'reemplazar'; // 'agregar' or 'reemplazar'

            if (is_string($articuloIds)) $articuloIds = json_decode($articuloIds, true);
            if (is_string($categoriaIds)) $categoriaIds = json_decode($categoriaIds, true);

            if (empty($articuloIds) || !is_array($articuloIds)) {
                return response()->json(['message' => 'No se seleccionaron artículos'], 422);
            }
            if (empty($categoriaIds) || !is_array($categoriaIds)) {
                return response()->json(['message' => 'No se seleccionaron categorías'], 422);
            }

            $actualizados = 0;
            $articulos = Articulo::whereIn('id', $articuloIds)->get();

            foreach ($articulos as $articulo) {
                if ($modo === 'agregar') {
                    $articulo->categorias()->syncWithoutDetaching($categoriaIds);
                } else {
                    $articulo->categorias()->sync($categoriaIds);
                }

                if (!empty($categoriaIds)) {
                    $articulo->idcategoria = $categoriaIds[0];
                    $articulo->save();
                }
                $actualizados++;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'actualizados' => $actualizados,
                'message' => "Se asignaron las categorías correctamente a {$actualizados} producto(s)."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en asignarCategoriasMasivas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al asignar categorías: ' . $e->getMessage()
            ], 500);
        }
    }
}

