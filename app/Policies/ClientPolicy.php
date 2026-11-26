<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Voir la liste des clients.
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
     * Voir un client.
     */
    public function view(User $user, Client $client): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Créer un client.
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
     * Modifier un client.
     */
    public function update(User $user, Client $client): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
            'employee',
        ], true);
    }

    /**
     * Supprimer un client.
     */
    public function delete(User $user, Client $client): bool
    {
        return in_array($user->role, [
            'admin',
            'manager',
        ], true);
    }
}
