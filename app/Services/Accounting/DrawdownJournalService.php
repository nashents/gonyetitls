<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\BillPayment;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Posts the application of money already received/paid in advance:
 *  - customer deposit applied to an invoice (invoice_payments source
 *    'drawdown'): DR Customer Deposits / CR Accounts Receivable
 *  - vendor prepayment applied to a bill (bill_payments source 'drawdown'):
 *    DR Accounts Payable / CR Vendor Prepayments
 * The cash already went through the ledger when the deposit/prepayment was
 * received (PaymentJournalService) - this only moves it from the advance
 * account onto the invoice/bill. Before 2026-10-19 nothing posted here, so
 * Customer Deposits / Vendor Prepayments never cleared and AR/AP stayed at
 * the full invoice/bill.
 *
 * The entry is linked to the allocation row (invoice_payment_id /
 * bill_payment_id) so removing the allocation reverses it. Rate = the
 * deposit's own rate, so the advance account clears exactly at the rate it
 * was credited/debited at. Dated the day of the allocation.
 */
class DrawdownJournalService
{
    public function __construct(private JournalReversalService $journalReversal)
    {
    }

    public function postInvoiceDrawdown(InvoicePayment $allocation): ?JournalEntry
    {
        if ($allocation->source !== 'drawdown' || ! $allocation->payment_id || $this->posted('invoice_payment_id', $allocation->id)) {
            return null;
        }

        $deposit = $allocation->payment()->withTrashed()->first();
        $invoice = $allocation->invoice()->withTrashed()->first();
        $number = optional($invoice)->invoice_number;

        return $this->post(
            ['invoice_payment_id' => $allocation->id],
            optional($deposit)->company_id ?? optional($invoice)->company_id,
            $allocation->created_at,
            "DD-" . ($number ?? $allocation->id),
            "Customer deposit " . optional($deposit)->payment_number . " applied to invoice {$number}",
            (float) $allocation->amount,
            (float) (optional($deposit)->exchange_rate ?: (optional($invoice)->exchange_rate ?: 1)),
            $allocation->currency_id,
            Account::where('name', 'Customer Deposits')->firstOrFail(),
            Account::where('name', 'Accounts Receivable')->firstOrFail(),
            ['customer_id' => $allocation->customer_id]
        );
    }

    public function postBillDrawdown(BillPayment $allocation): ?JournalEntry
    {
        if ($allocation->source !== 'drawdown' || ! $allocation->payment_id || $this->posted('bill_payment_id', $allocation->id)) {
            return null;
        }

        $prepayment = $allocation->payment()->withTrashed()->first();
        $bill = $allocation->bill()->withTrashed()->first();
        $number = optional($bill)->bill_number;

        return $this->post(
            ['bill_payment_id' => $allocation->id],
            optional($prepayment)->company_id,
            $allocation->created_at,
            "DD-" . ($number ?? $allocation->id),
            "Vendor prepayment " . optional($prepayment)->payment_number . " applied to bill {$number}",
            (float) $allocation->amount,
            (float) (optional($prepayment)->exchange_rate ?: (optional($bill)->exchange_rate ?: 1)),
            $allocation->currency_id,
            Account::where('name', 'Accounts Payable')->firstOrFail(),
            Account::where('name', 'Vendor Prepayments')->firstOrFail(),
            ['vendor_id' => $allocation->vendor_id]
        );
    }

    /** Allocation removed - un-post it (dated like the posting it cancels). */
    public function reverse(string $column, int $allocationId, string $reason): int
    {
        return $this->journalReversal->reverseLiveEffects(JournalEntry::where($column, $allocationId), $reason);
    }

    private function posted(string $column, int $id): bool
    {
        return JournalEntry::where($column, $id)->get()->contains(fn ($e) => $this->journalReversal->isLiveEffect($e));
    }

    private function post(array $link, ?int $companyId, $date, string $reference, string $description, float $amount, float $rate,
        $currencyId, Account $debitAccount, Account $creditAccount, array $dimensions): ?JournalEntry
    {
        if (round($amount, 2) == 0.0) {
            return null;
        }

        return DB::transaction(function () use ($link, $companyId, $date, $reference, $description, $amount, $rate, $currencyId, $debitAccount, $creditAccount, $dimensions) {
            $entry = JournalEntry::create($link + [
                'company_id'     => $companyId ?? Auth::user()?->employee?->company_id,
                'journal_number' => $this->generateNumber(),
                'date'           => $date ? \Illuminate\Support\Carbon::parse($date)->toDateString() : now()->toDateString(),
                'reference'      => $reference,
                'description'    => $description,
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            foreach ([[$debitAccount, $amount, 0], [$creditAccount, 0, $amount]] as [$account, $debit, $credit]) {
                $entry->journal_entry_lines()->create($dimensions + [
                    'account_id'      => $account->id,
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'exchange_debit'  => $debit * $rate,
                    'exchange_credit' => $credit * $rate,
                    'currency_id'     => $currencyId,
                    'exchange_rate'   => $rate,
                    'description'     => $description,
                ]);
            }

            return $entry;
        });
    }

    private function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
