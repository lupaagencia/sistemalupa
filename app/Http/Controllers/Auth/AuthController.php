<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;

class AuthController extends Controller
{
    public function verificarClave(Request $request)
    {
        $clave = $request->input('clave');

        if (empty($clave)) {
            return response()->json(false);
        }

        // 1. Check currently logged in user
        if (Auth::check()) {
            $user = Auth::user();
            if (Hash::check($clave, $user->password)) {
                return response()->json(true);
            }
        }

        // 2. Check all users in database (e.g. admins/managers)
        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            if ($usuario->password && Hash::check($clave, $usuario->password)) {
                return response()->json(true);
            }
        }

        return response()->json(false);
    }
}