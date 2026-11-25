<div>
    <a href="{{ route('customer.dashboard') }}" class="btn btn-xs btn-default mb-20"><i class="fa fa-arrow-left"></i> Back to My Jobs</a>

    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title"><h5>Job {{ $job->job_number }}</h5></div>
        </div>
        <div class="panel-body">
            <table class="table table-condensed">
                <tbody>
                    <tr><th>Your Reference</th><td>{{ $job->customer_reference }}</td></tr>
                    <tr><th>Status</th><td><span class="label label-info label-wide">{{ ucwords(str_replace('_', ' ', $job->status)) }}</span></td></tr>
                    <tr><th>Mode</th><td>{{ ucfirst($job->primary_transport_mode ?? '') }}</td></tr>
                    <tr><th>Route</th><td>{{ $job->origin }} &rarr; {{ $job->destination }}</td></tr>
                    <tr><th>Opened</th><td>{{ optional($job->opened_at)->format('d M Y') }}</td></tr>
                    @if ($job->completed_at)
                        <tr><th>Delivered</th><td>{{ optional($job->completed_at)->format('d M Y') }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($job->shipments as $shipment)
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title"><h5>Shipment {{ $shipment->shipment_number }}</h5></div>
            </div>
            <div class="panel-body">
                <table class="table table-condensed">
                    <tbody>
                        <tr><th>Mode</th><td>{{ ucfirst($shipment->mode ?? '') }}</td></tr>
                        <tr><th>Status</th><td>{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</td></tr>
                        <tr><th>ETD</th><td>{{ optional($shipment->etd)->format('d M Y') ?? '—' }}</td></tr>
                        <tr><th>ETA</th><td>{{ optional($shipment->eta)->format('d M Y') ?? '—' }}</td></tr>
                    </tbody>
                </table>

                @php
                    $masters = $shipment->consolidations->pluck('master_shipment')->filter();
                    $timeline = $shipment->milestones
                        ->concat($masters->flatMap->milestones)
                        ->sortByDesc(fn ($m) => $m->actual_at ?? $m->planned_at ?? $m->estimated_at ?? $m->created_at);
                @endphp

                @if ($masters->flatMap->containers->count())
                    <h6 class="underline mt-10 mb-10"><strong>Container</strong></h6>
                    <table class="table table-condensed table-bordered">
                        <thead>
                            <tr><th>Container #</th><th>Type</th><th>Current Stage</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($masters->flatMap->containers as $container)
                                <tr>
                                    <td>{{ $container->container_number }}</td>
                                    <td>{{ $container->container_type }}</td>
                                    <td>{{ \App\Models\ContainerStage::label($container->status) ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h6 class="underline mt-10 mb-10"><strong>Timeline</strong></h6>
                @if ($timeline->count())
                    <table class="table table-condensed table-bordered">
                        <thead>
                            <tr><th>Event</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($timeline as $milestone)
                                <tr>
                                    <td>{{ $milestone->milestone_name }}</td>
                                    <td>{{ ucfirst($milestone->status) }}</td>
                                    <td>{{ optional($milestone->actual_at ?? $milestone->planned_at)->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">No updates recorded yet.</p>
                @endif
            </div>
        </div>
    @endforeach

    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title"><h5>Documents</h5></div>
        </div>
        <div class="panel-body">
            @if ($documents->count())
                <table class="table table-condensed table-bordered">
                    <thead>
                        <tr><th>Title</th><th>Uploaded</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>{{ $document->title ?: $document->filename }}</td>
                                <td>{{ $document->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No documents shared yet.</p>
            @endif
        </div>
    </div>
</div>
