<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Transporter;
use App\Models\TripExpense;
use App\Services\VendorLedgerService;
use Illuminate\Support\Collection;

/**
 * "Trip Settlement" transporter statement: per trip what the transporter
 * earned (gross freight), what was recovered from them (trip expenses / fuel
 * the company or customer paid on their behalf - deducted on the bill or
 * invoiced to them), what was paid, and what is still due either way.
 *
 * Reads, never writes. The payable side reconciles to VendorLedgerService
 * (party "transporter"): opening + net bills - payments = closing. Invoices
 * to the transporter (invoice recovery mode) sit in Accounts Receivable, so
 * they are shown beside the payable and netted only in "net position".
 */
class TransporterSettlementService
{
    public function __construct(
        private VendorLedgerService $ledger,
        private TripExpenseRecoveryService $recovery
    ) {
    }

    /**
     * @return array<int, array> keyed by currency id; empty when the
     * transporter has nothing in any currency.
     */
    public function build(Transporter $transporter, string $from, string $to): array
    {
        $recoverableId = optional(\App\Models\Account::where('name', TripExpenseRecoveryService::TRANSPORTER_RECOVERABLE)->first())->id;

        $tripBills = Bill::with(['bill_expenses', 'trip.loading_point', 'trip.offloading_point', 'trip.company', 'trip_expense'])
            ->where('transporter_id', $transporter->id)
            ->where('category', TransporterPaymentBillService::CATEGORY)
            ->whereNull('deleted_at')
            ->get();

        $periodBills = $tripBills->filter(fn (Bill $b) => $b->authorization === 'approved' && $this->inPeriod($b->bill_date, $from, $to));
        $pendingBills = $tripBills->filter(fn (Bill $b) => $b->authorization === 'pending');

        $otherBills = Bill::with('vendor')
            ->where('transporter_id', $transporter->id)
            ->where(fn ($q) => $q->whereNull('category')->orWhere('category', '!=', TransporterPaymentBillService::CATEGORY))
            ->where('authorization', 'approved')
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (Bill $b) => $this->inPeriod($b->bill_date, $from, $to));

        $invoices = Invoice::with('invoice_items')
            ->where('transporter_id', $transporter->id)
            ->where('authorization', 'approved')
            ->whereNull('deleted_at')
            ->get();

