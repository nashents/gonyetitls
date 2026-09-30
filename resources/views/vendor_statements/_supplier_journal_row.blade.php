{{-- Statement "Item" cell for a suppliers journal row (VendorLedgerService transaction_type = supplier_journal). --}}
@php
    $supplier_journal = \App\Models\SupplierJournal::with(['bill_payments.bill'])
        ->where('journal_number', $result->number)
        ->first();
@endphp
@if ($supplier_journal && $supplier_journal->isDebit())
    Journal Debit# {{ $result->number }}
@else
    Journal Credit# {{ $result->number }}
@endif
@if ($supplier_journal)
    @if ($supplier_journal->description)
        - {{ $supplier_journal->description }}
    @endif
    @if ($supplier_journal->reference)
        (Ref: {{ $supplier_journal->reference }})
    @endif
    @if ($supplier_journal->bill_payments->count() > 0)
        <br>applied to
        @foreach ($supplier_journal->bill_payments as $bill_payment)
            @if ($bill_payment->bill)
                <a href="{{ route('bills.show', $bill_payment->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $bill_payment->bill->bill_number }}</a>@if (!$loop->last), @endif
            @endif
        @endforeach
    @endif
@endif
