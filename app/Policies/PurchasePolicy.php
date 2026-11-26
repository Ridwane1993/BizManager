<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    /**
     * Voir la liste des achats.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Voir un achat.
     */
    public function view(User $user, Purchase $purchase): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Créer un achat.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Modifier un achat.
     */
    public function update(User $user, Purchase $purchase): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Supprimer un achat.
     */
    public function delete(User $user, Purchase $purchase): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
