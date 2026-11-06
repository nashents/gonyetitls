<?php

namespace App\Http\Middleware;

use App\Models\Leave;
use App\Models\Loan;
use App\Models\PayrollSalary;
use App\Support\HrAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Server-side half of the "HR access: drivers only" toggle (App\Support\HrAccess).
 * Hiding menu links is not enough — this stops a restricted user opening payroll
 * or an admin employee's record by URL. Livewire actions are guarded in the
 * components themselves (they don't pass through these routes).
 */
class HrDriversOnlyGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!HrAccess::driversOnly()) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route?->getName();

        if (!$name) {
            return $next($request);
        }

        if (HrAccess::isBlockedRoute($name)) {
            abort(403, 'Your HR access is limited to drivers.');
        }

        $employee = $this->employeeFor($name, $route);
        if ($employee !== false) {
            HrAccess::authorizeEmployee($employee);
        }

        return $next($request);
    }

    /**
     * The employee behind a record route, null when the record doesn't exist
     * (left for the controller to 404), or false when the route isn't HR-scoped.
     */
    private function employeeFor(string $name, $route)
    {
        $param = fn (...$keys) => collect($keys)->map(fn ($k) => $route->parameter($k))->first(fn ($v) => $v !== null);

        switch (true) {
            case in_array($name, ['employees.show', 'employees.edit', 'employees.update', 'employees.destroy',
                                  'employees.activate', 'employees.deactivate', 'employees.archive'], true):
                $employee = $param('employee', 'id');
                return $employee ?: false;

            case in_array($name, ['leaves.show', 'leaves.edit', 'leaves.update', 'leaves.destroy', 'leaves.decision'], true):
                $leave = $param('leaf', 'leave', 'id');
                $leave = $leave instanceof Leave ? $leave : Leave::withTrashed()->find($leave);
                return $leave ? $leave->employee_id : null;

            case in_array($name, ['loans.show', 'loans.edit', 'loans.update', 'loans.destroy', 'loans.decision'], true):
                $loan = $param('loan', 'id');
                $loan = $loan instanceof Loan ? $loan : Loan::withTrashed()->find($loan);
                // Vendor/business loans have no employee — finance, not HR
                if ($loan && !$loan->employee_id) {
                    return false;
                }
                return $loan ? $loan->employee_id : null;

            case in_array($name, ['payslips.print', 'payslips.preview', 'payslips.pdf'], true):
                $salary = PayrollSalary::find($param('id'));
                return $salary ? $salary->employee_id : null;
        }

        return false;
    }
}
