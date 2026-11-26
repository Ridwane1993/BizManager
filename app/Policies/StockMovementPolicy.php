<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;

class StockMovementPolicy
{
    /**
     * Voir la liste des mouvements.
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
     * Voir un mouvement.
     */
    public function view(
        User $user,
        StockMovement $stockMovement
    ): bool {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Créer un mouvement manuel.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Modifier un mouvement.
     *
     * Seuls les mouvements manuels peuvent être modifiés.
     */
    public function update(
        User $user,
        StockMovement $stockMovement
    ): bool {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true)
        && $stockMovement->isManual();
    }

    /**
     * Supprimer un mouvement.
     *
     * Seuls les mouvements manuels peuvent être supprimés.
     */
    public function delete(
        User $user,
        StockMovement $stockMovement
    ): bool {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true)
        && $stockMovement->isManual();
    }
}
