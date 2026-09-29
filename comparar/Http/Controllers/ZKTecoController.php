<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ZKTecoController extends Controller
{
    public function handlePush(Request $request)
    {
         \Log::info('ZK Push data:', $request->all());   // Dump para ver qué está llegando
       
    \Log::info('Body raw:', [$request->getContent()]);

    return response()->json([
        'status' => 'ok',
        'received_raw' => $request->getContent(),
        'received_all' => $request->all(),
    ]);
    }
}