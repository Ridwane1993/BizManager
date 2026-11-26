<?php

namespace App\Policies;

use App\Models\User;
use App\Support\UserCompanyScope;

class UserPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir la liste des utilisateurs.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            && $user->company_id !== null;
    }

    /**
     * Déterminer si l'utilisateur peut voir un utilisateur.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin()
            && UserCompanyScope::belongsTo($user, $model);
    }

    /**
     * Déterminer si l'utilisateur peut créer un utilisateur.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin()
            && $user->company_id !== null;
    }

    /**
     * Déterminer si l'utilisateur peut modifier un utilisateur.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin()
            && UserCompanyScope::belongsTo($user, $model);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un utilisateur.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin()
            && UserCompanyScope::belongsTo($user, $model);
    }

    /**
     * Déterminer si l'utilisateur peut restaurer un utilisateur.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin()
            && UserCompanyScope::belongsTo($user, $model);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer définitivement un utilisateur.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin()
            && UserCompanyScope::belongsTo($user, $model);
    }
}
