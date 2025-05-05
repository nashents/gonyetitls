<?php

namespace App\Console\Commands;

use DB;
use App\Models\Employee;
use Illuminate\Console\Command;

class AccrueLeaveDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:accrue-leave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrues leave_days for employees based on their accrual_rate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::statement('UPDATE employees SET leave_days = leave_days + accrual_rate');

        $this->info('Leave days accrued successfully.');
    }
}
