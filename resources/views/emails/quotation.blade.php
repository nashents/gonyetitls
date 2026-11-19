@extends('layouts.emails')
@section('title')
Quotation {{ $quotation->quotation_number }} | {{ $company->name ?? '' }}
@endsection
@section('heading')
Quotation {{ $quotation->quotation_number }}
@endsection
@section('content')
@php
    $symbol = optional($quotation->currency)->symbol;
@endphp
<p style="margin:0 0 14px 0;">Dear {{ optional($quotation->customer)->name ?: 'Customer' }},</p>
<p style="margin:0 0 14px 0;">
    Thank you for your enquiry. Please find attached our quotation <strong>{{ $quotation->quotation_number }}</strong>@if($quotation->subheading) for {{ $quotation->subheading }}@endif.
</p>
@include('emails.partials.details', ['rows' => [
    'Quotation number' => $quotation->quotation_number,
    'Date' => $quotation->date ? \Carbon\Carbon::parse($quotation->date)->format('d M Y') : null,
    'Valid until' => $quotation->expiry ? \Carbon\Carbon::parse($quotation->expiry)->format('d M Y') : null,
    'Total' => $symbol . number_format((float) $quotation->total, 2),
]])
<p style="margin:0 0 14px 0;">The full quotation is attached as a PDF. Please contact us if you have any questions or would like to go ahead.</p>
<p style="margin:20px 0 0 0;">Kind regards,<br>{{ $company->name ?? '' }}</p>
@endsection
