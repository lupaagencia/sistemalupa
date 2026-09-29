<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; // ✅ correcta
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class AuthController extends Controller
{
    public function verificarClave(Request $request)
    {
        $clave = $request->input('clave');

        // Buscar usuario con idrol = 1
        $usuariosAdmin = User::where('idrol', 'Administrador')->get();

        foreach ($usuariosAdmin as $usuario) {
            if (Hash::check($clave, $usuario->password)) {
                return true;
            }
        }

        return false;
    }
}