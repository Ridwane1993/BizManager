<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Point d'entrée unique pour le cloisonnement des utilisateurs par entreprise.
 *
 * Ce scoping reste explicite : le modèle User ne reçoit pas de scope global,
 * afin de ne pas interférer avec l'inscription et les actions Fortify.
 */
final class UserCompanyScope
{
    public static function companyFor(User $user): Company
    {
        $company = $user->company;

        abort_if(
            $company === null,
            403,
            'Votre compte n’est associé à aucune entreprise.'
        );

        return $company;
    }

    public static function belongsTo(User $user, User $target): bool
    {
        return $user->company_id !== null
            && (int) $user->company_id === (int) $target->company_id;
    }

    public static function ensureBelongsTo(
        User $target,
        Company $company
    ): void {
        abort_unless(
            (int) $target->company_id === (int) $company->getKey(),
            404
        );
    }

    public static function queryFor(Company $company): Builder
    {
        return User::query()
            ->where('company_id', $company->getKey());
    }
}
