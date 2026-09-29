<?php

namespace App\Http\Controllers;

use App\CostoArticulo;
use Illuminate\Http\Request;

class CostoArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CostoArticulo  $costoArticulo
     * @return \Illuminate\Http\Response
     */
    public function show(CostoArticulo $costoArticulo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CostoArticulo  $costoArticulo
     * @return \Illuminate\Http\Response
     */
    public function edit(CostoArticulo $costoArticulo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CostoArticulo  $costoArticulo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CostoArticulo $costoArticulo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CostoArticulo  $costoArticulo
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $id=$request->id;
        $costo = CostoArticulo::find($id);
        $costo->delete();
    }
}
