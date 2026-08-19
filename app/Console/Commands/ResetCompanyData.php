<?php

namespace App\Console\Commands;

use App\Services\CompanyDataResetService;
use Illuminate\Console\Command;

class ResetCompanyData extends Command
{
    /**
     * php artisan company:reset-data              (dry run, shows what would be wiped)
     * php artisan company:reset-data --force       (actually wipes, asks for typed confirmation)
     * php artisan company:reset-data --force --yes (wipes without the confirmation prompt, for scripted use)
     */
    protected $signature = 'company:reset-data
        {--force : Actually perform the wipe. Without this flag the command only reports what it would do.}
        {--yes : Skip the typed confirmation prompt (still requires --force). Use with care.}';

    protected $description = 'Wipe all transactional/captured data (trips, invoices, accounting, HR, SHEQ, logistics, etc.) while leaving base modules (vehicles, drivers, employees, customers, vendors, consignees, locations, config/lookups) untouched.';

    public function handle(CompanyDataResetService $service): int
    {
        $preview = $service->preview();

        $this->info('=== company:reset-data — DRY RUN report ===');
        foreach ($preview['groups'] as $group => $data) {
            $this->line("<fg=yellow>{$group}</> ({$data['total']} rows)");
            foreach ($data['tables'] as $table => $count) {
                $this->line("  - {$table}: {$count}");
            }
        }
        $this->newLine();
        $this->info("Total rows that would be deleted: {$preview['total']}");

        if (! empty($preview['unclassified'])) {
            $this->newLine();
            $this->warn('These tables have data but are NOT classified as either "wipe" or "keep" — left untouched. Review and tell me if any should be added to the wipe list:');
            foreach ($preview['unclassified'] as $table => $count) {
                $this->line("  - {$table}: {$count} rows");
            }
        }

        if (! empty($preview['balances'])) {
            $this->newLine();
            $this->line('<fg=yellow>Stored balances that would be reset to 0</> (rows are kept, only the balance column changes):');
            foreach ($preview['balances'] as $column => $count) {
                $this->line("  - {$column}: {$count} row(s)");
            }
        }

        if (! $this->option('force')) {
            $this->newLine();
            $this->comment('This was a dry run — no data was touched. Re-run with --force to actually wipe the data above.');

            return self::SUCCESS;
        }

        if ($preview['total'] === 0 && empty($preview['balances'])) {
            $this->info('Nothing to wipe.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error('You are about to PERMANENTLY delete the ' . $preview['total'] . ' rows listed above. This cannot be undone. Take a database backup first if you have not already.');

        if (! $this->option('yes')) {
            $typed = $this->ask('Type RESET to confirm');
            if ($typed !== 'RESET') {
                $this->comment('Confirmation text did not match. Aborted, nothing was deleted.');

                return self::FAILURE;
            }
        }

        $results = $service->execute();

        $this->newLine();
        $this->info('Reset complete. Summary logged to storage/logs/laravel.log.');
        foreach ($results as $table => $result) {
            $this->line("  - {$table}: {$result}");
        }

        return self::SUCCESS;
    }
}
