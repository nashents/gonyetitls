<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccrueEmployeeLeave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan employees:accrue-leave
     */
    protected $signature = 'employees:accrue-leave';

    /**
     * The console command description.
     */
    protected $description = 'Monthly accrual of leave days for all employees, driven by each leave type\'s accrual settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly leave accrual...');

        $accrued = 0;

        DB::transaction(function () use (&$accrued) {
            EmployeeLeave::query()
                ->whereHas('leave_type', function ($query) {
                    $query->where('active', true)
                        ->where('is_accruable', true);
                })
                ->whereHas('employee', function ($query) {
                    $query->where('status', true)
                        ->where('archive', false);
                })
                // Skip records already accrued for the current month
                ->where(function ($query) {
                    $query->whereNull('last_accrual_date')
                        ->orWhereDate('last_accrual_date', '<', now()->startOfMonth());
                })
                ->with('leave_type')
                ->chunkById(200, function ($employeeLeaves) use (&$accrued) {
                    foreach ($employeeLeaves as $employeeLeave) {
                        // Employee-specific rate overrides the leave type's default rate
                        $rate = $employeeLeave->acrual_rate ?: $employeeLeave->leave_type->monthly_accrual_rate;

                        if (! $rate || $rate <= 0) {
                            continue;
                        }

                        $newBalance = (float) $employeeLeave->available_leave_days + $rate;

                        if ($employeeLeave->maximum_leave_days > 0) {
                            $newBalance = min($newBalance, $employeeLeave->maximum_leave_days);
                        }

                        $employeeLeave->update([
                            'available_leave_days' => $newBalance,
                            'days_accrued' => (float) $employeeLeave->days_accrued + $rate,
                            'last_accrual_date' => now()->toDateString(),
                        ]);

                        // Legacy route: keep the Employee table in sync, Annual Leave only
                        if (strtolower($employeeLeave->leave_type->name) === 'annual') {
                            Employee::where('id', $employeeLeave->employee_id)->update([
                                'accrual_rate' => $rate,
                                'leave_days' => $newBalance,
                            ]);
                        }

                        $accrued++;
                    }
                });
        });

        $this->info("Leave accrual completed successfully. {$accrued} employee leave record(s) accrued.");

        return Command::SUCCESS;
    }
}