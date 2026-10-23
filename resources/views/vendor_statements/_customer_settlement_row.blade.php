{{-- Statement "Item" cell for a bill the customer settled (VendorLedgerService transaction_type = customer_settlement). --}}
@php
    $settlement = \App\Models\BillPayment::with(['bill', 'customer_fuel_supply.customer'])->find($result->id);
    $settled_supply = $settlement?->customer_fuel_supply;
@endphp
Paid by customer# {{ $result->number }}
@if ($settled_supply?->customer)
    - {{ $settled_supply->customer->name }}
@endif
@if ($settled_supply?->description)
    ({{ $settled_supply->description }})
@endif
@if ($settlement?->bill)
    <br>for <a href="{{ route('bills.show', $settlement->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $settlement->bill->bill_number }}</a>
@endif
