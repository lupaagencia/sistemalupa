<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait RestrictsDeletionTrait
{
    /**
     * Check if the current user has permission to delete records (only Superadministrador).
     * Returns null if authorized, or a 403 JsonResponse if unauthorized.
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    protected function checkSuperadminForDeletion()
    {
        $user = Auth::user();
        if (!$user || $user->idrol !== 'Superadministrador') {
            return response()->json([
                'status' => 'error',
                'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar registros.'
            ], 403);
        }
        return null;
    }
}
