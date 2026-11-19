@extends('layouts.emails')
@section('title')
Invoice {{ $invoice->invoice_number }} | {{ $company->name ?? '' }}
@endsection
@section('heading')
Invoice {{ $invoice->invoice_number }}
@endsection
@section('content')
@php
    $symbol = optional($invoice->currency)->symbol;
    $money = fn ($v) => $symbol . number_format((float) $v, 2);
@endphp
<p style="margin:0 0 14px 0;">Dear {{ optional($invoice->customer)->name ?: 'Customer' }},</p>
<p style="margin:0 0 14px 0;">
    Please find attached invoice <strong>{{ $invoice->invoice_number }}</strong>@if($invoice->subheading) for {{ $invoice->subheading }}@endif.
</p>
@include('emails.partials.details', ['rows' => [
    'Invoice number' => $invoice->invoice_number,
    'Invoice date' => $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('d M Y') : null,
    'Due date' => $invoice->expiry ? \Carbon\Carbon::parse($invoice->expiry)->format('d M Y') : null,
    'Subtotal' => $invoice->vat_amount ? $money($invoice->subtotal) : null,
    'VAT' => $invoice->vat_amount ? $money($invoice->vat_amount) : null,
    'Total' => $money($invoice->total),
]])
<p style="margin:0 0 14px 0;">The full invoice is attached as a PDF. Please contact us if you have any questions about it.</p>
<p style="margin:20px 0 0 0;">Kind regards,<br>{{ $company->name ?? '' }}</p>
@endsection
