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
use Illuminate\Support\Facades\Log;

/**
 * Backfills approved documents that never got a JournalEntry - a gap caused
 * by the various observers (BillObserver, InvoiceObserver, etc.) only
 * posting on a genuine isDirty('authorization') transition, which never
 * fires for records created already-approved in one save (bulk import,
 * quick-create flows, etc.), or - for Payments - a post() call that failed
 * and was only logged. Every run*() method here is also the engine behind
 * each list page's "Bulk Post to Ledger" button (Bills/Invoices/Payments/
 * CreditNotes/DebitNotes index components), so it must be safe to call
 * from a live web request, not just the `ledger:backfill` CLI command.
 *
 * Only ever creates missing entries via the existing per-document
 * JournalService::post() (all idempotent, additive, never touch an
 * existing JournalEntry).
 *
 * Deliberately does NOT touch documents that already have a JournalEntry,
 * even when that entry looks incomplete (e.g. an unbalanced entry missing
 * its expense lines - see the Trip Expense / Fahrenheit investigation) -
 * repairing an existing entry means deleting real ledger rows, which needs
 * its own explicit, separate confirmation and is not something this
 * backfill does silently. Those are only reported here under 'incomplete'
 * for visibility (bills/invoices preview only).
 */
class LedgerBackfillService
{
    public function preview(): array
    {
        return [
            'bills'      => $this->previewBills(),
            'invoices'   => $this->previewInvoices(),
            'fuel_bills' => $this->previewFuelBills(),
        ];
    }

    public function run(): array
    {
        return [
            'bills'        => $this->runBills(),
            'invoices'     => $this->runInvoices(),
            'payments'     => $this->runPayments(),
            'credit_notes' => $this->runCreditNotes(),
            'debit_notes'  => $this->runDebitNotes(),
            'fuel_bills'   => $this->runFuelBills(),
        ];
    }

    // ---------------------------------------------------------------
    // Bills
    // ---------------------------------------------------------------

    /**
     * Fuel-order bills (fuel_id set) are excluded here and handled by
     * missingFuelBillsQuery()/runFuelBills() instead - they can't gate on
     * to_be_paid the way every other bill does. A Bulk Buy fuel consumption
     * bill is correctly to_be_paid=false forever (it draws down inventory
     * already paid for, not a new payable), which would make it permanently
     * invisible here otherwise - see FuelJournalService for the accounting
     * model.
     */
    public function missingBillsQuery()
    {
        return Bill::whereNull('fuel_id')
            ->where('authorization', 'approved')
            ->where('to_be_paid', true)
            ->whereDoesntHave('journal_entry');
    }

    /**
     * Approved fuel-order bills with no JournalEntry, regardless of
     * to_be_paid - see missingBillsQuery()'s note above.
     */
    public function missingFuelBillsQuery()
    {
        return Bill::whereNotNull('fuel_id')
            ->whereHas('fuel', fn ($q) => $q->where('authorization', 'approved'))
            ->whereDoesntHave('journal_entry');
    }

    protected function previewFuelBills(): array
    {
        $missing = $this->missingFuelBillsQuery()->get(['id', 'bill_number', 'bill_date', 'total']);

        return [
            'missing_count' => $missing->count(),
            'missing_ids'   => $missing->pluck('id')->all(),
        ];
    }

    public function runFuelBills(): array
    {
        $result = $this->emptyResult();
        $service = app(FuelJournalService::class);

        $this->withImpersonation(function () use ($service, &$result) {
            $this->missingFuelBillsQuery()->chunkById(200, function ($bills) use ($service, &$result) {
                foreach ($bills as $bill) {
                    $result['total']++;
                    try {
                        if (! $bill->user_id) {
                            throw new \RuntimeException('Bill has no user_id - cannot resolve its company.');
                        }
                        $this->impersonate($bill->user_id);

                        $entry = $service->postConsumption($bill->fuel);
                        $result['posted'][] = ['id' => $bill->id, 'number' => $bill->bill_number, 'journal_number' => $entry->journal_number];
                    } catch (\Throwable $e) {
                        $result['errors'][] = ['id' => $bill->id, 'number' => $bill->bill_number, 'message' => $e->getMessage()];
                        Log::error("LedgerBackfillService: failed to post fuel bill #{$bill->id}: " . $e->getMessage());
                    }
                }
            });
        });

        return $result;
    }

