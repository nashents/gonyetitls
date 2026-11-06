<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * "HR access: drivers only" — a per-user toggle (users.hr_drivers_only) that any
 * HR user can be given regardless of post, role or department.
 *
 * A restricted user keeps everything generic, but in HR they can only work with
 * drivers: driver records, driver leave (capture + approve), driver loans and
 * driver payslips. Admin staff records, admin leave and payroll are off limits.
 *
 * is_admin users and Super Admins are never restricted.
 */
class HrAccess
{
    /**
     * Routes a drivers-only user may never open: payroll and admin-staff screens.
     * Patterns use Str::is() (route names).
     */
    public const BLOCKED_ROUTES = [
        // Payroll — runs mix admin staff and drivers, so the whole module is out
        'payroll-runs.*',
        'payroll-config.*',
        'salary-advances.*',
        'salaries.*',
        'salary_items.*',
        'payslips.index', 'payslips.create', 'payslips.store', 'payslips.show',
        'payslips.edit', 'payslips.update', 'payslips.destroy',
        'deductions.*',
        'earnings.*',
        'loan_types.*',

        // Employees module = admin staff (drivers live under Drivers)
        'employees.index',
        'employees.create',
        'employees.store',
        'employees.reports',
        'employees.export.*',
        'employees.import',

        // Bulk imports can't be limited row-by-row to drivers
        'employees.leaves.import',
        'leaves.import',
    ];

    /** Whether the given (or current) user has the drivers-only HR restriction. */
    public static function driversOnly(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user || !$user->hr_drivers_only) {
            return false;
        }

        return !($user->is_admin() || $user->isSuperAdmin());
    }

    public static function isBlockedRoute(?string $routeName, ?User $user = null): bool
    {
        if (!$routeName || !static::driversOnly($user)) {
            return false;
        }

        return Str::is(static::BLOCKED_ROUTES, $routeName);
    }

    /** Can the user see/act on this employee's HR record? */
    public static function canAccessEmployee($employee, ?User $user = null): bool
    {
        if (!static::driversOnly($user)) {
            return true;
        }

        if (!$employee instanceof Employee) {
            $employee = Employee::withTrashed()->find($employee);
        }

        return $employee && $employee->driver()->withTrashed()->exists();
    }

    /** Abort 403 unless the user may access this employee's HR record. */
    public static function authorizeEmployee($employee, ?User $user = null): void
    {
        abort_unless(static::canAccessEmployee($employee, $user), 403, 'Your HR access is limited to drivers.');
    }

    /** Loans: staff loans follow the employee rule; vendor/business loans (no employee) are not HR. */
    public static function authorizeLoan($loan, ?User $user = null): void
    {
        if ($loan && $loan->employee_id) {
            static::authorizeEmployee($loan->employee_id, $user);
        }
    }

    /** Staff type a list/export must use: drivers-only users are pinned to 'drivers'. */
    public static function staffType(?string $requested, ?User $user = null): string
    {
        if (static::driversOnly($user)) {
            return 'drivers';
        }

        return in_array($requested, ['all', 'admin', 'drivers'], true) ? $requested : 'all';
    }
}
