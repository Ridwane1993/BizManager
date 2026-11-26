<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role, ['admin', 'manager', 'employee'], true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->belongsToCompany($user, $invoice)
            && in_array($user->role, ['admin', 'manager', 'employee'], true);
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role, ['admin', 'manager', 'employee'], true);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->belongsToCompany($user, $invoice)
            && in_array($user->role, ['admin', 'manager'], true);
    }

    private function belongsToCompany(User $user, Invoice $invoice): bool
    {
        return $user->company_id !== null
            && (int) $user->company_id === (int) $invoice->company_id;
    }
}
