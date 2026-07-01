<?php

namespace App\Policies;

use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollRunPolicy
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

    private function sameCompany(User $user, PayrollRun $run): bool
    {
        return $user->employee?->company_id === $run->company_id;
    }

    // ── Policy methods ────────────────────────────────────────────────────

    /**
     * HR, Finance, Super Admin and Management can list payroll runs.
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
    public function view(User $user, PayrollRun $run): bool
    {
        return $this->sameCompany($user, $run)
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
     * Covers the run's lifecycle actions (approve/lock/post/reverse), all of which
     * call authorize('update', $run) before mutating status.
     * Segregation of duties: the person who created the run cannot approve it.
     */
    public function update(User $user, PayrollRun $run): bool
    {
        if (!$this->sameCompany($user, $run)) return false;

        $isPrivileged = $this->isSuperAdmin($user) || $this->isHR($user) || $this->isFinance($user) || $this->isManagement($user);
        if (!$isPrivileged) return false;

        // Segregation of duties — creator cannot approve their own run
        if ($run->status === 'validated' && $run->created_by === $user->id) {
            return $this->isSuperAdmin($user);
        }

        return true;
    }

    public function delete(User $user, PayrollRun $run): bool
    {
        return $this->isSuperAdmin($user)
            && $this->sameCompany($user, $run)
            && $run->isDraft();
    }

    public function restore(User $user, PayrollRun $run): bool
    {
        return $this->isSuperAdmin($user) && $this->sameCompany($user, $run);
    }

    public function forceDelete(User $user, PayrollRun $run): bool
    {
        return $this->isSuperAdmin($user) && $this->sameCompany($user, $run);
    }
}
