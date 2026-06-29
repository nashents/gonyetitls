<?php

namespace App\Policies;

use App\Models\PayrollSalary;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollSalaryPolicy
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

    private function isOwnPayslip(User $user, PayrollSalary $salary): bool
    {
        return $user->employee?->id === $salary->employee_id;
    }

    private function isPayrollLocked(PayrollSalary $salary): bool
    {
        // Check new is_locked column (added in Phase 1 migration) or parent payroll status
        if (isset($salary->is_locked) && $salary->is_locked) return true;
        return $salary->payroll?->authorization === 'approved';
    }

    /**
     * HR, Finance, Management and Super Admin can list salary lines.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    /**
     * Salary line is viewable by: the employee themselves, HR, Finance, Management, Super Admin.
     */
    public function view(User $user, PayrollSalary $salary): bool
    {
        return $this->isOwnPayslip($user, $salary)
            || $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    /**
     * Only HR can create salary lines, and only on unlocked payrolls.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isHR($user);
    }

    /**
     * HR can update salary lines only while the parent payroll is not locked/approved.
     */
    public function update(User $user, PayrollSalary $salary): bool
    {
        if ($this->isPayrollLocked($salary)) {
            return Response::deny('This payroll has been approved and is locked. No edits are permitted.');
        }

        return $this->isSuperAdmin($user) || $this->isHR($user);
    }

    /**
     * Only Super Admin can delete, and only on unlocked payrolls.
     */
    public function delete(User $user, PayrollSalary $salary): bool
    {
        if ($this->isPayrollLocked($salary)) {
            return false;
        }

        return $this->isSuperAdmin($user);
    }

    /**
     * Employee can download their own payslip. HR, Finance, Management, Super Admin can download any.
     */
    public function downloadPayslip(User $user, PayrollSalary $salary): bool
    {
        return $this->isOwnPayslip($user, $salary)
            || $this->isSuperAdmin($user)
            || $this->isHR($user)
            || $this->isFinance($user)
            || $this->isManagement($user);
    }

    public function restore(User $user, PayrollSalary $salary): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user, PayrollSalary $salary): bool
    {
        return $this->isSuperAdmin($user) && !$this->isPayrollLocked($salary);
    }
}
