<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Voir la liste des dépenses.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Voir une dépense.
     */
    public function view(User $user, Expense $expense): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Créer une dépense.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Modifier une dépense.
     */
    public function update(User $user, Expense $expense): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }

    /**
     * Supprimer une dépense.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
