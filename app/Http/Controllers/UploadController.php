<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Imagenes;
use GuzzleHttp;

class UploadController extends Controller
{
    public function Subir(Request $request){
    }
    public function borrar(Request $request){
        $file=Imagenes::where('id', $request->id)->first();
        $id_tabla=$request->idtabla;
        $ruta=public_path('img/productos/'); 
        // $ruta=app_path().'img/productos/'; 
        $file_path = $ruta.$file->nombre;
        // unlink($file_path);
        $file->delete($file_path);
        $files=Imagenes::where('id_tabla',$id_tabla)->get();
        return $files;
    }
}
