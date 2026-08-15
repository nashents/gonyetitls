<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * One-time-per-range catch-up: settles every unpaid/partial invoice and bill
 * in a date range against a dedicated equity clearing account (no real cash
 * moves), so historical books can be closed cleanly. See
 * app/Http/Livewire/Accounts/BulkCatchUp.php for the UI that drives this.
 */
class BulkCatchUpService
{
    public const CLEARING_ACCOUNT_NAME = 'Opening Balance Equity - Historical Catch-up';
    public const CLEARING_ACCOUNT_TYPE_NAME = 'Business Owner Contribution & Drawing';
    public const DEFAULT_CURRENCY_ID = 1; // USD
    public const SOURCE = 'bulk_catchup';

    public function preview(array $params): array
    {
        $types = $params['types'] ?? ['invoices', 'bills'];

        return [
            'invoices' => in_array('invoices', $types)
                ? $this->summarize($this->invoiceQuery($params)->get(['id', 'balance', 'total', 'currency_id', 'authorization']))
                : null,
            'bills' => in_array('bills', $types)
                ? $this->summarize($this->billQuery($params)->get(['id', 'balance', 'total', 'currency_id', 'authorization']))
                : null,
        ];
    }

    public function run(array $params, int $actorUserId): array
    {
        Auth::loginUsingId($actorUserId);

        $types = $params['types'] ?? ['invoices', 'bills'];
        $clearingAccount = $this->resolveClearingAccount();

        return [
            'invoices' => in_array('invoices', $types) ? $this->runInvoices($params, $actorUserId, $clearingAccount) : null,
            'bills'    => in_array('bills', $types) ? $this->runBills($params, $actorUserId, $clearingAccount) : null,
        ];
    }

    // ---------------------------------------------------------------
    // Querysets
    // ---------------------------------------------------------------

    protected function invoiceQuery(array $params)
    {
        $query = Invoice::where('status', '!=', 'Paid')->where('date', '<=', $params['until']);
        if (!empty($params['from'])) {
            $query->where('date', '>=', $params['from']);
        }
        return $query;
    }

    protected function billQuery(array $params)
    {
        $query = Bill::where('status', '!=', 'Paid')
            ->where('bill_date', '<=', $params['until'])
            ->where('to_be_paid', true);
        if (!empty($params['from'])) {
            $query->where('bill_date', '>=', $params['from']);
        }
        return $query;
    }

    // ---------------------------------------------------------------
    // Preview (read-only)
    // ---------------------------------------------------------------

    protected function summarize($rows): array
    {
        $totalsByCurrency = [];
        $needsApproval = 0;
        $missingCurrency = 0;
        $alreadySettled = 0;
        $dataIssues = 0;

        foreach ($rows as $row) {
            if (is_null($row->currency_id)) {
                $missingCurrency++;
            }
            if ($row->authorization !== 'approved') {
                $needsApproval++;
            }

            $amount = $this->resolveAmount($row->balance, $row->total);
            if ($amount === null) {
                $dataIssues++;
                continue;
            }
            if ($amount <= 0) {
                $alreadySettled++;
                continue;
            }

            $currencyId = $row->currency_id ?? self::DEFAULT_CURRENCY_ID;
            $totalsByCurrency[$currencyId] = ($totalsByCurrency[$currencyId] ?? 0) + $amount;
        }

        return [
            'total'              => $rows->count(),
            'to_settle'          => $rows->count() - $alreadySettled - $dataIssues,
            'needs_approval'     => $needsApproval,
            'missing_currency'   => $missingCurrency,
            'already_settled'    => $alreadySettled,
            'data_issues'        => $dataIssues,
            'totals_by_currency' => $totalsByCurrency,
        ];
    }

    /**
     * Balance is preferred (remaining amount due); falls back to total for
     * records that predate the balance field. Null means the data itself is
     * unusable (non-numeric) and needs manual review rather than a guess.
     */
    protected function resolveAmount($balance, $total): ?float
    {
        if (is_numeric($balance)) {
            return (float) $balance;
        }
        if (is_numeric($total)) {
            return (float) $total;
        }
        return null;
    }

    // ---------------------------------------------------------------
    // Run — Bills
    // ---------------------------------------------------------------