    protected function previewBills(): array
    {
        $missing = $this->missingBillsQuery()->get(['id', 'bill_number', 'bill_date', 'total']);

        return [
            'missing_count' => $missing->count(),
            'missing_ids'   => $missing->pluck('id')->all(),
            'incomplete'    => $this->incompleteEntries('bill_id', Bill::class, 'bill_number'),
        ];
    }

    public function runBills(): array
    {
        $result = $this->emptyResult();
        $service = app(BillJournalService::class);

        $this->withImpersonation(function () use ($service, &$result) {
            $this->missingBillsQuery()->chunkById(200, function ($bills) use ($service, &$result) {
                foreach ($bills as $bill) {
                    $result['total']++;
                    try {
                        // Bills have no company_id column of their own -
                        // BillJournalService falls back to
                        // Auth::user()->employee->company_id, so we
                        // impersonate the bill's own original creator to
                        // reconstruct the same company it would have
                        // posted under had the observer fired at approval
                        // time.
                        if (! $bill->user_id) {
                            throw new \RuntimeException('Bill has no user_id - cannot resolve its company.');
                        }
                        $this->impersonate($bill->user_id);

                        $entry = $service->post($bill);
                        $result['posted'][] = ['id' => $bill->id, 'number' => $bill->bill_number, 'journal_number' => $entry->journal_number];
                    } catch (\Throwable $e) {
                        $result['errors'][] = ['id' => $bill->id, 'number' => $bill->bill_number, 'message' => $e->getMessage()];
                        Log::error("LedgerBackfillService: failed to post bill #{$bill->id}: " . $e->getMessage());
                    }
                }
            });
        });

        return $result;
    }

    // ---------------------------------------------------------------
    // Invoices
    // ---------------------------------------------------------------

    public function missingInvoicesQuery()
    {
        return Invoice::where('authorization', 'approved')
            ->whereDoesntHave('journal_entry');
    }

    protected function previewInvoices(): array
    {
        $missing = $this->missingInvoicesQuery()->get(['id', 'invoice_number', 'date', 'total']);

        return [
            'missing_count' => $missing->count(),
            'missing_ids'   => $missing->pluck('id')->all(),
            'incomplete'    => $this->incompleteEntries('invoice_id', Invoice::class, 'invoice_number'),
        ];
    }

    public function runInvoices(): array
    {
        $result = $this->emptyResult();
        $service = app(InvoiceJournalService::class);

        $this->missingInvoicesQuery()->chunkById(200, function ($invoices) use ($service, &$result) {
            foreach ($invoices as $invoice) {
                $result['total']++;
                try {
                    $entry = $service->post($invoice);
                    $result['posted'][] = ['id' => $invoice->id, 'number' => $invoice->invoice_number, 'journal_number' => $entry->journal_number];
                } catch (\Throwable $e) {
                    $result['errors'][] = ['id' => $invoice->id, 'number' => $invoice->invoice_number, 'message' => $e->getMessage()];
                    Log::error("LedgerBackfillService: failed to post invoice #{$invoice->id}: " . $e->getMessage());
                }
            }
        });

        return $result;
    }

    // ---------------------------------------------------------------
    // Payments - no approval workflow, gated on canPost() instead
    // ---------------------------------------------------------------

    public function missingPaymentsQuery()
    {
        return Payment::whereDoesntHave('journalEntry');
    }

    public function runPayments(): array
    {
        $result = $this->emptyResult();
        $service = app(PaymentJournalService::class);

        $this->missingPaymentsQuery()->chunkById(200, function ($payments) use ($service, &$result) {
            foreach ($payments as $payment) {
                if (! $service->canPost($payment)) {
                    continue; // not a data issue - just not a flow post() understands
                }

                $result['total']++;
                try {
                    $entry = $service->post($payment);
                    $result['posted'][] = ['id' => $payment->id, 'number' => $payment->payment_number, 'journal_number' => $entry->journal_number];
                } catch (\Throwable $e) {
                    $result['errors'][] = ['id' => $payment->id, 'number' => $payment->payment_number, 'message' => $e->getMessage()];
                    Log::error("LedgerBackfillService: failed to post payment #{$payment->id}: " . $e->getMessage());
                }
            }
        });

        return $result;
    }

    // ---------------------------------------------------------------
    // Credit Notes
    // ---------------------------------------------------------------

    public function missingCreditNotesQuery()
    {
        return CreditNote::where('authorization', 'approved')
            ->whereDoesntHave('journal_entry');
    }

