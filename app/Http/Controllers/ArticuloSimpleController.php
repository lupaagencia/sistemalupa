<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Articulo;
use GuzzleHttp;
use Storage;

class ArticuloController extends Controller
{
    public function send(Request $request){
        echo 'hola';

    }
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->orderBy('articulos.id', 'desc')->paginate(20);
        }
        else{
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('articulos.id', 'desc')->paginate(20);
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
            'articulos' => $articulos
        ];
    }
    public function listarArticulo(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->orderBy('articulos.id', 'desc')->paginate(10);
        }
        else{
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('articulos.id', 'desc')->paginate(10);
        }
        

        return [  'articulos' => $articulos ];
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
        ->select('id','nombre','precio_venta')->orderBy('nombre', 'asc')->take(1)->get();

        return ['articulos' => $articulos];
    }
    public function selectArticulobyid(Request $request){
        if (!$request->ajax()) return redirect('/');

        $id = $request->id;
        $articulos = Articulo::where('id','=', $id)
        ->select('*')->get();
        return $articulos;
    }
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $articulo = new Articulo();
        $articulo->idcategoria = $request->idcategoria;
        $articulo->codigo = $request->codigo;
        $articulo->nombre = $request->nombre;
        $articulo->precio_venta = $request->precio_venta;
        $articulo->stock = $request->stock;
        $articulo->descripcion = $request->descripcion;
        $articulo->condicion = '1';
        $articulo->save();
    }
    public function update(Request $request)
    {
        
        if (!$request->ajax()) return redirect('/');
       
            if($request->id==0){
                $articulo = new Articulo();
            }else{
                $articulo = Articulo::findOrFail($request->id);
            }
            
            $articulo->idcategoria = $request->idcategoria;
            $articulo->codigo = $request->codigo;
            $articulo->nombre = $request->nombre;
            $articulo->precio_venta = $request->precio_venta;
            $articulo->stock = $request->stock;
            $articulo->descripcion = $request->descripcion;
            $articulo->condicion = '1';
            $articulo->save();
            $newArticulo = Articulo::select('*')->get()->last();
            return $newArticulo;
       
    
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '0';
        $articulo->save();
    }

    public function activar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $articulo = Articulo::findOrFail($request->id);
        $articulo->condicion = '1';
        $articulo->save();
    }

}
