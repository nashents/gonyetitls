<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cleans up documents damaged by two bugs fixed 2026-09-28:
 *
 *  1. BillObserver::created() posted an Accounts Payable credit with no
 *     debit leg (the bill's expense lines didn't exist yet), throwing the
 *     Trial Balance out.
 *  2. LedgerResyncService (Resync to Ledger, Line item correction, Trial
 *     Balance "Repair") reversed REV- reversal entries too, re-instating
 *     the postings they had cancelled - each run left the document in the
 *     ledger one more time (double-counted AP/expense), and re-instated the
 *     credit-only entries from (1).
 *
 * A document is damaged when it has more than one live-effect entry (see
 * JournalReversalService::isLiveEffect()) or a live-effect entry that
 * doesn't balance. Repair = the (now fixed) resync: reverse every live
 * effect and post one fresh entry from the document's current figures. A
 * deleted document just gets its live effects reversed.
 */
class LedgerDoublePostingRepairService
{
    private const DOCUMENTS = [
        'bill_id' => Bill::class,
        'invoice_id' => Invoice::class,
        'credit_note_id' => CreditNote::class,
        'debit_note_id' => DebitNote::class,
        'payment_id' => Payment::class,
    ];

    public function __construct(
        private JournalReversalService $journalReversal,
        private LedgerResyncService $resync
    ) {
    }

    public function run(bool $dryRun = false): array
    {
        $result = ['repaired' => [], 'skipped' => [], 'failed' => []];
        $originalUser = Auth::user();

        try {
            foreach (self::DOCUMENTS as $column => $model) {
                $ids = JournalEntry::whereNotNull($column)->distinct()->pluck($column);

                foreach ($ids as $id) {
                    $live = $this->liveEntries($column, $id);
                    if ($live->isEmpty()) {
                        continue;
                    }
                    $unbalanced = $live->filter(fn ($e) => abs($this->diff($e)) > 0.01);
                    $document = $model::withTrashed()->find($id);
                    $deleted = ! $document || $document->trashed();

                    // A deleted document with ANY live posting is damaged too -
                    // deleted via a path that never un-posted it (trip expense
                    // removal etc.) or whose deletion reversed REV- cancellers
                    // and re-instated old postings.
                    if (! $deleted && $live->count() <= 1 && $unbalanced->isEmpty()) {
                        continue;
                    }

                    $row = [
                        'type' => $column,
                        'id' => $id,
                        'number' => $this->number($document),
                        'live_entries' => $live->pluck('journal_number')->all(),
                        'unbalanced' => $unbalanced->pluck('journal_number')->all(),
                    ];

                    if ($dryRun) {
                        $result['repaired'][] = $row + ['action' => $this->plannedAction($column, $document)];
                        continue;
                    }

                    try {
                        $row['action'] = $this->repair($column, $document, $live);
                        $result[$row['action'] === 'skipped' ? 'skipped' : 'repaired'][] = $row;
                    } catch (\Throwable $e) {
                        $result['failed'][] = $row + ['error' => $e->getMessage()];
                        Log::error("LedgerDoublePostingRepairService: {$column} #{$id} failed: " . $e->getMessage());
                    }
                }
            }
        } finally {
            if ($originalUser) {
                Auth::setUser($originalUser);
            }
        }

        return $result;
    }

    private function repair(string $column, $document, $live): string
    {
        $reason = 'Ledger repair: duplicate/one-sided posting cleanup';

        if (! $document || $document->trashed()) {
            // reverse() dates each one like the posting it cancels.
            DB::transaction(fn () => $live->each(fn ($e) => $this->journalReversal->reverse($e, "{$reason} (document deleted)")));

            return 'reversed (document deleted)';
        }

        // No resync for payments - a live one is reported, not touched.
        if ($column === 'payment_id') {
            return 'skipped';
        }

        if ($column === 'bill_id' && $document->authorization !== 'approved') {
            return 'skipped';
        }

        // Bills carry no company_id - posting falls back to the logged-in
        // user's company, so act as the document's own creator.
        if ($document->user_id && ($user = User::find($document->user_id))) {
            Auth::setUser($user);
        }

        switch ($column) {
            case 'bill_id':
                // Credits Fuel/Spares Inventory where the bill draws down
                // stock - see BillJournalService::creditAccountFor().
                $this->resync->resyncBill($document, $reason, force: true);
                break;
            case 'invoice_id':
                $this->resync->resyncInvoice($document, $reason, force: true);
                break;
            case 'credit_note_id':
                $this->resync->resyncCreditNote($document, $reason, force: true);
                break;
            case 'debit_note_id':
                $this->resync->resyncDebitNote($document, $reason, force: true);
                break;
        }

        return 'reposted';
    }

    private function plannedAction(string $column, $document): string
    {
        if (! $document || $document->trashed()) {
            return 'reverse (document deleted)';
        }

        if ($column === 'payment_id') {
            return 'skip (live payment - review manually)';
        }

        return $column === 'bill_id' && $document->authorization !== 'approved' ? 'skip (not approved)' : 'repost';
    }

    private function liveEntries(string $column, $id)
    {
        return JournalEntry::where($column, $id)
            ->where('status', 'posted')
            ->get()
            ->filter(fn (JournalEntry $e) => $this->journalReversal->isLiveEffect($e))
            ->values();
    }

    /**
     * Imbalance in the company's base currency, the way the Trial Balance
     * sums it - raw debit/credit are in each line's own currency, so a
     * cross-currency payment (cash in ZAR, AR in USD) looked one-sided.
     */
    private function diff(JournalEntry $entry): float
    {
        $base = (int) (DB::table('companies')->where('id', $entry->company_id)->value('currency_id') ?? 0);

        return (float) DB::table('journal_entry_lines')
            ->where('journal_entry_id', $entry->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN currency_id IS NULL OR currency_id = {$base} THEN debit ELSE exchange_debit END)
                - SUM(CASE WHEN currency_id IS NULL OR currency_id = {$base} THEN credit ELSE exchange_credit END), 0) as d")
            ->value('d');
    }

    private function number($document): ?string
    {
        return $document?->bill_number ?? $document?->invoice_number
            ?? $document?->credit_note_number ?? $document?->debit_note_number ?? $document?->payment_number;
    }
}