    public function runCreditNotes(): array
    {
        $result = $this->emptyResult();
        $service = app(CreditNoteJournalService::class);

        $this->missingCreditNotesQuery()->chunkById(200, function ($creditNotes) use ($service, &$result) {
            foreach ($creditNotes as $creditNote) {
                $result['total']++;
                try {
                    $entry = $service->post($creditNote);
                    $result['posted'][] = ['id' => $creditNote->id, 'number' => $creditNote->credit_note_number, 'journal_number' => $entry->journal_number];
                } catch (\Throwable $e) {
                    $result['errors'][] = ['id' => $creditNote->id, 'number' => $creditNote->credit_note_number, 'message' => $e->getMessage()];
                    Log::error("LedgerBackfillService: failed to post credit note #{$creditNote->id}: " . $e->getMessage());
                }
            }
        });

        return $result;
    }

    // ---------------------------------------------------------------
    // Debit Notes
    // ---------------------------------------------------------------

    public function missingDebitNotesQuery()
    {
        return DebitNote::where('authorization', 'approved')
            ->whereDoesntHave('journal_entry');
    }

    public function runDebitNotes(): array
    {
        $result = $this->emptyResult();
        $service = app(DebitNoteJournalService::class);

        $this->missingDebitNotesQuery()->chunkById(200, function ($debitNotes) use ($service, &$result) {
            foreach ($debitNotes as $debitNote) {
                $result['total']++;
                try {
                    $entry = $service->post($debitNote);
                    $result['posted'][] = ['id' => $debitNote->id, 'number' => $debitNote->debit_note_number, 'journal_number' => $entry->journal_number];
                } catch (\Throwable $e) {
                    $result['errors'][] = ['id' => $debitNote->id, 'number' => $debitNote->debit_note_number, 'message' => $e->getMessage()];
                    Log::error("LedgerBackfillService: failed to post debit note #{$debitNote->id}: " . $e->getMessage());
                }
            }
        });

        return $result;
    }

    // ---------------------------------------------------------------
    // Reporting only - never acted on by run()
    // ---------------------------------------------------------------

    /**
     * Entries that exist but whose lines don't balance (debit != credit) -
     * a sign the posting was only partially written (e.g. the AP/AR side
     * landed but the expense/income lines didn't). Report-only: fixing
     * these means deleting real ledger rows and needs separate sign-off.
     */
    protected function incompleteEntries(string $foreignKey, string $model, string $numberField): array
    {
        $entries = JournalEntry::whereNotNull($foreignKey)
            ->where('status', 'posted')
            ->withSum('journal_entry_lines as total_debit', 'debit')
            ->withSum('journal_entry_lines as total_credit', 'credit')
            ->get();

        $bad = $entries->filter(function ($e) {
            return abs((float) $e->total_debit - (float) $e->total_credit) > 0.01;
        });

        if ($bad->isEmpty()) {
            return ['count' => 0, 'items' => []];
        }

        $sourceIds = $bad->pluck($foreignKey)->all();
        $sources = $model::whereIn('id', $sourceIds)->pluck($numberField, 'id');

        return [
            'count' => $bad->count(),
            'items' => $bad->map(fn ($e) => [
                'journal_entry_id' => $e->id,
                'journal_number'   => $e->journal_number,
                'source_id'        => $e->{$foreignKey},
                'source_number'    => $sources[$e->{$foreignKey}] ?? null,
                'debit'            => (float) $e->total_debit,
                'credit'           => (float) $e->total_credit,
            ])->values()->all(),
        ];
    }

    protected function emptyResult(): array
    {
        return [
            'total'  => 0,
            'posted' => [],
            'errors' => [],
        ];
    }

    // ---------------------------------------------------------------
    // Impersonation helpers - Bills only, see runBills() above.
    //
    // Uses Auth::setUser() rather than Auth::loginUsingId(): setUser()
    // only swaps what Auth::user() resolves to for the rest of THIS
    // request/process, with no session write, no "login" event, no
    // remember-me cookie. loginUsingId() does all of that, which is fine
    // for a bare CLI run with nobody logged in, but unsafe to call from a
    // live web request (the "Bulk Post to Ledger" button) - it would mutate
    // the actual admin's session mid-request.
    // ---------------------------------------------------------------

    private function withImpersonation(\Closure $callback): void
    {
        $originalUser = Auth::user();

        try {
            $callback();
        } finally {
            // setUser() requires a non-null Authenticatable - a null
            // $originalUser (bare CLI run, nobody logged in) has nothing to
            // restore and no real session to protect, so it's left as-is.
            if ($originalUser) {
                Auth::setUser($originalUser);
            }
        }
    }

    private function impersonate(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            Auth::setUser($user);
        }
    }
}
