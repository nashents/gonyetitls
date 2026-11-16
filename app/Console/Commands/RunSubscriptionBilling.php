<?php

namespace App\Console\Commands;

use App\Services\Billing\SubscriptionBillingService;
use Illuminate\Console\Command;

class RunSubscriptionBilling extends Command
{
    protected $signature = 'billing:run {--force : Run even if another run holds the lock}';
    protected $description = 'Raise due Gonyeti license invoices, deliver them (email / WhatsApp) and send payment reminders.';

    public function handle(SubscriptionBillingService $billing): int
    {
        $result = $billing->runDaily((bool) $this->option('force'));

        if ($result === null) {
            $this->warn('Another billing run is in progress - skipped.');
            return self::SUCCESS;
        }

        $this->info("Invoices raised: {$result['generated']}, delivered: {$result['sent']}, reminders sent: {$result['reminders']}.");

        return self::SUCCESS;
    }
}
