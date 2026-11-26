<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

trait TenantValidation
{
    /**
     * L'entreprise du user authentifié (les routes concernées sont protégées).
     */
    protected function companyId(): mixed
    {
        return $this->user()?->company_id;
    }

    /**
     * Rule::exists() n'hérite pas du scope global des modèles : le filtre
     * company_id doit rester explicite pour éviter toute fuite multi-entreprise.
     */
    protected function existsForCompany(
        string $table,
        string $column = 'id'
    ): Exists {
        return Rule::exists($table, $column)
            ->where('company_id', $this->companyId());
    }

    protected function uniqueForCompany(
        string $table,
        string $column,
        mixed $ignore = null
    ): Unique {
        $rule = Rule::unique($table, $column)
            ->where('company_id', $this->companyId());

        return $ignore === null ? $rule : $rule->ignore($ignore);
    }
}
