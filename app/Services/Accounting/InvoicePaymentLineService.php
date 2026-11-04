<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\InvoicePaymentItem;
use App\Models\Trip;
use App\Models\TripPayment;
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
            }

            $row->save();

            if ($item->trip) {
                $this->recordTripPayment($item->trip, $row, $invoicePayment, $invoice);
            }
        }
    }

    /** Takes a payment's line split back off its trips and removes it. */
    public function reverse(InvoicePayment $invoicePayment): void
    {
        $trips = app(TripPaymentService::class);

        foreach ($invoicePayment->items()->with('trip')->get() as $row) {
            TripPayment::where('invoice_payment_item_id', $row->id)->delete();
            $row->delete();

            if ($row->trip) {
                $trips->recalculate($row->trip);
            }
        }
    }

    /** Adds the line split to the trip's payment history, dated on the payment. */
    protected function recordTripPayment(Trip $trip, InvoicePaymentItem $row, InvoicePayment $invoicePayment, Invoice $invoice): void
    {
        $trips = app(TripPaymentService::class);
        $payment = $invoicePayment->payment;
        $amount = (float) $row->amount;
        $base = $trips->baseCurrencyId($trip);

        $extra = [
            'source' => 'invoice',
            'invoice_payment_item_id' => $row->id,
            'notes' => 'Invoice ' . $invoice->invoice_number,
            'user_id' => $payment?->user_id ?? Auth::id(),
        ];

        // Cross-currency receipts carry their effective invoice → reporting rate on the payment.
        if ((int) $invoice->currency_id === (int) $base) {
            $extra['exchange_amount'] = round($amount, 2);
        } elseif (is_numeric($payment?->exchange_rate) && $payment->exchange_rate > 0) {
            $extra['exchange_amount'] = round($amount * (float) $payment->exchange_rate, 2);
        }

        $trips->record(
            $trip,
            $payment?->date ? \Carbon\Carbon::parse($payment->date)->toDateString() : now()->toDateString(),
            $invoice->currency_id,
            $amount,
            $amount > 0 ? (float) $row->trip_amount / $amount : 1.0,
            $extra
        );
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
