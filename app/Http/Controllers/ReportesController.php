<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Reportes;

class ReportesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $reportes = Reportes::select('*')->orderBy('created_at', 'DESC')->get();
        return $reportes;
    }
    public function delete(Request $request){
        $id=$request->id;
        
        $reporte = Reportes::find($id);
        if (file_exists('reportes/'.$reporte->archivo)){
            unlink('reportes/'.$reporte->archivo);
        }
        $reporte->delete();
    }
}
