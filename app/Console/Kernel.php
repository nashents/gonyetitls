<?php

namespace App\Console;

use App\Console\Commands\AccrueEmployeeLeave;
use Carbon\Carbon;
use App\Console\Commands\SyncChangeLog;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FitnessReminderCommand;
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
        SendDailyReports::class,
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
        ->dailyAt('08:00')
        ->timezone('Africa/Harare')
        ->withoutOverlapping()
        ->onOneServer();

        $schedule->command('employees:accrue-leave')
        ->monthlyOn(1, '00:05')            // 1st day of the month at 00:05
        ->timezone('Africa/Harare')        // your timezone
        ->withoutOverlapping()
        ->onOneServer();
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
