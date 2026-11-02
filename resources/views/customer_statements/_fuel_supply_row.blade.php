{{-- Statement "Item" cell for a customer-supplied fuel row (CustomerLedgerService transaction_type = customer_fuel). --}}
@php
    $fuel_supply = \App\Models\CustomerFuelSupply::with([
            'invoice_payments.invoice', 'trip',
            // Deleted source orders still show, so a stale supply can be traced.
            'fuel' => fn ($q) => $q->withTrashed(),
            'top_up' => fn ($q) => $q->withTrashed(),
        ])
        ->where('supply_number', $result->number)
        ->first();
    $fuel_source = $fuel_supply ? ($fuel_supply->fuel ?? $fuel_supply->top_up) : null;
@endphp
@if ($fuel_supply && $fuel_supply->trip_expense_id)
    Paid by customer# {{ $result->number }} - {{ $fuel_supply->description }}
@else
    Fuel Supplied# {{ $result->number }}
@endif
@if ($fuel_supply)
    @if ($fuel_supply->quantity)
        ({{ number_format((float) $fuel_supply->quantity, 2) }} L)
    @endif
    @if ($fuel_source)
        - {{ $fuel_supply->fuel ? 'Fuel Order' : 'Top Up' }}# {{ $fuel_source->order_number }}@if ($fuel_source->trashed()) (deleted)@endif
    @endif
    @if ($fuel_supply->trip && !$fuel_supply->trip_expense_id)
        - Trip# {{ $fuel_supply->trip->trip_number }}
    @endif
    @if ($fuel_supply->invoice_payments->count() > 0)
        <br>applied to
        @foreach ($fuel_supply->invoice_payments as $invoice_payment)
            @if ($invoice_payment->invoice)
                <a href="{{ route('invoices.show', $invoice_payment->invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Invoice# {{ $invoice_payment->invoice->invoice_number }}</a>@if (!$loop->last), @endif
            @endif
        @endforeach
    @endif
@endif
