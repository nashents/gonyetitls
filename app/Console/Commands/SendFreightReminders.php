<?php

namespace App\Console\Commands;

use App\Services\Freight\FreightJobReminderService;
use App\Services\Freight\FreightReminderService;
use Illuminate\Console\Command;

class SendFreightReminders extends Command
{
    protected $signature = 'freight:send-reminders {--company= : Only this company\'s freight jobs}';
    protected $description = 'Email due freight job follow-up reminders and the daily freight reminders digest (arrivals, departures, milestones, container free time, follow-ups) to anyone not yet sent one today (normally triggered automatically by web traffic).';

    public function handle(FreightReminderService $service, FreightJobReminderService $followUps): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;

        // Company-specific: only this company's jobs (all companies when no --company).
        $followUpsSent = $followUps->sendDue($companyId);
        $this->info("Freight job follow-ups: {$followUpsSent} reminder(s) emailed.");

        $sent = $service->sendDailyDigests($companyId);
        $this->info("Freight reminders: {$sent} digest(s) sent.");

        return self::SUCCESS;
    }
}
