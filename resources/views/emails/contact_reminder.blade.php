@extends('layouts.emails')
@section('title')
Expiry Reminder | {{ $company->name ?? config('app.name') }}
@endsection
@section('heading')
Expiry Reminder
@endsection
@section('content')
@php
    $item = optional($fitness->reminder_item)->name ?: 'document';
    $expires = $fitness->expires_at;
    $daysLeft = $expires ? now()->startOfDay()->diffInDays($expires->copy()->startOfDay(), false) : null;
@endphp
<p style="margin:0 0 14px 0;">Dear {{ $contactName }},</p>
<p style="margin:0 0 14px 0;">
    This is a courtesy reminder from <strong>{{ $company->name ?? '' }}</strong> that the
    <strong>{{ $item }}</strong>@if($partyName) for <strong>{{ $partyName }}</strong>@endif
    @if($fitness->issued_at), issued on {{ \Carbon\Carbon::parse($fitness->issued_at)->format('d M Y') }},@endif
    expires on <strong style="color:#c0392b;">{{ $expires->format('d M Y') }}</strong>
    @if(!is_null($daysLeft) && $daysLeft >= 0)
        ({{ $daysLeft == 0 ? 'today' : ($daysLeft == 1 ? 'in 1 day' : "in {$daysLeft} days") }})
    @endif.
</p>
<p style="margin:0 0 14px 0;">Please arrange for it to be renewed and send us the updated copy before it expires, so that there is no disruption to our work together.</p>
<p style="margin:0 0 14px 0;">If you have already renewed it, please ignore this message or reply with the new copy.</p>
<p style="margin:20px 0 0 0;">
    Kind regards,<br>
    {{ $fitness->user ? trim($fitness->user->name . ' ' . $fitness->user->surname) : '' }}<br>
    {{ $company->name ?? '' }}
</p>
@endsection
