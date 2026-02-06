<?php

namespace App\Console;

use Carbon\Carbon;
use App\Console\Commands\SyncChangeLog;
use App\Console\Commands\AccrueLeaveDays;
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
        AccrueLeaveDays::class,
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
     
        $schedule->command('employee:accrue-leave')
        ->daily()
        ->when(function () {
            return Carbon::today()->isSameDay(Carbon::now()->endOfMonth());
        });

        $schedule->command('reports:send-daily')
        ->dailyAt('11:05')
        ->timezone('Africa/Harare')
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
