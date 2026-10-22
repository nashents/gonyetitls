<?php

namespace App\Console\Commands;

use App\Services\Freight\FreightReminderService;
use Illuminate\Console\Command;

class SendFreightReminders extends Command
{
    protected $signature = 'freight:send-reminders {--company= : Only this company\'s freight jobs}';
    protected $description = 'Email the daily freight reminders digest (arrivals, departures, milestones, container free time) to anyone not yet sent one today (normally triggered automatically by web traffic).';

    public function handle(FreightReminderService $service): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $sent = $service->sendDailyDigests($companyId);
        $this->info("Freight reminders: {$sent} digest(s) sent.");

        return self::SUCCESS;
    }
}
