<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MensajeController extends Controller
{
    public function index()
    {
        return \App\Mensaje::latest()->take(50)->get()->reverse()->values();
    }

    public function store(Request $request)
    {
        $mensaje = new \App\Mensaje();
        $mensaje->usuario = $request->input('usuario', 'Anónimo');
        $mensaje->contenido = $request->input('contenido');
        $mensaje->save();

        return response()->json($mensaje, 201);
    }

    public function marcarLeidos(Request $request)
    {
        $usuario = $request->input('usuario');
        if ($usuario) {
            // Mark all messages NOT from me as read
            \App\Mensaje::where('usuario', '!=', $usuario)
                ->update(['leido' => true]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function destroyAll()
    {
        \App\Mensaje::truncate();
        return response()->json(['status' => 'ok']);
    }
}
