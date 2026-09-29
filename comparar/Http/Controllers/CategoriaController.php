<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Categoria;
use App\Articulo;
use Illuminate\Support\Facades\File;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //if(!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $categorias = Categoria::with('padre')->orderBy('condicion', 'asc')->paginate(1000);
        }
        else{
            $categorias = Categoria::with('padre')->where($criterio, 'like', '%'. $buscar . '%')->orderBy('condicion', 'asc')->paginate(1000);
        }
        
        return [
            'pagination' => [
                'total'        => $categorias->total(),
                'current_page' => $categorias->currentPage(),
                'per_page'     => $categorias->perPage(),
                'last_page'    => $categorias->lastPage(),
                'from'         => $categorias->firstItem(),
                'to'           => $categorias->lastItem(),
            ],
            'categorias' => $categorias
        ];
    }
    public function categoriaMenu(Request $request)
    {
        // Traer categorias raiz con sus subniveles
        $categoriasMenu = Categoria::whereNull('padre_id')->with('sublevels')->orderBy('condicion', 'asc')->get();
        return $categoriasMenu;
    }
    public function categorias(Request $request)
    {
        //if(!$request->ajax()) return redirect('/');
        $categoriasMenu=[];
        $categorias = Categoria::all();
       
        
        return  $categorias;
       
    }

    public function selectCategoria(Request $request){
        if(!$request->ajax()) return redirect('/');
        $categorias=Categoria::select('id','nombre')->orderBy('condicion', 'asc')->get();
        return['categorias'=>$categorias]; 

    }
    public function articulosCategoria(Request $request){
        if(!$request->ajax()) return redirect('/');
        $articulos=Articulo::select('*')->where('idcategoria', $request->id)->get();
        return['articulos'=>$articulos]; 

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        if($request->id>0){
            $categoria=Categoria::find($request->id);
        }else{

            $categoria = new Categoria();
        }
        $categoria->nombre = $request->nombre;
        $categoria->descripcion = $request->descripcion;
        $categoria->condicion = $request->condicion;
        $categoria->padre_id = $request->padre_id ? $request->padre_id : null;
        if ($request->hasFile('imagen')) {
            $rutaImagenAnterior = public_path('img/categorias/' . $request->imagen2);

            if ($request->imagen2 && File::exists($rutaImagenAnterior)) {
                File::delete($rutaImagenAnterior);
            }
            $file = $request->file('imagen');
            $nombreArchivo = $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->imagen = $nombreArchivo;
        }else{
            if($request->imagen2) $categoria->imagen=$request->imagen2;
        }
        if ($request->hasFile('banner')) {
            $rutaBannerAnterior = public_path('img/categorias/' . $request->banner2);

            if ($request->banner2 && File::exists($rutaBannerAnterior)) {
                File::delete($rutaBannerAnterior);
            }
            $file = $request->file('banner');
            $nombreArchivo = 'banner_' . time() . '_' . $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->banner = $nombreArchivo;
        }else{
            if($request->banner2) $categoria->banner=$request->banner2;
        }
        $categoria->save();
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
        if(!$request->ajax()) return redirect('/');
        $categoria = Categoria::findOrFail($request->id);
        $categoria->nombre = $request->nombre;
        $categoria->descripcion = $request->descripcion;
        $categoria->condicion = $request->condicion;
        $categoria->padre_id = $request->padre_id ? $request->padre_id : null;
        if ($request->hasFile('imagen')) {
            $rutaImagenAnterior = public_path('img/categorias/' . $request->imagen2);

            if ($request->imagen2 && File::exists($rutaImagenAnterior)) {
                File::delete($rutaImagenAnterior);
            }
            $file = $request->file('imagen');
            $nombreArchivo = $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->imagen = $nombreArchivo;
        }
        if ($request->hasFile('banner')) {
            $rutaBannerAnterior = public_path('img/categorias/' . $request->banner2);

            if ($request->banner2 && File::exists($rutaBannerAnterior)) {
                File::delete($rutaBannerAnterior);
            }
            $file = $request->file('banner');
            $nombreArchivo = 'banner_' . time() . '_' . $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->banner = $nombreArchivo;
        }
        $categoria->save();
    }
    
    public function eliminar(Request $request)
    {
       
        if (!$request->ajax()) return redirect('/');
        $articulos=Articulo::select('*')->where('idcategoria', $request->id)->get();
        foreach($articulos as $a){
            $a->idcategoria=$request->updateid;
            $a->save();
        }
        $categoria = Categoria::findOrFail($request->id);
        $categoria->delete();
       
    }
    public function eliminarC(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $categoria = Categoria::findOrFail($request->id);
        $categoria->delete();
       
    }

    public function activar(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        $categoria = Categoria::findOrFail($request->id);
        $categoria->condicion = '1';
        $categoria->save();
    }
}
