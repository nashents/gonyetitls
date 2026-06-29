<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PayrollPolicy
{
    use HandlesAuthorization;

    // ── Role/department helpers ───────────────────────────────────────────

    private function isSuperAdmin(User $user): bool
    {
        return $user->roles->pluck('name')->contains('Super Admin')
            || $user->roles->pluck('name')->contains('Admin');
    }

    private function isHR(User $user): bool
    {
        return $user->employee?->departments->pluck('name')->contains('Human Resources') ?? false;
    }

    private function isFinance(User $user): bool
    {
        return $user->employee?->departments->pluck('name')->contains('Finance') ?? false;
    }

    private function isManagement(User $user): bool
    {
        return $user->employee?->departments->pluck('name')->contains('Management') ?? false;
    }

    private function sameCompany(User $user, Payroll $payroll): bool
    {
        return $user->employee?->company_id === $payroll->company_id;
    }

    // ── Policy methods ────────────────────────────────────────────────────

    /**
     * HR, Finance, Super Admin and Management can list payrolls.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    /**
     * Same roles as viewAny, scoped to same company.
     */
    public function view(User $user, Payroll $payroll): bool
    {
        return $this->sameCompany($user, $payroll)
            && ($this->isSuperAdmin($user) || $this->isHR($user) || $this->isFinance($user) || $this->isManagement($user));
    }

    /**
     * Only HR can create payroll runs.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isHR($user);
    }

    /**
     * HR can edit payroll only while it is still pending (draft/not approved).
     */
    public function update(User $user, Payroll $payroll): bool
    {
        if (!$this->sameCompany($user, $payroll)) return false;

        // Locked payrolls are immutable
        if ($payroll->authorization === 'approved') {
            return $this->isSuperAdmin($user); // only super admin can touch an approved payroll
        }

        return $this->isSuperAdmin($user) || $this->isHR($user);
    }

    /**
     * Only Super Admin can delete, and only when still pending.
     */
    public function delete(User $user, Payroll $payroll): bool
    {
        return $this->isSuperAdmin($user)
            && $this->sameCompany($user, $payroll)
            && $payroll->authorization === 'pending';
    }

    /**
     * Finance or Management approves. Segregation of duties:
     * the person who created the payroll cannot approve it.
     */
    public function approve(User $user, Payroll $payroll): bool
    {
        if (!$this->sameCompany($user, $payroll)) return false;
        if ($payroll->authorization !== 'pending') return false;

        $isApprover = $this->isSuperAdmin($user) || $this->isFinance($user) || $this->isManagement($user);

        // Segregation of duties — creator cannot approve
        $isCreator = $payroll->user_id === $user->id;

        return $isApprover && !$isCreator;
    }

    /**
     * Reversal requires Super Admin or Finance, and approval is mandatory.
     */
    public function reverse(User $user, Payroll $payroll): bool
    {
        return $this->sameCompany($user, $payroll)
            && ($this->isSuperAdmin($user) || $this->isFinance($user))
            && $payroll->authorization === 'approved';
    }

    public function restore(User $user, Payroll $payroll): bool
    {
        return $this->isSuperAdmin($user) && $this->sameCompany($user, $payroll);
    }

    public function forceDelete(User $user, Payroll $payroll): bool
    {
        return $this->isSuperAdmin($user) && $this->sameCompany($user, $payroll);
    }
}
