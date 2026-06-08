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
        if (JournalEntry::where('payment_id', $payment->id)->exists()) {
            return JournalEntry::where('payment_id', $payment->id)->first();
        }

        $payment->loadMissing(['account', 'customer', 'vendor', 'currency', 'invoice', 'bill']);

        $rate   = (float) ($payment->exchange_rate ?? 1);
        $amount = (float) $payment->amount;

        $cashBankAccount = $payment->account_id
            ? Account::findOrFail($payment->account_id)
            : Account::where('name', 'Cash on Hand')->firstOrFail();

        return DB::transaction(function () use ($payment, $rate, $amount, $cashBankAccount) {

            $entry = JournalEntry::create([
               'company_id'     => $payment->company_id ? $payment->company_id : Auth::user()->employee->company_id,
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

            match (strtolower($payment->category)) {
                'customer' => $this->postCustomerWalletPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'vendor'   => $this->postVendorWalletPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'invoice'  => $this->postInvoicePayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'bill'     => $this->postBillPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                default    => null,
            };

            return $entry;
        });
    }

    // ── 1. Customer Wallet Top-up ─────────────────────────────────────────────
    // Payments component: source_destination = Customer, category = customer
    // DR Cash/Bank   CR Customer Deposits
    private function postCustomerWalletPayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        
        $customerDeposits = Account::where('name', 'Customer Deposits')->firstOrFail();

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
    }

    // ── 2. Vendor Wallet Top-up ───────────────────────────────────────────────
    // Payments component: source_destination = Vendor, category = vendor
    // DR Vendor Prepayments   CR Cash/Bank
    private function postVendorWalletPayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        $vendorPrepayments = Account::where('name', 'Vendor Prepayments')->firstOrFail();

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

    // ── 3. Direct Invoice Payment ─────────────────────────────────────────────
    // Invoice component: category = invoice, invoice_id set directly
    // DR Cash/Bank   CR Accounts Receivable
    private function postInvoicePayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        $arAccount = Account::where('name', 'Accounts Receivable')->firstOrFail();

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
            'description'     => "Invoice payment - {$payment->payment_number}",
        ]);

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
            'description'     => "AR settlement - Invoice #{$payment->invoice?->invoice_number} - {$payment->payment_number}",
        ]);
    }

    // ── 4. Direct Bill Payment ────────────────────────────────────────────────
    // Bill component: category = Bill (capital B), bill_id set directly
    // DR Accounts Payable   CR Cash/Bank
    private function postBillPayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        $apAccount = Account::where('name', 'Accounts Payable')->firstOrFail();

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
            'description'     => "AP settlement - Bill #{$payment->bill?->bill_number} - {$payment->payment_number}",
        ]);

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
            'description'     => "Cash payment - Bill #{$payment->bill?->bill_number} - {$payment->payment_number}",
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resolveDescription(Payment $payment): string
    {
        return match (strtolower($payment->category)) {
            'customer' => "Customer Deposit - {$payment->customer?->name} - {$payment->payment_number}",
            'vendor'   => "Vendor Prepayment - {$payment->vendor?->name} - {$payment->payment_number}",
            'invoice'  => "Invoice Payment - {$payment->customer?->name} - {$payment->payment_number}",
            'bill'     => "Bill Payment - {$payment->vendor?->name} - {$payment->payment_number}",
            default    => "Payment - {$payment->payment_number}",
        };
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}