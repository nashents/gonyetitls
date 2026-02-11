<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
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
    protected $description = 'Monthly accrual of leave days for all employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly leave accrual...');

        // If your columns are decimals, this is safe
        DB::transaction(function () {
            Employee::where('status', true)
                ->where('archive', false)
                ->whereNotNull('accrual_rate')
                ->where('accrual_rate', '>', 0)
                ->update([
                    'leave_days' => DB::raw('COALESCE(leave_days, 0) + accrual_rate'),
                    'updated_at' => now(),
                ]);
        });

        $this->info('Leave accrual completed successfully.');

        return Command::SUCCESS;
    }
}