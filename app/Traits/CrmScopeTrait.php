<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\User;

trait CrmScopeTrait
{
    /**
     * Scope query based on authenticated user's role and target filterUserId parameter.
     *
     * Hierarchy:
     * - Vendedor: strictly own records (user_id = Auth::id())
     * - Gerente Comercial: own records + records of all users with role 'Vendedor'
     * - Administrador / Coordinador: all records across all users
     */
    public function scopeCrmScope($query, $filterUserId = null)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return $query;
        }

        $role = $authUser->idrol;

        if ($role === 'Vendedor') {
            return $query->where('user_id', $authUser->id);
        }

        if ($role === 'Gerente Comercial') {
            $vendedorIds = User::where('idrol', 'Vendedor')->pluck('id')->toArray();
            $allowedIds = array_unique(array_merge([$authUser->id], $vendedorIds));

            if ($filterUserId && $filterUserId !== 'all' && $filterUserId !== 'equipo') {
                if (in_array((int)$filterUserId, $allowedIds)) {
                    return $query->where('user_id', (int)$filterUserId);
                }
            }

            return $query->whereIn('user_id', $allowedIds);
        }

        // Administrador / Coordinador
        if ($filterUserId && $filterUserId !== 'all' && $filterUserId !== 'equipo') {
            return $query->where('user_id', (int)$filterUserId);
        }

        return $query;
    }
}
