<div>
    <style>
        .trip-updates-timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 10px;
        }
        .trip-updates-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: #e3e6ea;
        }
        .trip-updates-timeline .timeline-item {
            position: relative;
            padding-bottom: 22px;
        }
        .trip-updates-timeline .timeline-item:last-child {
            padding-bottom: 0;
        }
        .trip-updates-timeline .timeline-dot {
            position: absolute;
            left: -30px;
            top: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #6c757d;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e3e6ea;
        }
        .trip-updates-timeline .timeline-item.is-status .timeline-dot {
            background: #28a745;
        }
        .trip-updates-timeline .timeline-item.is-custom .timeline-dot {
            background: #f0ad4e;
        }
        .trip-updates-timeline .timeline-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px 14px;
        }
    </style>

    @if ($trip_status_updates->isEmpty())
        <p class="text-muted mb-0">No trip updates recorded yet.</p>
    @else
        <div class="trip-updates-timeline">
            @foreach ($trip_status_updates as $update)
                <div class="timeline-item {{ $update->is_custom_update ? 'is-custom' : 'is-status' }}">
                    <span class="timeline-dot"></span>
                    <div class="timeline-card">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div>
                                @if ($update->is_custom_update)
                                    <span class="badge" style="background:#f0ad4e;color:#fff;">Custom Update</span>
                                @else
                                    <span class="badge" style="background:#28a745;color:#fff;">Status Change</span>
                                @endif
                                <strong class="ml-1">{{ $update->status }}</strong>
                            </div>
                            <small class="text-muted">
                                {{ $update->date ? \Carbon\Carbon::parse($update->date)->format('d M Y H:i') : $update->created_at->format('d M Y H:i') }}
                            </small>
                        </div>
                        @if ($update->description)
                            <p class="mb-1 mt-2">{{ $update->description }}</p>
                        @endif
                        <small class="text-muted">
                            Logged by {{ $update->user?->name ?? 'Unknown User' }}
                        </small>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
