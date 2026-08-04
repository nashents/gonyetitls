<?php

namespace App\Policies;

use App\Models\BankReconciliation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankReconciliationPolicy
{
    use HandlesAuthorization;

    private function isSuperAdmin(User $user): bool
    {
        return $user->roles->pluck('name')->contains('Super Admin')
            || $user->roles->pluck('name')->contains('Admin');
    }

    private function isFinance(User $user): bool
    {
        return $user->employee?->departments->pluck('name')->contains('Finance') ?? false;
    }

    private function sameCompany(User $user, BankReconciliation $reconciliation): bool
    {
        return $user->employee?->company_id === $reconciliation->company_id;
    }

    /** Finance and Super Admin can see reconciliation status/import statements. */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isFinance($user);
    }

    public function view(User $user, BankReconciliation $reconciliation): bool
    {
        return $this->sameCompany($user, $reconciliation)
            && ($this->isSuperAdmin($user) || $this->isFinance($user));
    }

    /** Starting a reconciliation period / importing a statement. */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isFinance($user);
    }

    /** Matching/unmatching lines, recording adjustment entries, completing/reopening. */
    public function update(User $user, BankReconciliation $reconciliation): bool
    {
        return $this->sameCompany($user, $reconciliation)
            && ($this->isSuperAdmin($user) || $this->isFinance($user));
    }

    /** Reopening a completed reconciliation is a control-sensitive action - restrict it to Super Admin. */
    public function reopen(User $user, BankReconciliation $reconciliation): bool
    {
        return $this->isSuperAdmin($user) && $this->sameCompany($user, $reconciliation);
    }

    public function delete(User $user, BankReconciliation $reconciliation): bool
    {
        return $this->isSuperAdmin($user)
            && $this->sameCompany($user, $reconciliation)
            && !$reconciliation->isCompleted();
    }
}
