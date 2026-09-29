<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PeriodoContable;

class PeriodoContableController extends Controller
{
    public function index()
    {
        return PeriodoContable::orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->get();
    }

    public function toggle(Request $request)
    {
        $this->validate($request, [
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer',
            'estado' => 'required|string|in:Abierto,Cerrado'
        ]);

        $periodo = PeriodoContable::firstOrCreate(
            ['mes' => $request->mes, 'anio' => $request->anio],
            ['estado' => 'Abierto']
        );

        $periodo->estado = $request->estado;
        $periodo->save();

        return response()->json(['success' => true, 'periodo' => $periodo]);
    }
}
