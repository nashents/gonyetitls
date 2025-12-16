<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{
    Allocation, Department, DepartmentHead, Leave, Loan,
    Payroll, Invoice, CreditNote, Bill, Requisition, TopUp,
    // ... add all models you need here
};

class SidebarComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        $employee = $user?->employee;

        // Collections instead of foreach + push
        $department_names = $employee
            ? $employee->departments->pluck('name')->all()
            : [];

        $role_names = $user
            ? $user->roles->pluck('name')->all()
            : [];

        $rank_names = $employee
            ? $employee->ranks->pluck('name')->all()
            : [];

        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek();
        $endOfWeek   = $now->copy()->endOfWeek();

        // Example: a helper for weekly counts (you can DRY this further)
        $weeklyCount = function ($model, $extra = []) use ($startOfWeek, $endOfWeek) {
            return $model::where($extra)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count();
        };

        $myAllocationCount = $employee
            ? Allocation::where('employee_id', $employee->id)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count()
            : 0;

        // Example departments (load once, reuse everywhere)
        $departments = Department::whereIn('name', [
            'HSEQ',
            'Workshop',
            'Finance',
            'Human Resources',
            'Security',
            'Transport & Logistics',
            'Stores',
        ])->get()->keyBy('name');

        // Example: leave stats
        $leavesPendingCount   = $weeklyCount(Leave::class,   ['status' => 'pending']);
        $leavesApprovedCount  = $weeklyCount(Leave::class,   ['status' => 'approved']);
        $leavesRejectedCount  = $weeklyCount(Leave::class,   ['status' => 'rejected']);
        $leavesDeletedCount   = Leave::onlyTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $hrDepartment   = $departments->get('Human Resources');
        $hrDeptHead     = $employee && $hrDepartment
            ? DepartmentHead::where('department_id', $hrDepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $hseq_department   = $departments->get('HSEQ');
        $wsdepartment   = $departments->get('Workshop');
        $wsdepartment_head     = $employee && $wsdepartment
            ? DepartmentHead::where('department_id', $wsdepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;
        $stdepartment   = $departments->get('Stores');
        $stdepartment_head     = $employee && $stdepartment
            ? DepartmentHead::where('department_id', $stdepartment->id)
                  ->where('employee_id', $employee->id)
                  ->first()
            : null;

        // ... same pattern for Finance, HSEQ, etc.

        $isSuperAdmin      = in_array('Super Admin', $role_names);
        $isAdmin           = in_array('Admin', $role_names);
        $inHR              = in_array('Human Resources', $department_names);
        $inFinance         = in_array('Finance', $department_names);
        $inHSEQ            = in_array('HSEQ', $department_names);
        $inTransport       = in_array('Transport & Logistics', $department_names);

        $view->with([
            'user'         => $user,
            'employee'     => $employee,
            'department_names'     => $department_names,
            'role_names'           => $role_names,
            'rank_names'           => $rank_names,
            'myAllocationCount'   => $myAllocationCount,
            'departmentsMap'      => $departments,
            'leavesPendingCount'  => $leavesPendingCount,
            'leavesApprovedCount' => $leavesApprovedCount,
            'leavesRejectedCount' => $leavesRejectedCount,
            'leavesDeletedCount'  => $leavesDeletedCount,
            'hrDeptHead'          => $hrDeptHead,
            'inTransport'          => $inTransport,
            'inHSEQ'          => $inHSEQ,
            'inFinance'          => $inFinance,
            'inHR'          => $inHR,
            'isAdmin'          => $isAdmin,
            'isSuperAdmin'          => $isSuperAdmin,
            // add other counters (loans, payrolls, invoices, bills, etc.)
        ]);
    }
}