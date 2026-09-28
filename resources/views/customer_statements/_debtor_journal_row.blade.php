{{-- Statement "Item" cell for a debtors journal row (CustomerLedgerService transaction_type = debtor_journal). --}}
@php
    $debtor_journal = \App\Models\DebtorJournal::with(['invoice_payments.invoice'])
        ->where('journal_number', $result->number)
        ->first();
@endphp
@if ($debtor_journal && $debtor_journal->isCredit())
    Journal Credit# {{ $result->number }}
@else
    Journal Debit# {{ $result->number }}
@endif
@if ($debtor_journal)
    @if ($debtor_journal->description)
        - {{ $debtor_journal->description }}
    @endif
    @if ($debtor_journal->reference)
        (Ref: {{ $debtor_journal->reference }})
    @endif
    @if ($debtor_journal->invoice_payments->count() > 0)
        <br>applied to
        @foreach ($debtor_journal->invoice_payments as $invoice_payment)
            @if ($invoice_payment->invoice)
                <a href="{{ route('invoices.show', $invoice_payment->invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Invoice# {{ $invoice_payment->invoice->invoice_number }}</a>@if (!$loop->last), @endif
            @endif
        @endforeach
    @endif
@endif
