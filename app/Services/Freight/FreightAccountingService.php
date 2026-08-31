<?php

namespace App\Services\Freight;

use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\FreightCharge;
use App\Models\FreightCost;
use App\Models\FreightJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Accounting\BillJournalService;
use App\Services\Accounting\InvoiceJournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Converts approved FreightCost/FreightCharge lines into real Bills/Invoices
 * that post to the GL through the existing accounting engine. Follows
 * FuelJournalService's exact idiom: guarded models built via direct
 * property assignment, authorization/to_be_paid set LAST (so the
 * Bill/InvoiceObserver's isDirty('authorization') auto-post fires only
 * once the child lines already exist), then an explicit, idempotent
 * post() call as well.
 *
 * FreightCost/FreightCharge are multi-currency PER LINE, but a Bill/Invoice
 * header carries one currency for all its lines - so lines are grouped by
 * (vendor_id, currency_id) for costs and (customer_id, currency_id) for
 * charges, producing one Bill/Invoice per group.
 */
class FreightAccountingService
{
    public function __construct(
        private BillJournalService $billJournal,
        private InvoiceJournalService $invoiceJournal
    ) {
    }

    /**
     * Returns ['bills' => Collection<Bill>, 'warnings' => string[]].
     */
    public function generateBillsFromCosts(FreightJob $job): array
    {
        return DB::transaction(function () use ($job) {
            $eligible = FreightCost::where('freight_job_id', $job->id)
                ->whereIn('verification_status', ['verified', 'approved'])
                ->whereNull('bill_id')
                ->with('charge_type')
                ->get();

            $bills = collect();
            $warnings = [];

            $groups = $eligible->groupBy(fn (FreightCost $cost) => $cost->vendor_id . ':' . ($cost->currency_id ?? $job->currency_id));

            foreach ($groups as $costLines) {
                $vendorId = $costLines->first()->vendor_id;
                $currencyId = $costLines->first()->currency_id ?? $job->currency_id;

                if (!$vendorId) {
                    $warnings[] = "Skipped {$costLines->count()} cost line(s) with no supplier assigned.";
                    continue;
                }

                // BillJournalService silently SKIPS a debit line with no
                // account_id, which would produce an imbalanced (AP-only,
                // no expense) journal entry - refuse to generate rather
                // than post something incorrect. Only the unmapped lines
                // themselves are excluded; mapped lines sharing the same
                // vendor+currency group still get billed.
                $unmapped = $costLines->filter(fn ($c) => !$c->charge_type || !$c->charge_type->expense_account_id);
                if ($unmapped->isNotEmpty()) {
                    $names = $unmapped->pluck('charge_type.name')->filter()->unique()->implode(', ') ?: 'unnamed charge type';
                    $warnings[] = "Skipped {$unmapped->count()} cost line(s): no expense account mapped for [{$names}]. Map it under Freight > Master > Charge Types first.";
                    $costLines = $costLines->diff($unmapped);
                }

                if ($costLines->isEmpty()) {
                    continue;
                }

                $subtotal = (float) $costLines->sum('amount');
                $tax = (float) $costLines->sum('tax_amount');

                $bill = new Bill;
                $bill->user_id = Auth::id();
                $bill->bill_number = $this->billNumber();
                $bill->vendor_id = $vendorId;
                $bill->category = 'Freight';
                $bill->bill_date = now();
                $bill->currency_id = $currencyId;
                $bill->exchange_rate = $costLines->first()->exchange_rate ?: 1;
                $bill->subtotal = $subtotal;
                $bill->tax_amount = $tax;
                $bill->total = $subtotal + $tax;
                $bill->balance = $subtotal + $tax;
                $bill->authorized_by_id = Auth::id();
                $bill->save();

                foreach ($costLines as $cost) {
                    $billExpense = new BillExpense;
                    $billExpense->user_id = Auth::id();
                    $billExpense->bill_id = $bill->id;
                    $billExpense->currency_id = $cost->currency_id ?? $currencyId;
                    $billExpense->account_id = $cost->charge_type->expense_account_id;
                    $billExpense->account_type_id = $cost->charge_type->expense_account?->account_type_id;
                    $billExpense->tax_id = $cost->tax_id;
                    $billExpense->tax_rate = $cost->tax_rate;
                    $billExpense->tax_amount = $cost->tax_amount;
                    $billExpense->qty = $cost->quantity ?: 1;
                    $billExpense->amount = $cost->amount;
                    $billExpense->subtotal = $cost->amount;
                    $billExpense->subtotal_incl = (float) $cost->amount + (float) ($cost->tax_amount ?? 0);
                    $billExpense->description = $cost->charge_type->name ?? 'Freight Cost';
                    $billExpense->save();

                    $cost->bill_id = $bill->id;
                    $cost->accounting_status = 'posted';
                    $cost->save();
                }

                $bill->to_be_paid = true;
                $bill->authorization = 'approved';
                $bill->save();

                $this->billJournal->post($bill->fresh());

                $bills->push($bill);
            }

            return ['bills' => $bills, 'warnings' => $warnings];
        });
    }

