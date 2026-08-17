<?php

namespace App\Console\Commands;

use App\Services\Accounting\PaymentDataIntegrityService;
use Illuminate\Console\Command;

class FixPaymentDataIssues extends Command
{
    /**
     * php artisan payments:fix-data-issues
     */
    protected $signature = 'payments:fix-data-issues';

    protected $description = 'Scan payment records for known data issues (missing transaction types, unposted ledger entries) and correct them';

    public function handle(PaymentDataIntegrityService $service)
    {
        $report = $service->run();

        foreach ($report['checks'] as $check) {
            $this->line("{$check['name']}: checked {$check['checked']}, fixed {$check['fixed']}"
                .(isset($check['skipped']) ? ", skipped {$check['skipped']}" : ''));
        }

        if ($report['fixed'] === 0) {
            $this->info('No data issues found.');

            return self::SUCCESS;
        }

        $this->info("Fixed {$report['fixed']} issue(s) out of {$report['checked']} record(s) checked.");

        return self::SUCCESS;
    }
}
