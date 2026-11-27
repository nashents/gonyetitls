@extends('layouts.emails')
@section('title')
Reminder | {{ $company->name }}
@endsection
@section('heading')
Shipment Reminder
@endsection
@section('content')
@php
    $shipment = $job->shipments->first();
@endphp
<p style="margin:0 0 14px 0;">Dear {{ $contactName ?: 'Sir/Madam' }},</p>
<p style="margin:0 0 14px 0;">
    This is a friendly reminder from <strong>{{ $company->name }}</strong> regarding your shipment
    <strong>{{ $job->job_number }}</strong>@if($job->customer_reference) (your reference {{ $job->customer_reference }})@endif:
</p>
<p style="margin:0 0 14px 0;padding:10px 14px;background:#f5f7fa;border-left:3px solid {{ $company->color ?: '#123bdc' }};">
    <strong>{{ $reminder->title }}</strong>
    @if($reminder->client_message)
        <br>{!! nl2br(e($reminder->client_message)) !!}
    @endif
</p>
@include('emails.partials.details', ['rows' => [
    'Required by' => $reminder->due_at->format('d M Y'),
    'ETD' => $shipment?->etd?->format('d M Y'),
    'ETA' => $shipment?->eta?->format('d M Y'),
]])
<p style="margin:0 0 14px 0;">If this has already been taken care of, please ignore this message or reply to let us know.</p>
<p style="margin:20px 0 0 0;">
    Kind regards,<br>
    {{ $reminder->user ? trim($reminder->user->name . ' ' . $reminder->user->surname) : '' }}<br>
    {{ $company->name }}
</p>
@endsection
