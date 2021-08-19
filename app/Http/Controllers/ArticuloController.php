<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Articulo;
use App\Atributo;
use App\OpcionAtributo;
use GuzzleHttp;

class ArticuloController extends Controller
{
    public function send(Request $request){
        echo 'hola';

    }
    public function index(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('*','articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','articulos.imagen','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.id_item_padre','=',0)->orderBy('articulos.id', 'desc')->paginate(10);
        }
        else{
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('*','articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','articulos.imagen','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('articulos.id', 'desc')->paginate(10);
        }
        $items = array();
        for($i=0;count($articulos)>$i;$i++){
                $at=Atributo::where('id_articulo','=',$articulos[$i]['id'])->get();
                $atributos=array();
                foreach($at as $a){
                    $op=OpcionAtributo::where('id_atributo','=',$a['id'])->get();
                    $opciones=array();
                    foreach($op as $o){
                        $opcion=[
                            'id'=>$o['id'],
                            'id_atributo'=>$o['id_atributo'],
                            'label'=>$o['label'],
                            'valor'=>$o['valor'],
                            'opciones'=>$o['opciones'],
                            'descripcion'=>$o['descripcion'],
                            'alerta'=>$o['alerta'],
                            'open'=>false,
                            'orden'=>$o['orden'],
                        ];
                        array_push($opciones,$opcion);
                    }
                    $atributo=[
                        'id'=>$a['id'],
                        'id_articulo'=>$a['id_articulo'],
                        'valor'=>$a['valor'],
                        'tipo_campo'=>$a['tipo_campo'],
                        'nombre'=>$a['nombre'],
                        'nota'=>$a['nota'],
                        'descripcion'=>$a['descripcion'],
                        'alerta'=>$a['alerta'],
                        'operacion'=>$a['operacion'],
                        'unidad_medida'=>$a['unidad_medida'],
                        'minimo'=>$a['minimo'],
                        'maximo'=>$a['maximo'],
                        'orden'=>$a['orden'],
                        'open'=>false,
                        'opciones_atributo'=>$opciones
                    ];
                    array_push($atributos,$atributo);
                }
            $subarticulos = self::getSubproducto($articulos[$i]['id']);
            $item=[
                'id'=>$articulos[$i]['id'],
                'id_item_padre'=>$articulos[$i]['id_item_padre'],
                'idcategoria'=>$articulos[$i]['idcategoria'],
                'codigo'=>$articulos[$i]['codigo'],
                'nombre'=>$articulos[$i]['nombre'],
                'imagen'=>$articulos[$i]['imagen'],
                'rangos'=>json_decode($articulos[$i]['rangos']),
                'precio_venta'=>$articulos[$i]['precio_venta'],
                'iva'=>$articulos[$i]['iva'],
                'stock'=>$articulos[$i]['stock'],
                'descripcion'=>$articulos[$i]['descripcion'],
                'condicion'=>$articulos[$i]['condicion'],
                'subproductos'=>$subarticulos['subproductos'],
                'atributos'=>$atributos,
                'tipo_producto'=>$articulos[$i]['tipo_producto'],
                'open'=>false
            ];
            array_push($items, $item);
        }
        return [
            'pagination' => [
                'total'        => $articulos->total(),
                'current_page' => $articulos->currentPage(),
                'per_page'     => $articulos->perPage(),
                'last_page'    => $articulos->lastPage(),
                'from'         => $articulos->firstItem(),
                'to'           => $articulos->lastItem(),
            ],
            'articulos' => $items
        ];
    }
    public function subproducto(Request $request){
        $id=$request->id;
        return $id;
        $articulos = Articulo::where('id_item_padre','=',$id)->orderBy('id', 'asc')->get();
        $subproductos = array();
        for($i=0;count($articulos)>$i;$i++){
            $at=Atributo::where('id_articulo','=',$articulos[$i]['id'])->get();
            $atributos=array();
            foreach($at as $a){
                $op=OpcionAtributo::where('id_atributo','=',$a['id'])->get();
                $opciones=array();
                foreach($op as $o){
                    $opcion=[
                        'id'=>$o['id'],
                        'id_atributo'=>$o['id_atributo'],
                        'label'=>$o['label'],
                        'valor'=>$o['valor'],
                        'opciones'=>$o['opciones'],
                        'descripcion'=>$o['descripcion'],
                        'alerta'=>$o['alerta'],
                        'open'=>false,
                        'orden'=>$o['orden'],
                    ];
                    array_push($opciones,$opcion);
                }
                $atributo=[
                    'id'=>$a['id'],
                    'id_articulo'=>$a['id_articulo'],
                    'valor'=>$a['valor'],
                    'tipo_campo'=>$a['tipo_campo'],
                    'nombre'=>$a['nombre'],
                    'nota'=>$a['nota'],
                    'descripcion'=>$a['descripcion'],
                    'alerta'=>$a['alerta'],
                    'operacion'=>$a['operacion'],
                    'unidad_medida'=>$a['unidad_medida'],
                    'minimo'=>$a['minimo'],
                    'maximo'=>$a['maximo'],
                    'orden'=>$a['orden'],
                    'open'=>false,
                    'opciones_atributo'=>$opciones
                ];
                array_push($atributos,$atributo);
            }
           
            $subproducto=[
                'id'=>$articulos[$i]['id'],
                'id_item_padre'=>$articulos[$i]['id_item_padre'],
                'idcategoria'=>$articulos[$i]['idcategoria'],
                'codigo'=>$articulos[$i]['codigo'],
                'nombre'=>$articulos[$i]['nombre'],
                'rangos'=>json_decode($articulos[$i]['rangos']),
                'precio_venta'=>$articulos[$i]['precio_venta'],
                'iva'=>$articulos[$i]['iva'],
                'stock'=>$articulos[$i]['stock'],
                'descripcion'=>$articulos[$i]['descripcion'],
                'condicion'=>$articulos[$i]['condicion'],
                'open'=>false,
                'atributos'=>$atributos
    
            ];
            array_push($subproductos, $subproducto);
        }
        return [
            'subproductos'=>$subproductos
        ];

    }
   public static function getSubproducto($id){
        $articulos = Articulo::where('id_item_padre','=',$id)->orderBy('id', 'asc')->get();
        $subproductos = array();
        for($i=0;count($articulos)>$i;$i++){
            $at=Atributo::where('id_articulo','=',$articulos[$i]['id'])->get();
            $atributos=array();
            foreach($at as $a){
                $op=OpcionAtributo::where('id_atributo','=',$a['id'])->get();
                $opciones=array();
                foreach($op as $o){
                    $opcion=[
                        'id'=>$o['id'],
                        'id_atributo'=>$o['id_atributo'],
                        'label'=>$o['label'],
                        'valor'=>$o['valor'],
                        'opciones'=>$o['opciones'],
                        'descripcion'=>$o['descripcion'],
                        'alerta'=>$o['alerta'],
                        'open'=>false,
                        'orden'=>$o['orden'],
                    ];
                    array_push($opciones,$opcion);
                }
                $atributo=[
                    'id'=>$a['id'],
                    'id_articulo'=>$a['id_articulo'],
                    'valor'=>$a['valor'],
                    'tipo_campo'=>$a['tipo_campo'],
                    'nombre'=>$a['nombre'],
                    'nota'=>$a['nota'],
                    'descripcion'=>$a['descripcion'],
                    'alerta'=>$a['alerta'],
                    'operacion'=>$a['operacion'],
                    'unidad_medida'=>$a['unidad_medida'],
                    'minimo'=>$a['minimo'],
                    'maximo'=>$a['maximo'],
                    'orden'=>$a['orden'],
                    'open'=>false,
                    'opciones_atributo'=>$opciones
                ];
                array_push($atributos,$atributo);
            }
           
            $subproducto=[
                'id'=>$articulos[$i]['id'],
                'id_item_padre'=>$articulos[$i]['id_item_padre'],
                'idcategoria'=>$articulos[$i]['idcategoria'],
                'codigo'=>$articulos[$i]['codigo'],
                'nombre'=>$articulos[$i]['nombre'],
                'imagen'=>$articulos[$i]['imagen'],
                'rangos'=>json_decode($articulos[$i]['rangos']),
                'precio_venta'=>$articulos[$i]['precio_venta'],
                'iva'=>$articulos[$i]['iva'],
                'stock'=>$articulos[$i]['stock'],
                'descripcion'=>$articulos[$i]['descripcion'],
                'condicion'=>$articulos[$i]['condicion'],
                'open'=>false,
                'atributos'=>$atributos
    
            ];
            array_push($subproductos, $subproducto);
        }
        return [
            'subproductos'=>$subproductos
        ];

    }
    public function listarArticulo(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','articulos.imagen','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.id_item_padre','=',0)->orderBy('articulos.id', 'desc')->paginate(10);
        }
        else{
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','articulos.imagen','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.'.$criterio, 'like', '%'. $buscar . '%')->where('articulos.id_item_padre','=',0)
            ->orderBy('articulos.id', 'desc')->paginate(10);
        }
        $items = array();
        for($i=0;count($articulos)>$i;$i++){
            $subarticulos = $this->getSubproducto($articulos[$i]['id']);
            $item=[
                'id'=>$articulos[$i]['id'],
                'id_item_padre'=>$articulos[$i]['id_item_padre'],
                'idcategoria'=>$articulos[$i]['idcategoria'],
                'codigo'=>$articulos[$i]['codigo'],
                'nombre'=>$articulos[$i]['nombre'],
                'imagen'=>$articulos[$i]['imagen'],
                'rangos'=>json_decode($articulos[$i]['rangos']),
                'precio_venta'=>$articulos[$i]['precio_venta'],
                'iva'=>$articulos[$i]['iva'],
                'stock'=>$articulos[$i]['stock'],
                'descripcion'=>$articulos[$i]['descripcion'],
                'condicion'=>$articulos[$i]['condicion'],
                'subproductos'=>$subarticulos['subproductos'],
                'open'=>false
            ];
            array_push($items, $item);
        }
        return [
            'pagination' => [
                'total'        => $articulos->total(),
                'current_page' => $articulos->currentPage(),
                'per_page'     => $articulos->perPage(),
                'last_page'    => $articulos->lastPage(),
                'from'         => $articulos->firstItem(),
                'to'           => $articulos->lastItem(),
            ],
            'articulos' => $items
        ];

    }
    public function buscarArticulo(Request $request){
        if (!$request->ajax()) return redirect('/');

        $filtro = $request->filtro;
        $articulos = Articulo::where('nombre','=', $filtro)
        ->select('id','nombre')->orderBy('nombre', 'asc')->take(1)->get();

        return ['articulos' => $articulos];
    }
    public function selectArticulo(Request $request){
        if (!$request->ajax()) return redirect('/');

        $filtro = $request->filtro;
        $articulos = Articulo::where('nombre','LIKE', '%'.$filtro.'%')
        ->select('id','nombre','precio_venta')->orderBy('nombre', 'asc')->take(20)->get();

        return ['articulos' => $articulos];
    }
    
    public function store(Request $request)
    {
        
        if (!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();

            $articulo = new Articulo();
            if($request->file('imagen')!=null){
                $img=$request->file('imagen');
                $nombreImg=time().'_'.$img->getclientoriginalname().'.'.$img->getClientOriginalExtension();
                $articulo->imagen=$nombreImg;
                $ruta=public_path('img');
                $request->imagen->move($ruta, $nombreImg);
            }else{
                
                $nombreImg='';
            }
            if($request->idarticulo_padre){
                $articulo->id_item_padre=$request->idarticulo_padre;
            }else{
                $articulo->id_item_padre=0;
            }
            
            $articulo->idcategoria = $request->idcategoria;
            $articulo->codigo = 1;
            $articulo->rangos=$request->rangos;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta;
            $articulo->stock = $request->stock;
            $articulo->descripcion = $request->descripcion;
            $articulo->condicion = 1;
            $articulo->save();
            $atributos=json_decode($request->atributos);
            foreach($atributos as $atr){
                $atributo=new Atributo();
                $atributo->id_articulo=$articulo->id;
                $atributo->valor=$atr->valor;
                $atributo->tipo_campo=$atr->tipoCampo;
                $atributo->tipo_valor=$atr->tipoValor;
                $atributo->nombre=$atr->nombreAtributo;
                $atributo->descripcion=$atr->descripcionAtributo;
                $atributo->alerta=$atr->alertaAtributo;
                $atributo->unidad_medida=$atr->unidadmedida;
                $atributo->operacion=$atr->operacion;
                $atributo->orden=$atr->ordenAtributo;
                $atributo->minimo=$atr->minimoValor;
                $atributo->maximo=$atr->maximoValor;
                $atributo->save();
                $opciones=$atr->opciones;
                
                foreach($opciones as $op){
                    $opcion=new OpcionAtributo();
                    $opcion->id_atributo=$atributo->id;
                    $opcion->label=$op->labelOpAtributo;
                    $opcion->valor=$op->valorOpAtributo;
                    $opcion->descripcion=$op->descripcionOpAtributo;
                    $opcion->alerta=$op->alertaOpAtributo;
                    $opcion->orden=$op->posicionOpAtributo;
                    $opcion->save();
                }

            }
            DB::commit();
        } catch (Exception $e){
            DB::rollBack();
        }
       
    }
    
    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();    
            $articulo = Articulo::findOrFail($request->id);
            //$articulo = Articulo::where('id', $request->id)->get();
            if($request->idarticulo_padre){
                $articulo->id_item_padre=$request->idarticulo_padre;
            }else{
                $articulo->id_item_padre=0;
            }
            $articulo->idcategoria = $request->idcategoria;
            $articulo->codigo = 1;
            $articulo->rangos=$request->rangos;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta;
            $articulo->stock = $request->stock;
            $articulo->descripcion = $request->descripcion;
            $articulo->condicion = 1;
            if($request->file('imagen')!=null){
                $img=$request->file('imagen');
                $nombreImg=time().'.'.$img->getClientOriginalExtension();
                $articulo->imagen=$nombreImg;
                $ruta=public_path('img/productos');
                $request->imagen->move($ruta, $nombreImg);
            }else{
                $nombreImg='';
            }
            
            $articulo->save();
            $atributos=json_decode($request->atributos);
            foreach($atributos as $atr){
                if(empty($atr->id)){
                    $atributo=new Atributo();
                }else{
                    $atributo= Atributo::findOrFail($atr->id);
                }
                $atributo->id_articulo=$articulo->id;
                $atributo->valor=$atr->valor;
                $atributo->tipo_campo=$atr->tipoCampo;
                $atributo->tipo_valor=$atr->tipoValor;
                $atributo->nombre=$atr->nombreAtributo;
                $atributo->descripcion=$atr->descripcionAtributo;
                $atributo->alerta=$atr->alertaAtributo;
                $atributo->unidad_medida=$atr->unidadmedida;
                $atributo->operacion=$atr->operacion;
                $atributo->orden=$atr->ordenAtributo;
                $atributo->minimo=$atr->minimoValor;
                $atributo->maximo=$atr->maximoValor;
                $atributo->save();
                $opciones=$atr->opciones;
                foreach($opciones as $op){
                    if(empty($op->id)){
                        $opcion=new OpcionAtributo();
                    }else{
                        $opcion= OpcionAtributo::findOrFail($op->id);
                    }
                    $opcion->id_atributo=$atributo->id;
                    $opcion->label=$op->labelOpAtributo;
                    $opcion->valor=$op->valorOpAtributo;
                    $opcion->descripcion=$op->descripcionOpAtributo;
                    $opcion->alerta=$op->alertaOpAtributo;
                    $opcion->orden=$op->posicionOpAtributo;
                    $opcion->save();
                }

            }
            DB::commit();
        } catch (Exception $e){
            DB::rollBack();
        }
        
    }

    public function eliminar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->delete();
        $subarticulo=Articulo::where('id_item_padre',$articulo->id);
        $subarticulo->delete();
        return $request->id;
    }

    public function activar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '1';
        $articulo->save();
    }

}
