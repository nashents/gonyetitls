@extends('layouts.emails')
@section('title')
Unrecognized Advance Invoices | {{ $company->name ?? '' }}
@endsection
@section('heading')
Unrecognized Advance Invoices
@endsection
@section('content')
<p style="margin:0 0 16px 0;">
    The following approved Advance invoices have been outstanding for {{ $agingDays }}+ days without their linked trip(s) reaching Offloaded status — their revenue is still parked in Customer Advances rather than Sales. Please follow up: chase the trip's delivery, or review whether the invoice needs manual attention.
</p>
<table role="presentation" style="width:100%;border-collapse:collapse;font-size:13px;">
    <tr style="background:#f4f6f8;">
        <th style="padding:8px;border-bottom:2px solid #d9dde3;text-align:left;">Invoice #</th>
        <th style="padding:8px;border-bottom:2px solid #d9dde3;text-align:left;">Customer</th>
        <th style="padding:8px;border-bottom:2px solid #d9dde3;text-align:right;">Total</th>
        <th style="padding:8px;border-bottom:2px solid #d9dde3;text-align:left;">Invoice Date</th>
        <th style="padding:8px;border-bottom:2px solid #d9dde3;text-align:right;">Days</th>
    </tr>
    @foreach ($invoices as $invoice)
        <tr>
            <td style="padding:8px;border-bottom:1px solid #eef0f3;">{{ $invoice->invoice_number }}</td>
            <td style="padding:8px;border-bottom:1px solid #eef0f3;">{{ $invoice->customer?->name }}</td>
            <td style="padding:8px;border-bottom:1px solid #eef0f3;text-align:right;white-space:nowrap;">{{ $invoice->currency?->symbol }}{{ number_format($invoice->total, 2) }}</td>
            <td style="padding:8px;border-bottom:1px solid #eef0f3;">{{ $invoice->date }}</td>
            <td style="padding:8px;border-bottom:1px solid #eef0f3;text-align:right;">{{ \Carbon\Carbon::parse($invoice->date)->diffInDays(now()) }}</td>
        </tr>
    @endforeach
</table>
@endsection
