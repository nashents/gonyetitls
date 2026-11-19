@extends('layouts.emails')
@section('title')
{{ $headline }} | {{ $company->name ?? config('app.name') }}
@endsection
@section('heading')
{{ $headline }}
@endsection
@section('content')
@php
    $m = $movement;
    $doc = $m->document;
    $rows = [
        'Document' => $doc->title,
        'From' => $m->fromLabel(),
        'To' => $m->toLabel(),
        'Handed over' => optional($m->handed_over_at)->format('d M Y H:i'),
        'Location' => $m->location,
        'Purpose' => $m->purpose,
        'Condition at hand-over' => $m->handover_condition,
        'Hand-over notes' => $m->handover_notes,
    ];
    if (in_array($event, ['received', 'rejected'])) {
        $rows[$event === 'received' ? 'Received' : 'Rejected'] = optional($m->received_at)->format('d M Y H:i');
        if ($event === 'received') { $rows['Condition on receipt'] = $m->receipt_condition; }
        $rows[$event === 'received' ? 'Receipt notes' : 'Reason'] = $m->receipt_notes;
    }
@endphp
<p style="margin:0 0 14px 0;">Hi {{ $recipientName }},</p>
<p style="margin:0 0 14px 0;">
    @if ($event === 'handed_over')
        <strong>{{ $m->fromLabel() }}</strong> has handed <strong>{{ $doc->title }}</strong> over to you. Please confirm you have received it, and in what condition, by signing for it in the system.
    @elseif ($event === 'received')
        <strong>{{ $m->toLabel() }}</strong> has confirmed receipt of <strong>{{ $doc->title }}</strong>.
    @elseif ($event === 'rejected')
        <strong>{{ $m->toLabel() }}</strong> has <strong style="color:#c0392b;">rejected</strong> the hand-over of <strong>{{ $doc->title }}</strong>. The document is still recorded as being with {{ $m->fromLabel() }}.
    @elseif ($event === 'cancelled')
        The hand-over of <strong>{{ $doc->title }}</strong> from {{ $m->fromLabel() }} to you has been cancelled. You do not need to sign for it.
    @endif
</p>
@include('emails.partials.details', ['rows' => $rows])
@include('emails.partials.button', ['url' => $actionUrl, 'label' => $actionLabel])
<p style="margin:10px 0 0 0;font-size:12px;color:#777777;">You will be asked to log in first if you are not signed in.</p>
@endsection
