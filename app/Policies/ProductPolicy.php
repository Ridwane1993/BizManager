<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Voir la liste des produits.
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
     * Voir un produit.
     */
    public function view(User $user, Product $product): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Créer un produit.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Modifier un produit.
     */
    public function update(User $user, Product $product): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Supprimer un produit.
     */
    public function delete(User $user, Product $product): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