    protected function runBills(array $params, int $actorUserId, Account $clearingAccount): array
    {
        $result = $this->emptyResult();

        $this->billQuery($params)->chunkById(200, function ($bills) use ($actorUserId, $clearingAccount, &$result, $params) {
            foreach ($bills as $bill) {
                $result['total']++;
                try {
                    DB::transaction(function () use ($bill, $actorUserId, $clearingAccount, &$result, $params) {
                        $this->settleBill($bill, $actorUserId, $clearingAccount, $params, $result);
                    });
                } catch (\Throwable $e) {
                    $result['errors'][] = [
                        'bill_id'     => $bill->id,
                        'bill_number' => $bill->bill_number,
                        'message'     => $e->getMessage(),
                    ];
                    Log::error("BulkCatchUpService: failed to settle bill #{$bill->id}: " . $e->getMessage());
                }
            }
        });

        return $result;
    }

    protected function settleBill(Bill $bill, int $actorUserId, Account $clearingAccount, array $params, array &$result): void
    {
        if (is_null($bill->currency_id)) {
            $bill->currency_id = self::DEFAULT_CURRENCY_ID;
            $bill->saveQuietly();
            $result['defaulted_currency'][] = $bill->id;
        }

        $amount = $this->resolveAmount($bill->balance, $bill->total);
        if ($amount === null || $amount <= 0) {
            $result['skipped'][] = [
                'bill_id'     => $bill->id,
                'bill_number' => $bill->bill_number,
                'reason'      => $amount === null ? 'unreadable balance/total' : 'already zero/negative balance',
            ];
            return;
        }

        if ($bill->authorization !== 'approved') {
            $bill->authorization = 'approved';
            $bill->authorized_by_id = $actorUserId;
            $bill->authorization_date = now();
            $bill->saveQuietly();
            app(BillJournalService::class)->post($bill->fresh());
            $result['approved'][] = $bill->id;
        }

        $payment = new Payment;
        $payment->company_id = optional(optional(Auth::user()->employee)->company)->id;
        $payment->vendor_id = $bill->vendor_id;
        $payment->transporter_id = $bill->transporter_id;
        $payment->bill_id = $bill->id;
        $payment->movement = 'Dbt';
        $payment->description = $bill->notes;
        $payment->user_id = $actorUserId;
        $payment->currency_id = $bill->currency_id;
        $payment->payment_number = $this->nextPaymentNumber();
        $payment->category = 'Bill';
        $payment->mode_of_payment = 'Bulk Catch-up';
        $payment->account_id = $clearingAccount->id;
        $payment->amount = $amount;
        $payment->exchange_rate = is_numeric($bill->exchange_rate) ? $bill->exchange_rate : 1;
        $payment->balance = 0;
        $payment->date = $bill->bill_date;
        $payment->notes = $this->runNote($params);
        $payment->save();

        $billPayment = new BillPayment;
        $billPayment->vendor_id = $bill->vendor_id;
        $billPayment->bill_id = $bill->id;
        $billPayment->payment_id = $payment->id;
        $billPayment->source = self::SOURCE;
        $billPayment->currency_id = $bill->currency_id;
        $billPayment->amount = $amount;
        $billPayment->save();

        $bill->balance = 0;
        $bill->status = 'Paid';
        $bill->saveQuietly();

        $clearingAccount->balance = $this->numeric($clearingAccount->balance) - $amount;
        $clearingAccount->save();

        $result['settled'][] = [
            'bill_id'     => $bill->id,
            'bill_number' => $bill->bill_number,
            'amount'      => $amount,
            'currency_id' => $bill->currency_id,
        ];
    }

    // ---------------------------------------------------------------
    // Run — Invoices
    // ---------------------------------------------------------------

    protected function runInvoices(array $params, int $actorUserId, Account $clearingAccount): array
    {
        $result = $this->emptyResult();

        $this->invoiceQuery($params)->chunkById(200, function ($invoices) use ($actorUserId, $clearingAccount, &$result, $params) {
            foreach ($invoices as $invoice) {
                $result['total']++;
                try {
                    DB::transaction(function () use ($invoice, $actorUserId, $clearingAccount, &$result, $params) {
                        $this->settleInvoice($invoice, $actorUserId, $clearingAccount, $params, $result);
                    });
                } catch (\Throwable $e) {
                    $result['errors'][] = [
                        'invoice_id'     => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'message'        => $e->getMessage(),
                    ];
                    Log::error("BulkCatchUpService: failed to settle invoice #{$invoice->id}: " . $e->getMessage());
                }
            }
        });

        return $result;
    }

