<?php

namespace App\Policies;

use App\Models\PayrollPayslip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPayslipPolicy
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

    private function isPrivileged(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    /**
     * Payslips are sensitive. Only HR, Finance, Management and Super Admin can list all.
     * Employees see only their own via the view check.
     */
    public function viewAny(User $user): bool
    {
        return $this->isPrivileged($user);
    }

    /**
     * Employee can view their own payslip only. Privileged roles see any.
     */
    public function view(User $user, PayrollPayslip $payslip): bool
    {
        return $user->employee?->id === $payslip->employee_id
            || $this->isPrivileged($user);
    }

    /**
     * Payslips are system-generated. No one creates them manually.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Payslips are immutable once generated. No edits allowed.
     */
    public function update(User $user, PayrollPayslip $payslip): bool
    {
        return false;
    }

    /**
     * Only Super Admin can delete a payslip (e.g. after reversal).
     */
    public function delete(User $user, PayrollPayslip $payslip): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Employee can download their own payslip. Privileged roles can download any.
     */
    public function download(User $user, PayrollPayslip $payslip): bool
    {
        return $user->employee?->id === $payslip->employee_id
            || $this->isPrivileged($user);
    }

    public function restore(User $user, PayrollPayslip $payslip): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user, PayrollPayslip $payslip): bool
    {
        return $this->isSuperAdmin($user);
    }
}
