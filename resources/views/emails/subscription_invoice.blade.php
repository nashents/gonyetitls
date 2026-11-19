@php
    // Sent BY Gonyeti TLS (the issuer) to a client company - brand it as the issuer.
    $company = $issuer;
@endphp
@extends('layouts.emails')
@section('title')
{{ $type === 'reminder' ? 'Payment Reminder' : 'Invoice' }} {{ $invoice->invoice_number }}
@endsection
@section('heading')
{{ $type === 'reminder' ? 'Payment Reminder' : 'License Invoice' }}
@endsection
@section('content')
<p style="margin:0 0 14px 0;">Good day{{ $recipientName ? ' ' . $recipientName : '' }},</p>

@if ($type === 'reminder')
    <p style="margin:0 0 16px 0;">
        @if ($invoice->isOverdue())
            This is a reminder that invoice <strong>{{ $invoice->invoice_number }}</strong> for your Gonyeti TLS license was due on
            <strong>{{ $invoice->due_date->format('d M Y') }}</strong> and we have not yet recorded payment in full.
        @else
            This is a reminder that invoice <strong>{{ $invoice->invoice_number }}</strong> for your Gonyeti TLS license is due on
            <strong>{{ $invoice->due_date->format('d M Y') }}</strong>.
        @endif
    </p>
@else
    <p style="margin:0 0 16px 0;">
        Please find attached invoice <strong>{{ $invoice->invoice_number }}</strong> for your Gonyeti TLS license,
        covering {{ $invoice->period_start->format('d M Y') }} to {{ $invoice->period_end->format('d M Y') }}.
    </p>
@endif

@include('emails.partials.details', ['rows' => [
    'Company' => $invoice->company?->name,
    'Package' => $invoice->company?->plan_label,
    'Invoice amount' => $invoice->money($invoice->amount),
    'Paid to date' => $invoice->amount_paid > 0 ? $invoice->money($invoice->amount_paid) : null,
    'Amount due' => $invoice->money($invoice->balance),
    'Due date' => $invoice->due_date->format('d M Y'),
]])

<p style="margin:0 0 16px 0;">
    The invoice is attached as a PDF. You can also see all your invoices and payments in Gonyeti TLS under
    Business Settings &gt; Billing &amp; Subscription.
    @if ($type === 'reminder') If you have already paid, please send us the proof of payment so we can update your account. @endif
</p>

<p style="margin:0;">
    Regards,<br>
    {{ $issuer?->name ?? 'Gonyeti TLS' }}
    @if ($issuer?->phonenumber)<br>{{ $issuer->phonenumber }}@endif
    @if ($issuer?->email)<br>{{ $issuer->email }}@endif
</p>
@endsection
