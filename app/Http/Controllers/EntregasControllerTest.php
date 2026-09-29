<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entrega;
use App\EntregaItems;
use App\Ordentrabajo;
use App\LineaComprobante;
use App\Comprobante;
use App\Ajustes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class EntregasController extends Controller
{
    public function index()
    {
        return response()->json(Entrega::all());
    }
}