        $payments = Payment::where('transporter_id', $transporter->id)
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn (Payment $p) => $this->inPeriod($p->date, $from, $to));

        $currencyIds = collect()
            ->merge($tripBills->pluck('currency_id'))
            ->merge($otherBills->pluck('currency_id'))
            ->merge($invoices->pluck('currency_id'))
            ->merge($payments->pluck('currency_id'))
            ->filter()->unique()->values();

        // Opening/closing balances carry history from outside the period too.
        $ledgerCurrencies = Bill::where('transporter_id', $transporter->id)->where('authorization', 'approved')->pluck('currency_id');
        $currencyIds = $currencyIds->merge($ledgerCurrencies)->filter()->unique()->values();

        $result = [];
        foreach ($currencyIds as $currencyId) {
            $currencyId = (int) $currencyId;

            $trips = $periodBills->where('currency_id', $currencyId)
                ->sortBy('bill_date')
                ->map(fn (Bill $b) => $this->tripRow($b, $recoverableId, $invoices))
                ->values();

            $inTransit = $pendingBills->where('currency_id', $currencyId)
                ->sortBy('bill_date')
                ->map(fn (Bill $b) => $this->tripRow($b, $recoverableId, $invoices, true))
                ->values();

            $other = $otherBills->where('currency_id', $currencyId)->sortBy('bill_date')->map(fn (Bill $b) => [
                'bill'        => $b,
                'number'      => $b->bill_number,
                'date'        => substr((string) $b->bill_date, 0, 10),
                'description' => trim(($b->category ?: 'Bill').($b->vendor ? ' - '.$b->vendor->name : '')),
                'total'       => round((float) $b->total, 2),
                'paid'        => round((float) $b->total - (float) $b->balance, 2),
                'due'         => round((float) $b->balance, 2),
            ])->values();

            $periodPayments = $payments->where('currency_id', $currencyId)->sortBy('date')->map(fn (Payment $p) => [
                'payment' => $p,
                'number'  => $p->payment_number,
                'date'    => substr((string) $p->date, 0, 10),
                'amount'  => round((float) $p->amount, 2),
                'notes'   => $p->notes,
            ])->values();

            $currencyInvoices = $invoices->where('currency_id', $currencyId);
            $invoiceRows = $currencyInvoices
                ->filter(fn (Invoice $i) => $this->inPeriod($i->date, $from, $to) || (float) $i->balance > 0.009)
                ->sortBy('date')
                ->map(fn (Invoice $i) => [
                    'invoice' => $i,
                    'number'  => $i->invoice_number,
                    'date'    => substr((string) $i->date, 0, 10),
                    'trips'   => $i->invoice_items->pluck('trip_id')->filter()->unique()->count(),
                    'total'   => round((float) $i->total, 2),
                    'paid'    => round((float) $i->total - (float) $i->balance, 2),
                    'due'     => round((float) $i->balance, 2),
                ])->values();

            $opening = $this->ledger->openingBalance($transporter->id, $currencyId, $from, VendorLedgerService::PARTY_TRANSPORTER);
            $closing = $this->ledger->closingBalance($transporter->id, $currencyId, $to, VendorLedgerService::PARTY_TRANSPORTER);
            $invoicesDue = round($currencyInvoices->sum(fn (Invoice $i) => max(0, (float) $i->balance)), 2);

            if ($trips->isEmpty() && $inTransit->isEmpty() && $other->isEmpty() && $periodPayments->isEmpty()
                && $invoiceRows->isEmpty() && abs($opening) < 0.01 && abs($closing) < 0.01) {
                continue;
            }

            $result[$currencyId] = [
                'currency'   => Currency::find($currencyId),
                'trips'      => $trips,
                'in_transit' => $inTransit,
                'other'      => $other,
                'payments'   => $periodPayments,
                'invoices'   => $invoiceRows,
                'totals'     => [
                    'opening'          => $opening,
                    'freight'          => round($trips->sum('freight'), 2),
                    'recoverables'     => round($trips->sum('recoverables_total'), 2),
                    'deducted'         => round($trips->sum('deducted'), 2),
                    'invoiced'         => round($trips->sum('invoiced'), 2),
                    'unrecovered'      => round($trips->sum('unrecovered'), 2),
                    'net_trip_bills'   => round($trips->sum('net'), 2),
                    'other_bills'      => round($other->sum('total'), 2),
                    'payments'         => round($periodPayments->sum('amount'), 2),
                    'closing'          => $closing,
                    'invoices_due'     => $invoicesDue,
                    'net_position'     => round($closing - $invoicesDue, 2),
                    'in_transit_net'   => round($inTransit->sum('net'), 2),
                    'net_payable_trips' => round($trips->sum('net_payable'), 2),
                    // What to actually pay the transporter now: everything due to them
                    // (closing balance) less what they owe back - unpaid invoices to
                    // them and expenses paid on their behalf not yet recovered.
                    'net_payable'      => round($closing - $invoicesDue - $trips->sum('unrecovered'), 2),
                ],
            ];
        }

        return $result;
    }

    private function tripRow(Bill $bill, ?int $recoverableId, Collection $invoices, bool $projected = false): array
    {
        $trip = $bill->trip;
        $lines = $bill->bill_expenses;

        $freight = round($lines->filter(fn ($l) => (int) $l->account_id !== (int) $recoverableId)->sum(fn ($l) => (float) $l->amount), 2);
        $deducted = round(abs($lines->filter(fn ($l) => (int) $l->account_id === (int) $recoverableId)->sum(fn ($l) => (float) $l->amount)), 2);
        if ($freight <= 0) {
            // Bill without lines (legacy): fall back to its header + any deduction.
            $freight = round((float) $bill->total + $deducted, 2);
        }

        $items = collect();
        if ($trip) {
            $items = $this->recovery->transporterDeductions($trip)->map(fn (TripExpense $e) => [
                'name'   => $this->itemName($e),
                'date'   => substr((string) ($e->date ?? $e->created_at), 0, 10),
                'funded' => $e->supplied_by_customer ? 'Customer' : 'Company',
                'amount' => round($this->recovery->amountIn($e, $bill->currency_id, $trip->company, $bill->exchange_rate), 2),
            ]);
        }
        $recoverablesTotal = round($items->sum('amount'), 2);

        $invoiced = 0.0;
        if ($trip) {
            $invoiced = round((float) InvoiceItem::whereIn('invoice_id', $invoices->where('currency_id', $bill->currency_id)->pluck('id'))
                ->where('trip_id', $trip->id)
                ->sum('subtotal_incl'), 2);
        }

        $net = round((float) $bill->total, 2);

        return [
            'bill'               => $bill,
            'trip'               => $trip,
            'trip_number'        => optional($trip)->trip_number ?? '-',
            'date'               => substr((string) $bill->bill_date, 0, 10),
            'route'              => $trip ? trim(optional($trip->loading_point)->name.' - '.optional($trip->offloading_point)->name, ' -') : '',
            'status'             => optional($trip)->trip_status,
            'freight'            => $freight,
            'items'              => $items,
            'recoverables_total' => $recoverablesTotal,
            'deducted'           => $deducted,
            'invoiced'           => $invoiced,
            'unrecovered'        => round(max(0, $recoverablesTotal - $deducted - $invoiced), 2),
            'net'                => $net,
            'paid'               => $projected ? 0.0 : round($net - (float) $bill->balance, 2),
            'due'                => $projected ? $net : round((float) $bill->balance, 2),
            // Still due on the trip less expenses paid for the transporter on it
            // that were neither deducted nor invoiced - what to pay for this trip.
            'net_payable'        => round(($projected ? $net : (float) $bill->balance) - max(0, $recoverablesTotal - $deducted - $invoiced), 2),
        ];
    }

    private function itemName(TripExpense $e): string
    {
        if ($e->fuel_id && $e->fuel) {
            $litres = (float) $e->fuel->quantity;
            return 'Fuel'.($e->fuel->order_number ? ' '.$e->fuel->order_number : '').($litres > 0 ? ' ('.rtrim(rtrim(number_format($litres, 2), '0'), '.').' L)' : '');
        }

        return optional($e->expense)->name ?? optional($e->allowance)->name ?? 'Expense';
    }

    private function inPeriod($date, string $from, string $to): bool
    {
        $d = substr((string) $date, 0, 10);

        return $d !== '' && $d >= $from && $d <= $to;
    }
}
