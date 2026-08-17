<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentJournalService
{
    private const POSTABLE_CATEGORIES = [
        'customer', 'vendor', 'invoice', 'bill', 'sale', 'recovery', 'withdrawal', 'deposit',
    ];

    /**
     * Whether this payment resolves to a category post() actually knows how to
     * book. Used by the data-integrity repair job so it doesn't create empty
     * JournalEntry headers for payments that fall outside every known flow.
     */
    public function canPost(Payment $payment): bool
    {
        return in_array($this->resolveCategory($payment), self::POSTABLE_CATEGORIES, true);
    }

    public function post(Payment $payment): JournalEntry
    {
        $existing = JournalEntry::where('payment_id', $payment->id)->first();
        if ($existing && $existing->journal_entry_lines()->exists()) {
            return $existing;
        }

        $payment->loadMissing(['account', 'customer', 'vendor', 'currency', 'invoice', 'bill', 'sale', 'recovery', 'transaction_type']);

        $rate    = (float) ($payment->exchange_rate ?? 1);
        $amount  = (float) $payment->amount;
        $category = $this->resolveCategory($payment);

        $cashBankAccount = $this->resolveCashBankAccount($payment);

        return DB::transaction(function () use ($payment, $rate, $amount, $category, $cashBankAccount, $existing) {

            $entry = $existing ?? JournalEntry::create([
               'company_id'     => $payment->company_id ? $payment->company_id : Auth::user()->employee->company_id,
                'payment_id'     => $payment->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $payment->date,
                'reference'      => $payment->payment_number,
                'description'    => $this->resolveDescription($payment, $category),
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            match ($category) {
                'customer'   => $this->postCustomerWalletPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'vendor'     => $this->postVendorWalletPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'invoice'    => $this->postInvoicePayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'bill'       => $this->postBillPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'sale'       => $this->postSalePayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'recovery'   => $this->postRecoveryPayment($entry, $payment, $amount, $rate, $cashBankAccount),
                'withdrawal' => $this->postWithdrawal($entry, $payment, $amount, $rate, $cashBankAccount),
                'deposit'    => $this->postDeposit($entry, $payment, $amount, $rate, $cashBankAccount),
                default      => null,
            };

            return $entry;
        });
    }

    /**
     * category is set explicitly by most flows, but older withdrawal/deposit
     * records (and any created before category was wired up) only carry a
     * transaction_type relation - fall back to that so manual re-posting can
     * still resolve them.
     */
    private function resolveCategory(Payment $payment): ?string
    {
        if ($payment->category) {
            return strtolower($payment->category);
        }

        $typeName = optional($payment->transaction_type)->name;

        return $typeName ? strtolower($typeName) : null;
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

    // ── 5. Direct Sale Payment ────────────────────────────────────────────────
    // Sales component: category = sale, sale_id set directly
    // DR Cash/Bank   CR Accounts Receivable
    private function postSalePayment(
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
            'description'     => "Sale payment - {$payment->payment_number}",
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
            'description'     => "AR settlement - Sale #{$payment->sale_id} - {$payment->payment_number}",
        ]);
    }

    // ── 6. Driver Recovery Payment ────────────────────────────────────────────
    // Recoveries component: category = recovery, recovery_id set directly
    // DR Cash/Bank   CR Accounts Receivable
    private function postRecoveryPayment(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        $arAccount = Account::where('name', 'Accounts Receivable')->firstOrFail();

        $entry->journal_entry_lines()->create([
            'account_id'      => $cashBankAccount->id,
            'driver_id'       => $payment->driver_id,
            'debit'           => $amount,
            'credit'          => 0,
            'exchange_debit'  => $amount * $rate,
            'exchange_credit' => 0,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Recovery payment - {$payment->payment_number}",
        ]);

        $entry->journal_entry_lines()->create([
            'account_id'      => $arAccount->id,
            'driver_id'       => $payment->driver_id,
            'debit'           => 0,
            'credit'          => $amount,
            'exchange_debit'  => 0,
            'exchange_credit' => $amount * $rate,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "AR settlement - Recovery #{$payment->recovery_id} - {$payment->payment_number}",
        ]);
    }

    // ── 7. Withdrawal ─────────────────────────────────────────────────────────
    // Transactions component: category = withdrawal, transaction_category = contra account name
    // DR Contra account (e.g. expense)   CR Cash/Bank
    private function postWithdrawal(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        $contraAccount = $this->resolveContraAccount($payment, 'Uncategorized Expense');

        $entry->journal_entry_lines()->create([
            'account_id'      => $contraAccount->id,
            'customer_id'     => $payment->customer_id,
            'vendor_id'       => $payment->vendor_id,
            'debit'           => $amount,
            'credit'          => 0,
            'exchange_debit'  => $amount * $rate,
            'exchange_credit' => 0,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Withdrawal - {$contraAccount->name} - {$payment->payment_number}",
        ]);

        $entry->journal_entry_lines()->create([
            'account_id'      => $cashBankAccount->id,
            'customer_id'     => $payment->customer_id,
            'vendor_id'       => $payment->vendor_id,
            'debit'           => 0,
            'credit'          => $amount,
            'exchange_debit'  => 0,
            'exchange_credit' => $amount * $rate,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Withdrawal from {$cashBankAccount->name} - {$payment->payment_number}",
        ]);
    }

    // ── 8. Deposit ────────────────────────────────────────────────────────────
    // Transactions component: category = deposit, transaction_category = contra account name
    // DR Cash/Bank   CR Contra account (e.g. income)
    private function postDeposit(
        JournalEntry $entry,
        Payment $payment,
        float $amount,
        float $rate,
        Account $cashBankAccount
    ): void {
        $contraAccount = $this->resolveContraAccount($payment, 'Uncategorized Income');

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
            'description'     => "Deposit to {$cashBankAccount->name} - {$payment->payment_number}",
        ]);

        $entry->journal_entry_lines()->create([
            'account_id'      => $contraAccount->id,
            'customer_id'     => $payment->customer_id,
            'vendor_id'       => null,
            'debit'           => 0,
            'credit'          => $amount,
            'exchange_debit'  => 0,
            'exchange_credit' => $amount * $rate,
            'currency_id'     => $payment->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Deposit - {$contraAccount->name} - {$payment->payment_number}",
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Withdrawal/deposit modals let the user pick any chart-of-accounts entry
     * as the contra account via transaction_category (which stores the
     * account NAME, not an id). Falls back to the Uncategorized Expense/
     * Income account used as the modal's own default.
     */
    private function resolveContraAccount(Payment $payment, string $fallbackName): Account
    {
        if ($payment->transaction_category) {
            $account = Account::where('name', $payment->transaction_category)->first();
            if ($account) {
                return $account;
            }
        }

        return Account::where('name', $fallbackName)->firstOrFail();
    }

    /**
     * Payments record which Chart of Accounts "Cash & Bank" account the money
     * moved through directly via account_id (this can be "Cash on Hand" or any
     * bank-linked account) - bank_account_id is not populated when recording a
     * payment, so it is not consulted here.
     */
    private function resolveCashBankAccount(Payment $payment): Account
    {
        if ($payment->account_id) {
            return Account::findOrFail($payment->account_id);
        }

        return Account::where('name', 'Cash on Hand')->firstOrFail();
    }

    private function resolveDescription(Payment $payment, ?string $category = null): string
    {
        return match ($category ?? $this->resolveCategory($payment)) {
            'customer'   => "Customer Deposit - {$payment->customer?->name} - {$payment->payment_number}",
            'vendor'     => "Vendor Prepayment - {$payment->vendor?->name} - {$payment->payment_number}",
            'invoice'    => "Invoice Payment - {$payment->customer?->name} - {$payment->payment_number}",
            'bill'       => "Bill Payment - {$payment->vendor?->name} - {$payment->payment_number}",
            'sale'       => "Sale Payment - {$payment->customer?->name} - {$payment->payment_number}",
            'recovery'   => "Recovery Payment - {$payment->driver?->employee?->name} - {$payment->payment_number}",
            'withdrawal' => "Withdrawal - {$payment->transaction_category} - {$payment->payment_number}",
            'deposit'    => "Deposit - {$payment->transaction_category} - {$payment->payment_number}",
            default      => "Payment - {$payment->payment_number}",
        };
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}