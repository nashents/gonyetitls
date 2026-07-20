<?php

namespace App\Console;

use App\Console\Commands\AccrueEmployeeLeave;
use App\Console\Commands\CarryForwardEmployeeLeave;
use Carbon\Carbon;
use App\Console\Commands\SyncChangeLog;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FitnessReminderCommand;
use App\Console\Commands\GenerateZimbabweHolidays;
use App\Console\Commands\SendDailyReports;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FitnessReminderCommand::class,
        AccrueEmployeeLeave::class,
        CarryForwardEmployeeLeave::class,
        SendDailyReports::class,
        GenerateZimbabweHolidays::class,
        SyncChangeLog::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reminder:send')
        ->timezone('Africa/Harare');
      
        $schedule->command('reports:send-daily')
        ->dailyAt('10:30')
        ->timezone('Africa/Harare')
        ->withoutOverlapping()
        ->onOneServer();

        // Runs just before the Jan 1 accrual so the new year's accrual builds on the carried-forward balance
        $schedule->command('employees:carry-forward-leave')
        ->yearlyOn(12, 31, '23:50')
        ->timezone('Africa/Harare')
        ->withoutOverlapping()
        ->onOneServer();

        $schedule->command('employees:accrue-leave')
        ->monthlyOn(1, '00:00')
        ->timezone('Africa/Harare')        // your timezone
        ->withoutOverlapping()
        ->onOneServer();

        $schedule->command('holidays:generate ' . (now()->year + 1))
            ->yearlyOn(12, 1, '00:10');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
