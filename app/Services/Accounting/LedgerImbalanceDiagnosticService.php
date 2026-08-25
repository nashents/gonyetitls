<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finds journal entries whose own lines don't balance (debit total != credit
 * total) - the root cause of an out-of-balance Trial Balance - and, where the
 * entry is linked to a source document (Bill/Invoice/CreditNote/DebitNote),
 * repairs it via LedgerResyncService's reverse-and-repost (the same
 * mechanism already used for the "Resync to Ledger" buttons and for the
 * fuel-order Bill / CEB00192 / CEB00203 fixes).
 *
 * A reversed entry is expected to be one-sided (its reversal offsets it, see
 * JournalReversalService) so it's excluded here - only *unreversed* entries
 * whose lines don't sum to zero indicate a real problem.
 */
class LedgerImbalanceDiagnosticService
{
    public function __construct(private LedgerResyncService $resync)
    {
    }

    public function findImbalancedEntries(int $companyId, string $dateFrom, string $dateTo): Collection
    {
        return JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'reversed')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('journal_entries.*, (
                    select coalesce(sum(debit), 0) from journal_entry_lines where journal_entry_lines.journal_entry_id = journal_entries.id
                ) as total_debit, (
                    select coalesce(sum(credit), 0) from journal_entry_lines where journal_entry_lines.journal_entry_id = journal_entries.id
                ) as total_credit')
            ->get()
            ->filter(fn ($entry) => abs((float) $entry->total_debit - (float) $entry->total_credit) > 0.01)
            ->map(function (JournalEntry $entry) {
                $entry->diff = round((float) $entry->total_debit - (float) $entry->total_credit, 2);
                $entry->source = $this->resolveSource($entry);

                return $entry;
            })
            ->values();
    }

    /**
     * Reverses+reposts every fixable imbalanced entry in range for this
     * company. Returns a summary: ledger totals before/after, what was
     * fixed (old vs new journal number, amount corrected), and what was
     * skipped (no source document to regenerate from - needs manual review).
     */
    public function repair(int $companyId, string $dateFrom, string $dateTo, string $reason): array
    {
        $before = $this->totals($companyId, $dateFrom, $dateTo);

        $fixed = [];
        $skipped = [];

        foreach ($this->findImbalancedEntries($companyId, $dateFrom, $dateTo) as $entry) {
            $source = $entry->source;

            if (! $source['fixable']) {
                $skipped[] = [
                    'journal_number' => $entry->journal_number,
                    'reference' => $entry->reference,
                    'diff' => $entry->diff,
                    'reason' => $source['type'] === 'manual'
                        ? 'Manual journal entry - no source document to regenerate from, needs review.'
                        : 'No linked source document found - needs review.',
                ];

                continue;
            }

            try {
                $newEntry = $this->resyncOne($source['type'], $source['id'], $reason);

                $fixed[] = [
                    'document_type' => $source['type'],
                    'reference' => $entry->reference,
                    'old_journal_number' => $entry->journal_number,
                    'new_journal_number' => $newEntry->journal_number,
                    'corrected_amount' => abs($entry->diff),
                ];
            } catch (\Throwable $e) {
                $skipped[] = [
                    'journal_number' => $entry->journal_number,
                    'reference' => $entry->reference,
                    'diff' => $entry->diff,
                    'reason' => 'Resync failed: '.$e->getMessage(),
                ];
            }
        }

        $after = $this->totals($companyId, $dateFrom, $dateTo);

        return [
            'before' => $before,
            'after' => $after,
            'fixed' => $fixed,
            'skipped' => $skipped,
        ];
    }

    private function resolveSource(JournalEntry $entry): array
    {
        if ($entry->bill_id) {
            return ['type' => 'bill', 'id' => $entry->bill_id, 'fixable' => true];
        }
        if ($entry->invoice_id) {
            return ['type' => 'invoice', 'id' => $entry->invoice_id, 'fixable' => true];
        }
        if ($entry->credit_note_id) {
            return ['type' => 'credit_note', 'id' => $entry->credit_note_id, 'fixable' => true];
        }
        if ($entry->debit_note_id) {
            return ['type' => 'debit_note', 'id' => $entry->debit_note_id, 'fixable' => true];
        }

        return ['type' => $entry->is_manual ? 'manual' : 'unknown', 'id' => null, 'fixable' => false];
    }

    private function resyncOne(string $type, int $id, string $reason): JournalEntry
    {
        return match ($type) {
            'bill' => $this->resync->resyncBill(Bill::findOrFail($id), $reason, force: true),
            'invoice' => $this->resync->resyncInvoice(Invoice::findOrFail($id), $reason, force: true),
            'credit_note' => $this->resync->resyncCreditNote(CreditNote::findOrFail($id), $reason, force: true),
            'debit_note' => $this->resync->resyncDebitNote(DebitNote::findOrFail($id), $reason, force: true),
        };
    }

    private function totals(int $companyId, string $dateFrom, string $dateTo): array
    {
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.company_id', $companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereBetween('journal_entries.date', [$dateFrom, $dateTo]);

        $debit = (float) $query->clone()->sum('journal_entry_lines.debit');
        $credit = (float) $query->clone()->sum('journal_entry_lines.credit');

        return ['debit' => $debit, 'credit' => $credit, 'diff' => round($debit - $credit, 2)];
    }
}
