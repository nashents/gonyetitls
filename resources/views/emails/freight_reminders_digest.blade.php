@extends('layouts.emails')
@section('title')
Freight Reminders | {{$company->name}}
@endsection
@section('heading')
Freight Reminders
@endsection
@section('content')
@php
    $groups = [
        'overdue' => ['label' => 'Overdue', 'color' => '#c0392b'],
        'today' => ['label' => 'Due today', 'color' => '#d35400'],
        'upcoming' => ['label' => 'Coming up', 'color' => '#2471a3'],
    ];
    $bySeverity = collect($reminders)->groupBy('severity');
@endphp
<p style="margin:0 0 18px 0;">These freight operations need attention as of {{ now()->format('d M Y') }}.</p>

@foreach ($groups as $severity => $meta)
    @if ($bySeverity->has($severity))
        <p style="margin:16px 0 6px 0;font-weight:bold;color:{{ $meta['color'] }};">
            {{ $meta['label'] }} ({{ $bySeverity[$severity]->count() }})
        </p>
        <table role="presentation" style="width:100%;border-collapse:collapse;font-size:13px;">
            @foreach ($bySeverity[$severity] as $reminder)
                <tr>
                    <td style="padding:6px 8px;border-bottom:1px solid #eef0f3;white-space:nowrap;vertical-align:top;">
                        <a href="{{ $reminder['url'] }}" target="_blank" style="color:#1a56db;">{{ $reminder['job_number'] }}</a>
                    </td>
                    <td style="padding:6px 8px;border-bottom:1px solid #eef0f3;">{{ $reminder['message'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
@endforeach

<p style="margin:24px 0 0 0;font-size:12px;color:#777777;">
    You get this email because you created or are an officer on these freight jobs, or are set up to receive Freight Reminders. It is sent at most once a day.
</p>
@endsection
