<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Superadministrador
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->idrol !== 'Superadministrador') {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acceso denegado: Solo el usuario Superadministrador puede eliminar registros o realizar esta acción.'
                ], 403);
            }
            return response('Acceso denegado: Solo el usuario Superadministrador puede eliminar registros o realizar esta acción.', 403);
        }

        return $next($request);
    }
}
