<div>
    <style id="trip-notes-modal-style">
        .trip-notes-timeline { max-height: 360px; overflow-y: auto; padding: 10px 5px; }
        .trip-notes-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .trip-notes-item:last-child { border-bottom: none; }
        .trip-notes-date-divider { text-align: center; color: #6c757d; font-size: 12px; margin: 12px 0 6px; text-transform: uppercase; }
        .trip-notes-item .badge-task { background: #337ab7; color: #fff; margin-right: 8px; }
        .trip-notes-item .author { font-weight: 700; text-decoration: underline; }
        .trip-notes-item .time { color: #6c757d; font-size: 12px; float: right; }
        .trip-notes-item .location { color: #6c757d; font-size: 12px; margin-top: 4px; }
        .trip-notes-context-bar { background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 12px; font-size: 12px; margin-bottom: 10px; }
        .trip-notes-context-bar div { margin-bottom: 2px; }
        .trip-notes-context-bar strong { color: #2f3640; font-weight: 600; }
        .trip-notes-templates .btn { margin-right: 6px; margin-bottom: 6px; }
        .trip-notes-templates .btn.active { box-shadow: inset 0 0 0 2px #1c4e91; }
        .trip-notes-fields-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .trip-notes-fields-row > div { flex: 1 1 140px; min-width: 120px; }
        .trip-notes-fields-row label { margin-bottom: 2px; }
    </style>

    <div wire:ignore.self class="modal" id="tripNotesModal" tabindex="-1" role="dialog">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-comments"></i>
                        Trip Notes @if($trip) &mdash; {{ $trip->trip_number }} @endif
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="trip-notes-timeline">
                        @forelse ($notes as $note)
                            @php
                                $prevItem = $loop->index > 0 ? $notes[$loop->index - 1] : null;
                                $prevDate = $prevItem?->created_at?->format('Y-m-d');
                            @endphp
                            @if ($loop->first || $note->created_at->format('Y-m-d') !== $prevDate)
                                <div class="trip-notes-date-divider">{{ $note->created_at->format('d M Y') }}</div>
                            @endif
                            <div class="trip-notes-item">
                                @if ($note->task_type)
                                    <span class="badge badge-task">{{ \App\Models\TripNote::TASK_LABELS[$note->task_type] ?? $note->task_type }}</span>
                                @endif
                                <span class="author">{{ optional($note->user)->name }} {{ optional($note->user)->surname }}</span>
                                <span class="time">{{ $note->created_at->format('H:i') }}</span>
                                <div class="mt-1">{{ $note->body }}</div>
                                @if ($note->location_description)
                                    <div class="location"><i class="fas fa-map-marker-alt"></i> {{ $note->location_description }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">No notes yet on this trip.</p>
                        @endforelse
                    </div>

                </div>

                <div class="modal-footer d-block">

                    @if ($trip)
                        @php
                            $asset = $trip->horse ?? $trip->vehicle;
                            $tripSummary = collect([
                                $trip->customer?->name,
                                $trip->cargo?->name,
                                collect([$trip->loading_point?->name, $trip->offloading_point?->name])->filter()->implode(' - '),
                                $trip->container_number,
                                $trip->haulage_type,
                            ])->filter()->implode(' / ');
                            $refNumbers = $trip->transport_orders->pluck('transport_order_number')->filter()->implode(' / ') ?: $trip->trip_ref;
                        @endphp
                        <div class="trip-notes-context-bar">
                            <div>Vehicle: <strong>{{ $asset->registration_number ?? '—' }}</strong></div>
                            <div>Fleet: <strong>{{ $asset->fleet_number ?? '—' }}</strong></div>
                            <div>Trip: <strong>{{ $tripSummary ?: '—' }}</strong></div>
                            <div>Ref: <strong>{{ $refNumbers ?: '—' }}</strong></div>
                            <div>
                                Location:
                                <strong>
                                    @if ($positionSnapshot)
                                        <i class="fas fa-satellite-dish text-muted"></i>
                                        {{ $positionSnapshot['address'] ?: number_format($positionSnapshot['lat'], 4) . ', ' . number_format($positionSnapshot['lng'], 4) }}
                                    @else
                                        &mdash;
                                    @endif
                                </strong>
                            </div>
                            <div>
                                Date:
                                <strong>
                                    @if ($positionSnapshot && $positionSnapshot['observed_at'])
                                        {{ \Carbon\Carbon::parse($positionSnapshot['observed_at'])->format('j M Y H:i:s') }}
                                        - {{ \Carbon\Carbon::parse($positionSnapshot['observed_at'])->diffForHumans() }}
                                    @else
                                        &mdash;
                                    @endif
                                </strong>
                            </div>
                        </div>
                    @endif

                    <div class="form-group mb-2">
                        <textarea wire:model="body" class="form-control" rows="3" placeholder="Add a comment..."></textarea>
                        @error('body') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    @if ($taskType)
                        <div class="trip-notes-fields-row mb-2">
                            <div>
                                <label class="small text-muted mb-1">Area</label>
                                <select wire:model="area" class="form-control form-control-sm">
                                    <option value="">Select area...</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->name }}">{{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if (in_array($taskType, ['to_leave','to_arrive','to_load','to_offload']))
                                <div>
                                    <label class="small text-muted mb-1">By</label>
                                    <input type="time" wire:model="byTime" class="form-control form-control-sm">
                                </div>
                            @endif
                            @if (in_array($taskType, ['to_leave','to_arrive','notify_on_arrival']))
                                <div>
                                    <label class="small text-muted mb-1">Notify</label>
                                    <select wire:model="notify" class="form-control form-control-sm">
                                        <option value="">Select employee...</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ trim($employee->name . ' ' . $employee->surname) }}">{{ trim($employee->name . ' ' . $employee->surname) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="trip-notes-templates mb-2">
                        <button type="button" class="btn btn-sm btn-default {{ !$taskType ? 'active' : '' }}" wire:click="selectTemplate(null)">Comment</button>
                        @foreach (\App\Models\TripNote::TASK_LABELS as $type => $label)
                            <button type="button" class="btn btn-sm btn-info {{ $taskType === $type ? 'active' : '' }}" wire:click="selectTemplate('{{ $type }}')">{{ $label }}</button>
                        @endforeach
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-primary" wire:click="store" wire:loading.attr="disabled">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        function scrollTripNotesToBottom() {
            const el = document.querySelector('.trip-notes-timeline');
            if (el) { el.scrollTop = el.scrollHeight; }
        }

        window.addEventListener('show-tripNotesModal', () => {
            $('#tripNotesModal').modal({ backdrop: 'static', keyboard: false });
            $('#tripNotesModal').modal('show');
            setTimeout(scrollTripNotesToBottom, 50);
        });

        // Keeps the newest comment (now at the bottom) in view after posting one.
        document.addEventListener('livewire:update', () => setTimeout(scrollTripNotesToBottom, 50));
    </script>
</div>
