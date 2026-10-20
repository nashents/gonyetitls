<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Advance invoice releases (RECLASS- entries: DR Customer Advances / CR Sales)
 * were dated the day they ran - when the last trip was Offloaded or "Post to
 * Sales" was clicked - not the invoice's date, so revenue landed in a later
 * month than the invoice. Re-dates every release to its invoice's date, then
 * re-dates reversals (journals:redate-correction-reversals --all) so a
 * reversed release still cancels in the same period. Idempotent.
 *
 * php artisan ledger:redate-advance-releases --dry-run
 */
class RedateAdvanceReleases extends Command
{
    protected $signature = 'ledger:redate-advance-releases {--dry-run : Preview only, change nothing}';

    protected $description = 'Date advance invoice releases (Customer Advances -> Sales) on the invoice date';

    public function handle()
    {
        $rows = [];
        $entries = JournalEntry::where('reference', 'like', 'RECLASS-%')->whereNotNull('invoice_id')->orderBy('id')->get();

        foreach ($entries as $entry) {
            $invoice = Invoice::withTrashed()->find($entry->invoice_id);
            $target = $invoice && $invoice->date ? substr((string) $invoice->date, 0, 10) : null;

            if ($target && optional($entry->date)->toDateString() !== $target) {
                $rows[] = [$entry, $invoice, $target];
            }
        }

        if (! $rows) {
            $this->info('Every advance release is dated on its invoice date.');
            return self::SUCCESS;
        }

        $this->table(['Journal', 'Invoice', 'Status', 'Dated now', 'Invoice date'], array_map(fn ($r) => [
            $r[0]->journal_number, $r[1]->invoice_number, $r[0]->status, optional($r[0]->date)->toDateString(), $r[2],
        ], $rows));

        if ($this->option('dry-run')) {
            $this->warn(count($rows) . ' release(s) would be re-dated (their reversals follow). Nothing changed.');
            return self::SUCCESS;
        }

        foreach ($rows as [$entry, , $target]) {
            $entry->date = $target;
            $entry->saveQuietly();
        }
        $this->info('Re-dated ' . count($rows) . ' release(s).');

        // Reversals of a release are dated like the release they cancel.
        Artisan::call('journals:redate-correction-reversals', ['--all' => true, '--since' => '2000-01-01']);
        $this->line(trim(Artisan::output()));

        return self::SUCCESS;
    }
}
