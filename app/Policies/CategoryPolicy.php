<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Voir la liste des catégories.
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
     * Créer une catégorie.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Modifier une catégorie.
     */
    public function update(User $user, Category $category): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Supprimer une catégorie.
     */
    public function delete(User $user, Category $category): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