    protected function settleInvoice(Invoice $invoice, int $actorUserId, Account $clearingAccount, array $params, array &$result): void
    {
        if (is_null($invoice->currency_id)) {
            $invoice->currency_id = self::DEFAULT_CURRENCY_ID;
            $invoice->saveQuietly();
            $result['defaulted_currency'][] = $invoice->id;
        }

        $amount = $this->resolveAmount($invoice->balance, $invoice->total);
        if ($amount === null || $amount <= 0) {
            $result['skipped'][] = [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'reason'         => $amount === null ? 'unreadable balance/total' : 'already zero/negative balance',
            ];
            return;
        }

        if ($invoice->authorization !== 'approved') {
            $invoice->authorization = 'approved';
            $invoice->authorized_by_id = $actorUserId;
            $invoice->authorization_date = now();
            $invoice->saveQuietly();
            app(InvoiceJournalService::class)->post($invoice->fresh());
            $result['approved'][] = $invoice->id;
        }

        $payment = new Payment;
        $payment->company_id = $invoice->company_id ?: optional(optional(Auth::user()->employee)->company)->id;
        $payment->customer_id = $invoice->customer_id;
        $payment->user_id = $actorUserId;
        $payment->currency_id = $invoice->currency_id;
        $payment->payment_number = $this->nextPaymentNumber();
        $payment->movement = 'Crt';
        $payment->category = 'invoice';
        $payment->mode_of_payment = 'Bulk Catch-up';
        $payment->account_id = $clearingAccount->id;
        $payment->invoice_id = $invoice->id;
        $payment->amount = $amount;
        $payment->exchange_rate = is_numeric($invoice->exchange_rate) ? $invoice->exchange_rate : 1;
        $payment->balance = 0;
        $payment->date = $invoice->date;
        $payment->notes = $this->runNote($params);
        $payment->save();

        $invoicePayment = new InvoicePayment;
        $invoicePayment->customer_id = $invoice->customer_id;
        $invoicePayment->invoice_id = $invoice->id;
        $invoicePayment->payment_id = $payment->id;
        $invoicePayment->source = self::SOURCE;
        $invoicePayment->currency_id = $invoice->currency_id;
        $invoicePayment->amount = $amount;
        $invoicePayment->save();

        $invoice->balance = 0;
        $invoice->status = 'Paid';
        $invoice->saveQuietly();

        $receipt = new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
        $receipt->invoice_id = $invoice->id;
        $receipt->customer_id = $invoice->customer_id;
        $receipt->currency_id = $invoice->currency_id;
        $receipt->receipt_number = $this->nextReceiptNumber();
        $receipt->user_id = $actorUserId;
        $receipt->amount = $amount;
        $receipt->balance = 0;
        $receipt->date = $invoice->date;
        $receipt->save();

        $clearingAccount->balance = $this->numeric($clearingAccount->balance) + $amount;
        $clearingAccount->save();

        $result['settled'][] = [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'amount'         => $amount,
            'currency_id'    => $invoice->currency_id,
        ];
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    protected function resolveClearingAccount(): Account
    {
        $account = Account::where('name', self::CLEARING_ACCOUNT_NAME)->first();
        if ($account) {
            return $account;
        }

        $accountType = AccountType::where('name', self::CLEARING_ACCOUNT_TYPE_NAME)->firstOrFail();

        $account = new Account;
        $account->name = self::CLEARING_ACCOUNT_NAME;
        $account->account_type_id = $accountType->id;
        $account->account_type_group_id = $accountType->account_type_group_id;
        $account->currency_id = null;
        $account->balance = 0;
        $account->description = 'Offsetting account for the bulk historical catch-up tool — no real cash moves through this account.';
        $account->save();

        return $account;
    }

    protected function companyInitials(): string
    {
        $user = Auth::user();
        $company = $user?->employee?->company ?: $user?->company;

        if (!$company || !$company->name) {
            return 'BC';
        }

        $words = explode(' ', $company->name);
        return isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
    }

    protected function nextPaymentNumber(): string
    {
        $last = Payment::orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;
        return $this->companyInitials() . 'P' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected function nextReceiptNumber(): string
    {
        $last = Receipt::orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;
        return $this->companyInitials() . 'R' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected function runNote(array $params): string
    {
        $from = $params['from'] ?? 'the beginning';
        return "Bulk historical catch-up settlement (range {$from} .. {$params['until']}), run " . now()->toDateTimeString();
    }

    protected function numeric($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    protected function emptyResult(): array
    {
        return [
            'total'              => 0,
            'settled'            => [],
            'approved'           => [],
            'defaulted_currency' => [],
            'skipped'            => [],
            'errors'             => [],
        ];
    }
}
