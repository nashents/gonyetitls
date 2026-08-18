<?php

namespace App\Console\Commands;

use App\Services\Accounting\LedgerBackfillService;
use Illuminate\Console\Command;

class LedgerBackfill extends Command
{
    /**
     * php artisan ledger:backfill --dry-run
     * php artisan ledger:backfill
     */
    protected $signature = 'ledger:backfill {--dry-run : Preview only, post nothing}';

    protected $description = 'Post approved bills/invoices that never made it to the general ledger (missing JournalEntry)';

    public function handle(LedgerBackfillService $service)
    {
        if ($this->option('dry-run')) {
            $preview = $service->preview();

            $this->info('Bills: ' . count($preview['bills']['missing_ids']) . ' approved bill(s) with no JournalEntry would be posted.');
            $this->reportIncomplete('Bills', $preview['bills']['incomplete']);

            $this->newLine();

            $this->info('Invoices: ' . count($preview['invoices']['missing_ids']) . ' approved invoice(s) with no JournalEntry would be posted.');
            $this->reportIncomplete('Invoices', $preview['invoices']['incomplete']);

            return self::SUCCESS;
        }

        $result = $service->run();

        $labels = [
            'bills'        => 'Bills',
            'invoices'     => 'Invoices',
            'payments'     => 'Payments',
            'credit_notes' => 'Credit Notes',
            'debit_notes'  => 'Debit Notes',
        ];

        foreach ($labels as $key => $label) {
            $r = $result[$key];
            $this->info("{$label}: posted " . count($r['posted']) . " / " . $r['total'] . " checked, " . count($r['errors']) . ' error(s).');
            foreach ($r['errors'] as $e) {
                $this->error("  #{$e['id']} ({$e['number']}): {$e['message']}");
            }
        }

        return self::SUCCESS;
    }

    private function reportIncomplete(string $label, array $incomplete): void
    {
        if ($incomplete['count'] === 0) {
            return;
        }

        $this->warn("{$label}: {$incomplete['count']} existing JournalEntry row(s) look incomplete (unbalanced) - NOT touched by this command, needs separate review:");
        foreach (array_slice($incomplete['items'], 0, 10) as $item) {
            $this->line("  JE {$item['journal_number']} <- #{$item['source_id']} ({$item['source_number']}): debit={$item['debit']} credit={$item['credit']}");
        }
        if ($incomplete['count'] > 10) {
            $this->line('  ... and ' . ($incomplete['count'] - 10) . ' more.');
        }
    }
}
