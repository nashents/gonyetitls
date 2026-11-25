<?php

use App\Models\Account;
use App\Models\FreightCharge;
use App\Models\Invoice;
use App\Services\Freight\FreightAccountingService;
use Illuminate\Database\Migrations\Migration;

/**
 * Invoices generated from Freight Job Costing (FRI…) before 2026-11-24 were
 * saved without company_id (preview/PDF crashed on $company->logo), balance
 * (payments, statements, aged AR), exchange_amount, due date and approval
 * stamps, and their lines only said the charge type name. Fills those in
 * the way FreightAccountingService::generateInvoicesFromCharges now does.
 * Only empty fields are filled; amounts and journals are not touched.
 */
class RepairFreightGeneratedInvoices extends Migration
{
    public function up()
    {
        $salesId = Account::where('name', 'Sales')->value('id');

        $invoiceIds = FreightCharge::withTrashed()->whereNotNull('invoice_id')->distinct()->pluck('invoice_id');

        foreach (Invoice::whereIn('id', $invoiceIds)->with('invoice_items')->get() as $invoice) {
            $charges = FreightCharge::withTrashed()->where('invoice_id', $invoice->id)->with(['charge_type', 'freight_job'])->get();
            $job = $charges->first()?->freight_job;

            if (!$job) {
                continue;
            }

            $rate = (float) ($invoice->exchange_rate ?: 1);

            $invoice->company_id = $invoice->company_id ?? $job->company_id;
            $invoice->account_id = $invoice->account_id ?? $salesId;
            $invoice->expiry = $invoice->expiry ?? $invoice->date;
            $invoice->reason = $invoice->reason ?? 'Freight';
            $invoice->memo = $invoice->memo ?? ('Freight Job ' . $job->job_number);
            $invoice->authorized_by_id = $invoice->authorized_by_id ?? $invoice->user_id;
            $invoice->authorization_date = $invoice->authorization_date ?? $invoice->created_at;
            if ($invoice->exchange_amount === null && is_numeric($invoice->total)) {
                $invoice->exchange_amount = round($rate * (float) $invoice->total, 2);
            }
            $invoice->save();

            // balance/status from payments, allocations and credit notes (none yet = total).
            $invoice->recalculateBalance();

            // Lines were created in charge order; match each by description + amount.
            $unmatched = $charges->values();
            foreach ($invoice->invoice_items as $item) {
                $index = $unmatched->search(fn ($c) => ($c->charge_type->name ?? 'Freight Charge') === $item->description
                    && round((float) $c->amount, 2) === round((float) $item->amount, 2));

                if ($index !== false) {
                    $item->description = FreightAccountingService::chargeDescription($unmatched[$index], $job);
                    $unmatched->forget($index);
                }
                if ($item->exchange_amount === null && is_numeric($item->subtotal_incl)) {
                    $item->exchange_rate = $item->exchange_rate ?? $rate;
                    $item->exchange_amount = round($rate * (float) $item->subtotal_incl, 2);
                }
                $item->save();
            }
        }
    }

    public function down()
    {
        // Data repair - nothing to undo.
    }
}
