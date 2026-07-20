<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CarryForwardEmployeeLeave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan employees:carry-forward-leave
     */
    protected $signature = 'employees:carry-forward-leave';

    /**
     * The console command description.
     */
    protected $description = 'Year-end rollover of unused leave balances, capped by each leave type\'s max carry forward days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting year-end leave carry forward...');

        $processed = 0;

        DB::transaction(function () use (&$processed) {
            EmployeeLeave::query()
                ->whereHas('leave_type', function ($query) {
                    $query->where('active', true);
                })
                ->whereHas('employee', function ($query) {
                    $query->where('status', true)
                        ->where('archive', false);
                })
                ->with('leave_type')
                ->chunkById(200, function ($employeeLeaves) use (&$processed) {
                    foreach ($employeeLeaves as $employeeLeave) {
                        $leaveType = $employeeLeave->leave_type;

                        $carried = $leaveType->carry_forward_allowed
                            ? min((float) $employeeLeave->available_leave_days, (float) $leaveType->max_carry_forward_days)
                            : 0;

                        $employeeLeave->update([
                            'opening_balance' => $carried,
                            'carried_forward_days' => $carried,
                            'available_leave_days' => $carried,
                        ]);

                        // Legacy route: keep the Employee table in sync, Annual Leave only
                        if (strtolower($leaveType->name) === 'annual') {
                            Employee::where('id', $employeeLeave->employee_id)->update([
                                'leave_days' => $carried,
                            ]);
                        }

                        $processed++;
                    }
                });
        });

        $this->info("Leave carry forward completed successfully. {$processed} employee leave record(s) processed.");

        return Command::SUCCESS;
    }
}
