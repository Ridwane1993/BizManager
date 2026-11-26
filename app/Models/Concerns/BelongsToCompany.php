<?php

namespace App\Models\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            $user = auth()->user();

            if ($user === null) {
                // Aucun utilisateur (CLI, queue, requête publique) : ne jamais
                // laisser échapper des données métier d'une entreprise.
                $builder->whereRaw('1 = 0');

                return;
            }

            if ($user->company_id === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn('company_id'),
                $user->company_id
            );
        });

        static::creating(function (Model $model): void {
            $user = auth()->user();

            if ($user === null) {
                throw new AuthorizationException(
                    'Aucun utilisateur authentifié : création de données métier refusée.'
                );
            }

            if ($user->company_id === null) {
                throw new AuthorizationException(
                    'Votre compte n’est associé à aucune entreprise.'
                );
            }

            if (
                $model->company_id !== null
                && (int) $model->company_id !== (int) $user->company_id
            ) {
                throw new AuthorizationException(
                    'Vous ne pouvez pas créer de données pour une autre entreprise.'
                );
            }

            $model->company_id = $user->company_id;
        });
    }
}
