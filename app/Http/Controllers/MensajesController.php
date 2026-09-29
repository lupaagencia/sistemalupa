<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MensajeRecibido;

class MensajesController extends Controller
{
    public function formularioContacto(Request $request){
        $msg=[
            'nombre'=>$request->nombre,
            'telefono'=>$request->telefono,
            'correo'=>$request->correo,
            'asunto'=>$request->asunto,
            'mensaje'=>$request->mensaje
        ];
        return Mail::to('empaqueslupa@gmail.com')->send(new MensajeRecibido($msg));
        

    }
}
