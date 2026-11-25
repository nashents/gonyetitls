<?php

namespace App\Services\Freight;

use App\Models\Account;
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

            if ($eligible->isEmpty()) {
                return ['bills' => $bills, 'warnings' => $this->whyNothingEligible(
                    FreightCost::where('freight_job_id', $job->id)->get(['verification_status', 'bill_id']),
                    'verification_status', 'bill_id', FreightCost::VERIFICATION_STATUSES,
                    'cost', 'billed', 'Verified or Approved'
                )];
            }

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

            if ($eligible->isEmpty()) {
                return ['invoices' => $invoices, 'warnings' => $this->whyNothingEligible(
                    FreightCharge::where('freight_job_id', $job->id)->get(['status', 'invoice_id']),
                    'status', 'invoice_id', FreightCharge::STATUSES,
                    'charge', 'invoiced', 'Approved'
                )];
            }

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
                $total = $subtotal + $tax;
                $rate = (float) ($chargeLines->first()->exchange_rate ?: 1);

                // Same header fields a normal invoice gets: company (logo/letterhead on
                // preview + PDF), balance (payments, statements, aged AR), exchange amount.
                $invoice = new Invoice;
                $invoice->user_id = Auth::id();
                $invoice->company_id = $job->company_id ?? Auth::user()?->employee?->company_id;
                $invoice->invoice_number = $this->invoiceNumber();
                $invoice->customer_id = $customerId;
                $invoice->currency_id = $currencyId;
                $invoice->account_id = Account::where('name', 'Sales')->value('id');
                $invoice->date = now();
                $invoice->expiry = now();
                $invoice->reason = 'Freight';
                $invoice->memo = 'Freight Job ' . $job->job_number;
                $invoice->exchange_rate = $rate;
                $invoice->subtotal = $subtotal;
                $invoice->tax_amount = $tax;
                $invoice->total = $total;
                $invoice->balance = $total;
                $invoice->exchange_amount = round($rate * $total, 2);
                $invoice->status = 'Unpaid';
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
                    $invoiceItem->exchange_rate = $rate;
                    $invoiceItem->exchange_amount = round($rate * (float) $invoiceItem->subtotal_incl, 2);
                    $invoiceItem->description = self::chargeDescription($charge, $job);
                    $invoiceItem->save();

                    $charge->invoice_id = $invoice->id;
                    $charge->accounting_status = 'posted';
                    $charge->save();
                }

                $invoice->authorized_by_id = Auth::id();
                $invoice->authorization_date = now();
                $invoice->authorization = 'approved';
                $invoice->save();

                $this->invoiceJournal->post($invoice->fresh());

                $invoices->push($invoice);
            }

            return ['invoices' => $invoices, 'warnings' => $warnings];
        });
    }

    /** Invoice line text, e.g. "Ocean Freight - FF-2026-000017 / 2 x 945.00 - Port of Beira". */
    public static function chargeDescription(FreightCharge $charge, FreightJob $job): string
    {
        $text = ($charge->charge_type->name ?? 'Freight Charge') . ' - ' . $job->job_number;

        if ((float) $charge->quantity > 1 && $charge->rate) {
            $text .= ' / ' . rtrim(rtrim(number_format((float) $charge->quantity, 3, '.', ''), '0'), '.') . ' x ' . number_format((float) $charge->rate, 2);
        }
        if (trim((string) $charge->notes) !== '') {
            $text .= ' - ' . trim($charge->notes);
        }

        return $text;
    }

    /**
     * Explains an empty generate run, e.g. "3 cost line(s) are still
     * Received - set them to Verified or Approved first."
     */
    private function whyNothingEligible($lines, string $statusField, string $linkField, array $labels, string $noun, string $doneWord, string $needed): array
    {
        if ($lines->isEmpty()) {
            return ["This job has no {$noun} lines yet."];
        }

        $reasons = [];
        $done = $lines->whereNotNull($linkField)->count();
        if ($done) {
            $reasons[] = "{$done} {$noun} line(s) already {$doneWord}.";
        }

        $waiting = $lines->whereNull($linkField)->groupBy($statusField);
        foreach ($waiting as $status => $group) {
            $label = $labels[$status] ?? ucwords(str_replace('_', ' ', (string) $status));
            $reasons[] = in_array($status, ['cancelled', 'rejected'], true)
                ? "{$group->count()} {$noun} line(s) are {$label} and will not be {$doneWord}."
                : "{$group->count()} {$noun} line(s) are {$label} - set them to {$needed} first (Status buttons on the line).";
        }

        return $reasons;
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
