@extends('layouts.emails')
@section('title')
Job Follow-up | {{ $company->name }}
@endsection
@section('heading')
Job Follow-up Reminder
@endsection
@section('content')
@php
    $shipment = $job->shipments->first();
    $status = $daysLeft < 0
        ? '<strong style="color:#c0392b;">Overdue by ' . abs($daysLeft) . ' day' . (abs($daysLeft) === 1 ? '' : 's') . '</strong>'
        : ($daysLeft === 0 ? '<strong style="color:#d35400;">Due today</strong>' : 'Due in ' . $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's'));
@endphp
<p style="margin:0 0 14px 0;">{!! $status !!}: <strong>{{ $reminder->title }}</strong></p>
@include('emails.partials.details', ['rows' => [
    'Job' => $job->job_number,
    'Customer' => $job->customer?->name,
    'Customer Ref' => $job->customer_reference,
    'Due' => $reminder->due_at->format('d M Y H:i'),
    'ETD' => $shipment?->etd?->format('d M Y'),
    'ETA' => $shipment?->eta?->format('d M Y'),
    'Notes' => $reminder->notes,
    'Set by' => $reminder->user ? trim($reminder->user->name . ' ' . $reminder->user->surname) : null,
    'Client emailed' => $reminder->notify_client ? 'Yes' : null,
]])
@include('emails.partials.button', ['url' => route('freight.jobs.show', $job->id) . '#reminders', 'label' => 'Open Job'])
<p style="margin:20px 0 0 0;font-size:12px;color:#777777;">
    Mark the reminder as done on the job's Reminders tab to stop further emails.
</p>
@endsection
