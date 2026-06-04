<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class EmployeeLeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $employees = Employee::all();
        $leaveTypes = LeaveType::all();

        foreach ($employees as $employee) {

            foreach ($leaveTypes as $leaveType) {

                $maximumDays = is_numeric($leaveType->entitlement)
                    ? (float) $leaveType->entitlement
                    : 0;

                $isAnnual = strtolower(trim($leaveType->name)) === 'annual';

                $accrualRate = $isAnnual
                    ? (
                        is_numeric($employee->accrual_rate)
                            ? (float) $employee->accrual_rate
                            : (float) ($leaveType->monthly_accrual_rate ?? 0)
                    )
                    : (float) ($leaveType->monthly_accrual_rate ?? 0);

                $approvedLeaveDaysTaken = Leave::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('hod_decision', 'approved')
                    ->where('management_decision', 'approved')
                    ->whereYear('from', date('Y'))
                    ->sum('days');

                $employeeLeave = EmployeeLeave::firstOrNew([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                ]);

                $employeeLeave->acrual_rate = $employeeLeave->acrual_rate ?? $accrualRate;
                $employeeLeave->maximum_leave_days = $employeeLeave->maximum_leave_days ?? $maximumDays;

                if (strtolower(trim($leaveType->name)) === 'annual') {

                    $employeeLeave->available_leave_days = is_numeric($employee->leave_days)
                        ? (float) $employee->leave_days
                        : max(0, $maximumDays - $approvedLeaveDaysTaken);

                } else {

                    $employeeLeave->available_leave_days = max(
                        0,
                        $maximumDays - $approvedLeaveDaysTaken
                    );
                }

                $employeeLeave->save();
            }
        }
        
    }
}
