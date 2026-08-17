<?php

namespace App\Services\Accounting;

use App\Models\Payment;
use App\Models\TransactionType;

class PaymentDataIntegrityService
{
    /**
     * Runs every known repair check and returns a report. Safe to run
     * repeatedly - each check only touches records it finds broken, and does
     * nothing when everything already looks correct.
     */
    public function run(): array
    {
        $checks = [
            $this->fixCustomerVendorTransactionTypes(),
            $this->fixUnpostedLedgerEntries(),
        ];

        return [
            'checked' => array_sum(array_column($checks, 'checked')),
            'fixed'   => array_sum(array_column($checks, 'fixed')),
            'checks'  => $checks,
        ];
    }

    /**
     * Customer/vendor wallet payments (Payments module - deposits/prepayments
     * awaiting allocation) must carry a valid transaction_type_id so the
     * Transactions list can show a Type. A prior bug assigned the whole
     * TransactionType model instead of its id, which MySQL silently coerced
     * to 0.
     */
    private function fixCustomerVendorTransactionTypes(): array
    {
        $depositTypeId    = TransactionType::where('name', 'Deposit')->value('id');
        $withdrawalTypeId = TransactionType::where('name', 'Withdrawal')->value('id');

        $broken = Payment::whereIn('category', ['customer', 'vendor'])
            ->where(function ($query) {
                $query->whereNull('transaction_type_id')->orWhere('transaction_type_id', 0);
            })
            ->get();

        $fixed = 0;

        foreach ($broken as $payment) {
            $correctId = strtolower($payment->category) === 'customer' ? $depositTypeId : $withdrawalTypeId;

            if (!$correctId) {
                continue;
            }

            $payment->transaction_type_id = $correctId;
            $payment->save();
            $fixed++;
        }

        return [
            'name'    => 'Customer/vendor payments missing a valid transaction type',
            'checked' => $broken->count(),
            'fixed'   => $fixed,
        ];
    }

    /**
     * Every payment should end up with a posted double-entry JournalEntry.
     * A prior bug left withdrawal/deposit payments (and any payment whose
     * PaymentObserver post() call failed) with no JournalEntry, or with an
     * empty dangling header and no lines. Re-runs PaymentJournalService::post()
     * for each - it is idempotent and safe to call again.
     */
    private function fixUnpostedLedgerEntries(): array
    {
        $candidates = Payment::where(function ($query) {
                $query->whereDoesntHave('journalEntry')
                    ->orWhereHas('journalEntry', function ($entryQuery) {
                        $entryQuery->whereDoesntHave('journal_entry_lines');
                    });
            })
            ->get();

        $service = app(PaymentJournalService::class);
        $fixed   = 0;
        $skipped = 0;

        foreach ($candidates as $payment) {
            if (!$service->canPost($payment)) {
                $skipped++;
                continue;
            }

            try {
                $entry = $service->post($payment);
                if ($entry->journal_entry_lines()->exists()) {
                    $fixed++;
                }
            } catch (\Throwable) {
                // Leave for manual review - next run will pick it up again.
            }
        }

        return [
            'name'    => 'Payments missing a posted ledger entry',
            'checked' => $candidates->count(),
            'fixed'   => $fixed,
            'skipped' => $skipped,
        ];
    }
}
