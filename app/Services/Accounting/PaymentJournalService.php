<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentJournalService
{
    public function post(Payment $payment): JournalEntry
    {
        // Prevent duplicate
        if (JournalEntry::where('payment_id', $payment->id)->exists()) {
            return JournalEntry::where('payment_id', $payment->id)->first();
        }

        $payment->loadMissing(['account', 'customer', 'vendor', 'currency']);

        $rate   = (float) ($payment->exchange_rate ?? 1);
        $amount = (float) $payment->amount;

        // Resolve control accounts
        $arAccount        = Account::where('name', 'Accounts Receivable')->firstOrFail();
        $apAccount        = Account::where('name', 'Accounts Payable')->firstOrFail();
        $customerDeposits = Account::where('name', 'Customer Deposits')->firstOrFail();
        $vendorPrepayments = Account::where('name', 'Vendor Prepayments')->firstOrFail();

        // Cash/Bank account selected on the payment form
        $cashBankAccount = $payment->account_id
            ? Account::findOrFail($payment->account_id)
            : Account::where('name', 'Cash on Hand')->firstOrFail();

        return DB::transaction(function () use (
            $payment, $rate, $amount,
            $arAccount, $apAccount,
            $customerDeposits, $vendorPrepayments,
            $cashBankAccount
        ) {
            $entry = JournalEntry::create([
                'company_id'     => $payment->company_id,
                'payment_id'     => $payment->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $payment->date,
                'reference'      => $payment->payment_number,
                'description'    => $this->resolveDescription($payment),
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            if ($payment->category === 'customer') {
                $this->postCustomerPayment(
                    $entry, $payment, $amount, $rate,
                    $cashBankAccount, $arAccount, $customerDeposits
                );
            } elseif ($payment->category === 'vendor') {
                $this->postVendorPayment(
                    $entry, $payment, $amount, $rate,
                    $cashBankAccount, $apAccount, $vendorPrepayments
                );
            }

            return $entry;
        });
    }

    // ── Customer Payment ──────────────────────────────────────────────────────
    private function postCustomerPayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount,
        Account $arAccount,
        Account $customerDeposits
    ): void {
        $isDeposit = $payment->transaction_category === 'Customer Deposits';

        // DR Cash/Bank — money coming in
        $entry->journal_entry_lines()->create([
            'account_id'      => $cashBankAccount->id,
            'customer_id'     => $payment->customer_id,
            'vendor_id'       => null,
            'debit'           => $amount,
            'credit'          => 0,
            'exchange_debit'  => $amount * $rate,
            'exchange_credit' => 0,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Cash receipt - {$payment->payment_number}",
        ]);

        if ($isDeposit) {
            // CR Customer Deposits (liability) — no invoice yet, hold as deposit
            $entry->journal_entry_lines()->create([
                'account_id'      => $customerDeposits->id,
                'customer_id'     => $payment->customer_id,
                'vendor_id'       => null,
                'debit'           => 0,
                'credit'          => $amount,
                'exchange_debit'  => 0,
                'exchange_credit' => $amount * $rate,
                'currency_id'     => $payment->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "Customer deposit - {$payment->payment_number}",
            ]);
        } else {
            // CR Accounts Receivable — clears invoice balance
            $entry->journal_entry_lines()->create([
                'account_id'      => $arAccount->id,
                'customer_id'     => $payment->customer_id,
                'vendor_id'       => null,
                'debit'           => 0,
                'credit'          => $amount,
                'exchange_debit'  => 0,
                'exchange_credit' => $amount * $rate,
                'currency_id'     => $payment->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AR settlement - {$payment->payment_number}",
            ]);
        }
    }

    // ── Vendor Payment ────────────────────────────────────────────────────────
    private function postVendorPayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount,
        Account $apAccount,
        Account $vendorPrepayments
    ): void {
        // Determine if this is a prepayment (no bill linked) or bill settlement
        $isPrePayment = $payment->transaction_category === 'Vendor Payments'
            && empty($payment->bill_id);

        if ($isPrePayment) {
            // DR Vendor Prepayments (asset) — paid but no bill yet
            $entry->journal_entry_lines()->create([
                'account_id'      => $vendorPrepayments->id,
                'vendor_id'       => $payment->vendor_id,
                'customer_id'     => null,
                'debit'           => $amount,
                'credit'          => 0,
                'exchange_debit'  => $amount * $rate,
                'exchange_credit' => 0,
                'currency_id'     => $payment->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "Vendor prepayment - {$payment->payment_number}",
            ]);
        } else {
            // DR Accounts Payable — clears existing bill
            $entry->journal_entry_lines()->create([
                'account_id'      => $apAccount->id,
                'vendor_id'       => $payment->vendor_id,
                'customer_id'     => null,
                'debit'           => $amount,
                'credit'          => 0,
                'exchange_debit'  => $amount * $rate,
                'exchange_credit' => 0,
                'currency_id'     => $payment->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AP settlement - {$payment->payment_number}",
            ]);
        }

        // CR Cash/Bank — money going out
        $entry->journal_entry_lines()->create([
            'account_id'      => $cashBankAccount->id,
            'vendor_id'       => $payment->vendor_id,
            'customer_id'     => null,
            'debit'           => 0,
            'credit'          => $amount,
            'exchange_debit'  => 0,
            'exchange_credit' => $amount * $rate,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Cash payment - {$payment->payment_number}",
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resolveDescription(Payment $payment): string
    {
        if ($payment->category === 'customer') {
            $name = $payment->customer?->name ?? 'Customer';
            return $payment->transaction_category === 'Customer Deposits'
                ? "Customer Deposit - {$name} - {$payment->payment_number}"
                : "Customer Payment - {$name} - {$payment->payment_number}";
        }

        $name = $payment->vendor?->name ?? 'Vendor';
        $type = empty($payment->bill_id) ? 'Vendor Prepayment' : 'Vendor Payment';
        return "{$type} - {$name} - {$payment->payment_number}";
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}