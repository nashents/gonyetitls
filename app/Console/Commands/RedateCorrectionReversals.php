<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use Illuminate\Console\Command;

/**
 * Reversals posted by a resync/correction used to be dated the day the
 * correction ran, pulling the old figure out of a later month while the
 * repost landed back in the original one - distorting both months. Re-dates
 * each such reversal to the date of the entry it cancels (what
 * LedgerResyncService now does going forward).
 *
 * A reversal counts as a correction only when the same document was posted
 * again after it (a repost); reversals from deletions/voids have no repost
 * and keep their date - unless --all, which re-dates every reversal (the
 * accountant's rule since 2026-10-16: a reversal belongs in the period of
 * the posting it cancels, e.g. a fuel bill removed because the order became
 * customer-supplied, or a deleted trip expense bill). Idempotent.
 *
 * php artisan journals:redate-correction-reversals --all --since=2000-01-01 --dry-run
 * php artisan journals:redate-correction-reversals --dry-run
 * php artisan journals:redate-correction-reversals --since=2026-01-01
 */
class RedateCorrectionReversals extends Command
{
    private const DOCUMENT_KEYS = ['bill_id', 'invoice_id', 'credit_note_id', 'debit_note_id', 'customer_fuel_supply_id'];

    protected $signature = 'journals:redate-correction-reversals
        {--since=2026-10-14 : Only reversals created on/after this date}
        {--dry-run : Preview only, change nothing}
        {--all : Every reversal (removals/deletions too), not just corrections followed by a repost}';

    protected $description = 'Re-date correction reversals (resync/repair) to the date of the entry they reverse';

    public function handle()
    {
        $reversals = JournalEntry::where('reference', 'like', 'REV-%')
            ->where('created_at', '>=', $this->option('since'))
            ->orderBy('id')
            ->get();

        $rows = [];
        // Dates assigned earlier in this run, by journal number. In a chain
        // X <- R1 <- R2 the target for R2 is R1's NEW date - reading R1's
        // stored (old) date left R2 a month off, so the chain no longer
        // cancelled within one period. Ids ascend, so R1 is settled first.
        $newDates = [];
        foreach ($reversals as $reversal) {
            $original = JournalEntry::withTrashed()->where('journal_number', substr($reversal->reference, 4))->first();
            if (! $original || ! $original->date) {
                continue;
            }
            $target = $newDates[$original->journal_number] ?? $original->date->toDateString();
            if (optional($reversal->date)->toDateString() === $target) {
                continue;
            }

            if (! $this->option('all')) {
                $key = collect(self::DOCUMENT_KEYS)->first(fn ($k) => $reversal->{$k});
                if (! $key) {
                    continue;
                }

                // Reposted after this reversal = a correction, not a deletion/void.
                $reposted = JournalEntry::where($key, $reversal->{$key})
                    ->where('id', '>', $reversal->id)
                    ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
                    ->exists();
                if (! $reposted) {
                    continue;
                }
            }

            $rows[] = [$reversal, $original, $target];
            $newDates[$reversal->journal_number] = $target;
        }

        if (! $rows) {
            $this->info('No correction reversals to re-date.');
            return self::SUCCESS;
        }

        $this->table(['Reversal', 'Reverses', 'Document', 'Dated now', 'Will be'], array_map(fn ($r) => [
            $r[0]->journal_number,
            $r[1]->journal_number,
            $r[0]->reference . ' ' . collect(self::DOCUMENT_KEYS)->filter(fn ($k) => $r[0]->{$k})->map(fn ($k) => "{$k}={$r[0]->{$k}}")->implode(' '),
            optional($r[0]->date)->toDateString(),
            $r[2],
        ], $rows));

        if ($this->option('dry-run')) {
            $this->warn(count($rows) . ' reversal(s) would be re-dated. Nothing changed.');
            return self::SUCCESS;
        }

        foreach ($rows as [$reversal, , $target]) {
            $reversal->date = $target;
            $reversal->saveQuietly();
        }

        $this->info('Re-dated ' . count($rows) . ' reversal(s).');
        return self::SUCCESS;
    }
}