    /**
     * Returns ['invoices' => Collection<Invoice>, 'warnings' => string[]].
     */
    public function generateInvoicesFromCharges(FreightJob $job): array
    {
        return DB::transaction(function () use ($job) {
            $eligible = FreightCharge::where('freight_job_id', $job->id)
                ->where('status', 'approved')
                ->whereNull('invoice_id')
                ->with('charge_type')
                ->get();

            $invoices = collect();
            $warnings = [];

            $groups = $eligible->groupBy(fn (FreightCharge $charge) => ($charge->customer_id ?? $job->customer_id) . ':' . ($charge->currency_id ?? $job->currency_id));

            foreach ($groups as $chargeLines) {
                $customerId = $chargeLines->first()->customer_id ?? $job->customer_id;
                $currencyId = $chargeLines->first()->currency_id ?? $job->currency_id;

                if (!$customerId) {
                    $warnings[] = "Skipped {$chargeLines->count()} charge line(s) with no customer assigned.";
                    continue;
                }

                $subtotal = (float) $chargeLines->sum('amount');
                $tax = (float) $chargeLines->sum('tax_amount');

                $invoice = new Invoice;
                $invoice->user_id = Auth::id();
                $invoice->invoice_number = $this->invoiceNumber();
                $invoice->customer_id = $customerId;
                $invoice->currency_id = $currencyId;
                $invoice->date = now();
                $invoice->exchange_rate = $chargeLines->first()->exchange_rate ?: 1;
                $invoice->subtotal = $subtotal;
                $invoice->tax_amount = $tax;
                $invoice->total = $subtotal + $tax;
                $invoice->invoice_type = 'earned';
                $invoice->save();

                foreach ($chargeLines as $charge) {
                    $invoiceItem = new InvoiceItem;
                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->account_id = $charge->charge_type->revenue_account_id ?? null;
                    $invoiceItem->tax_id = $charge->tax_id;
                    $invoiceItem->tax_rate = $charge->tax_rate;
                    $invoiceItem->tax_amount = $charge->tax_amount;
                    $invoiceItem->qty = $charge->quantity ?: 1;
                    $invoiceItem->amount = $charge->amount;
                    $invoiceItem->subtotal = $charge->amount;
                    $invoiceItem->subtotal_incl = (float) $charge->amount + (float) ($charge->tax_amount ?? 0);
                    $invoiceItem->description = $charge->charge_type->name ?? 'Freight Charge';
                    $invoiceItem->save();

                    $charge->invoice_id = $invoice->id;
                    $charge->accounting_status = 'posted';
                    $charge->save();
                }

                $invoice->authorization = 'approved';
                $invoice->save();

                $this->invoiceJournal->post($invoice->fresh());

                $invoices->push($invoice);
            }

            return ['invoices' => $invoices, 'warnings' => $warnings];
        });
    }

    private function billNumber(): string
    {
        $last = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return 'FRB' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    private function invoiceNumber(): string
    {
        $last = Invoice::latest()->orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return 'FRI' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
