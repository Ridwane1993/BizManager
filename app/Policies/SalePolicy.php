<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Voir la liste des ventes.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Voir une vente.
     */
    public function view(User $user, Sale $sale): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Créer une vente.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Modifier une vente.
     */
    public function update(User $user, Sale $sale): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Supprimer une vente.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
