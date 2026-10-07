<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\InvoicePaymentItem;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

/**
 * Splits a payment against an invoice across its line items, and for trip
 * lines keeps the trip's own payment tracking (amount_paid, payment_status)
 * in step - adding on payment, taking back off when the payment is deleted.
 */
class InvoicePaymentLineService
{
    /**
     * The invoice's lines with what each still has outstanding, as plain
     * arrays (the payment modal holds them as Livewire state).
     */
    public function lines(Invoice $invoice): array
    {
        $items = InvoiceItem::with(['trip:id,trip_number', 'product:id,name'])
            ->where('invoice_id', $invoice->id)
            ->orderBy('id')
            ->get();

        $paid = InvoicePaymentItem::whereIn('invoice_item_id', $items->pluck('id'))
            ->whereHas('invoice_payment') // ignores allocations of soft-deleted payments
            ->groupBy('invoice_item_id')
            ->selectRaw('invoice_item_id, SUM(amount) as paid')
            ->pluck('paid', 'invoice_item_id');

        return $items->map(function (InvoiceItem $item) use ($paid) {
            $total = $this->lineTotal($item);
            $linePaid = round((float) ($paid[$item->id] ?? 0), 2);

            return [
                'id'          => $item->id,
                'trip_id'     => $item->trip_id,
                'label'       => $this->label($item),
                'total'       => $total,
                'paid'        => $linePaid,
                'outstanding' => max(0, round($total - $linePaid, 2)),
            ];
        })->values()->all();
    }

    /**
     * Records the split and updates any trips it touches.
     *
     * @param array $amounts invoice_item_id => amount (invoice currency)
     */
    public function apply(InvoicePayment $invoicePayment, Invoice $invoice, array $amounts): void
    {
        $items = InvoiceItem::with('trip')
            ->where('invoice_id', $invoice->id)
            ->whereIn('id', array_keys($amounts))
            ->get()
            ->keyBy('id');

        foreach ($amounts as $itemId => $amount) {
            $amount = round((float) $amount, 2);
            $item = $items->get($itemId);
            if (! $item || $amount <= 0) {
                continue;
            }

            $row = new InvoicePaymentItem;
            $row->invoice_payment_id = $invoicePayment->id;
            $row->payment_id = $invoicePayment->payment_id;
            $row->invoice_id = $invoice->id;
            $row->invoice_item_id = $item->id;
            $row->trip_id = $item->trip_id;
            $row->amount = $amount;

            if ($item->trip) {
                $row->trip_amount = $this->toTripCurrency($amount, $invoice, $item->trip);
                $this->adjustTrip($item->trip, $row->trip_amount);
            }

            $row->save();
        }
    }

    /** Takes a payment's line split back off its trips and removes it. */
    public function reverse(InvoicePayment $invoicePayment): void
    {
        foreach ($invoicePayment->items()->with('trip')->get() as $row) {
            if ($row->trip && $row->trip_amount) {
                $this->adjustTrip($row->trip, -$row->trip_amount);
            }
            $row->delete();
        }
    }

    protected function adjustTrip(Trip $trip, float $delta): void
    {
        $paid = max(0, round((float) $trip->amount_paid + $delta, 2));
        $freight = (float) $trip->freight;

        $trip->amount_paid = $paid > 0 ? $paid : null;
        $trip->exchange_amount_paid = $paid > 0 && $trip->exchange_rate
            ? round($paid * (float) $trip->exchange_rate, 2)
            : null;

        if ($paid <= 0) {
            $trip->payment_status = null;
            $trip->paid_at = null;
            $trip->paid_by = null;
        } else {
            $trip->payment_status = $paid >= $freight - 0.01 ? 'Paid' : 'Partial';
            if ($delta > 0) {
                $trip->paid_at = now();
                $trip->paid_by = Auth::id();
            }
        }

        $trip->save();
    }

    /**
     * Invoice-currency amount in the trip's currency, going through each
     * side's rate to the company currency when the two differ.
     */
    protected function toTripCurrency(float $amount, Invoice $invoice, Trip $trip): float
    {
        if (! $trip->currency_id || (int) $trip->currency_id === (int) $invoice->currency_id) {
            return $amount;
        }

        $invoiceRate = is_numeric($invoice->exchange_rate) && $invoice->exchange_rate > 0 ? (float) $invoice->exchange_rate : 1.0;
        $tripRate = is_numeric($trip->exchange_rate) && $trip->exchange_rate > 0 ? (float) $trip->exchange_rate : 1.0;

        return round($amount * $invoiceRate / $tripRate, 2);
    }

    protected function lineTotal(InvoiceItem $item): float
    {
        foreach (['subtotal_incl', 'subtotal'] as $field) {
            if (is_numeric($item->{$field})) {
                return round((float) $item->{$field}, 2);
            }
        }

        return is_numeric($item->amount) && is_numeric($item->qty)
            ? round($item->amount * $item->qty, 2)
            : 0.0;
    }

    protected function label(InvoiceItem $item): string
    {
        $parts = [];
        if ($item->trip) {
            $parts[] = 'Trip ' . $item->trip->trip_number;
        }
        if ($item->product) {
            $parts[] = $item->product->name;
        }
        if ($item->description) {
            $parts[] = \Illuminate\Support\Str::limit(strip_tags($item->description), 60);
        }

        return $parts ? implode(' - ', $parts) : 'Line #' . $item->id;
    }
}
