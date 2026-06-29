<?php

namespace App\Policies;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryPolicy
{
    use HandlesAuthorization;

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

    /**
     * Salary structures are HR-confidential.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    /**
     * An employee may view their own salary record only.
     * Privileged roles may view any.
     */
    public function view(User $user, Salary $salary): bool
    {
        return $user->employee?->id === $salary->employee_id
            || $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    /**
     * Only HR and Super Admin can create salary records.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isHR($user);
    }

    /**
     * Only HR and Super Admin can update salary records.
     * Payroll lock enforcement is done at the Livewire component level.
     */
    public function update(User $user, Salary $salary): bool
    {
        return $this->isSuperAdmin($user) || $this->isHR($user);
    }

    /**
     * Only Super Admin can delete salary records.
     */
    public function delete(User $user, Salary $salary): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function restore(User $user, Salary $salary): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user, Salary $salary): bool
    {
        return $this->isSuperAdmin($user);
    }
}
