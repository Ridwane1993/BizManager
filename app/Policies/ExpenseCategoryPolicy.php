<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;

class ExpenseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $this->belongsToCompany($user, $expenseCategory)
            && $this->canManage($user);
    }

    public function delete(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $this->belongsToCompany($user, $expenseCategory)
            && $this->canManage($user);
    }

    private function canManage(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role, ['admin', 'manager'], true);
    }

    private function belongsToCompany(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->company_id !== null
            && (int) $user->company_id === (int) $expenseCategory->company_id;
    }
}
