<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    /**
     * Voir la liste des fournisseurs.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Voir un fournisseur.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Créer un fournisseur.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Modifier un fournisseur.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Supprimer un fournisseur.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
