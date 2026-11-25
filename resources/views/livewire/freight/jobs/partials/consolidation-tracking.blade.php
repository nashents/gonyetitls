{{--
    Consolidation (LCL) tracking on a job's Containers / Timeline tab.
    House (client) job: read-only view of the master shipment's containers or
    milestones - the container is recorded and moved on the master job only.
    Master job: note listing the consolidation(s) it carries.
    Expects $job (shipments.consolidations.master_shipment.* and
    shipments.master_consolidation.house_shipments.freight_job.customer loaded)
    and $section = 'containers' | 'timeline'.
--}}
@foreach ($job->shipments as $shipment)
    @if ($shipment->master_consolidation)
        @php $mc = $shipment->master_consolidation; @endphp
        <div class="alert alert-info">
            <i class="fa fa-cubes"></i>
            <strong>{{ $shipment->shipment_number }}</strong> is the master shipment of consolidation
            <a href="{{ route('freight.consolidations.show', $mc->id) }}" target="_blank"><strong>{{ $mc->consolidation_number }}</strong></a>
            carrying {{ $mc->house_shipments->count() }} client shipment(s):
            {{ $mc->house_shipments->map(fn ($s) => ($s->freight_job?->job_number ?? $s->shipment_number) . ($s->freight_job?->customer ? ' (' . $s->freight_job->customer->name . ')' : ''))->implode(', ') ?: '—' }}.
            @if ($section === 'containers')
                Stages recorded here show on every client job.
            @endif
            Changing this job's status (Confirmed &rarr; Transport Arranged) moves approved client jobs that are at the same status; clients already moved on their own keep theirs.
        </div>
    @endif

    @foreach ($shipment->consolidations as $consolidation)
        @php
            $master = $consolidation->master_shipment;
            $masterJob = $master?->freight_job;
        @endphp
        @continue(!$master)
        <div class="panel panel-info mb-15">
            <div class="panel-heading">
                <i class="fa fa-cubes"></i>
                <strong>{{ $shipment->shipment_number }}</strong> is consolidated in
                <a href="{{ route('freight.consolidations.show', $consolidation->id) }}" target="_blank"><strong>{{ $consolidation->consolidation_number }}</strong></a>
                &mdash; master shipment {{ $master->shipment_number }}
                @if ($masterJob)
                    on job <a href="{{ route('freight.jobs.show', $masterJob->id) }}" target="_blank"><strong>{{ $masterJob->job_number }}</strong></a>
                @endif
                <small class="pull-right">Read only - update the container on the master job</small>
            </div>
            <div class="panel-body">
                @if ($section === 'containers')
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Container #</th>
                                <th>Type</th>
                                <th>Seal #</th>
                                <th>Shipping Line</th>
                                <th>Current Stage</th>
                                <th>Last Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($master->containers as $container)
                                @php
                                    $last = $master->milestones
                                        ->where('shipping_container_id', $container->id)
                                        ->sortByDesc(fn ($m) => $m->actual_at ?? $m->planned_at ?? $m->created_at)
                                        ->first();
                                @endphp
                                <tr wire:key="consol-container-{{ $shipment->id }}-{{ $container->id }}">
                                    <td>{{ $container->container_number }}</td>
                                    <td>{{ $container->container_type }}</td>
                                    <td>{{ $container->seal_number }}</td>
                                    <td>{{ $container->shipping_line?->name ?? $container->shipping_line_name }}</td>
                                    <td><span class="label label-info label-wide">{{ \App\Models\ContainerStage::label($container->status) ?? '—' }}</span></td>
                                    <td>{{ $last ? $last->milestone_name . ' - ' . optional($last->actual_at ?? $last->planned_at)->format('d M Y H:i') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No container recorded on the master shipment yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Container</th>
                                <th>Milestone</th>
                                <th>Status</th>
                                <th>Planned</th>
                                <th>Actual</th>
                                <th>Customer Visible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($master->milestones->sortByDesc(fn ($m) => $m->actual_at ?? $m->planned_at ?? $m->estimated_at ?? $m->created_at) as $milestone)
                                <tr wire:key="consol-milestone-{{ $shipment->id }}-{{ $milestone->id }}">
                                    <td>{{ $milestone->shipping_container?->container_number }}</td>
                                    <td>{{ $milestone->milestone_name }}</td>
                                    <td>
                                        @if ($milestone->status == 'completed')
                                            <span class="label label-success label-wide">Completed</span>
                                        @else
                                            <span class="label label-warning label-wide">{{ ucfirst($milestone->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $milestone->planned_at?->format('d M Y H:i') }}</td>
                                    <td>{{ $milestone->actual_at?->format('d M Y H:i') }}</td>
                                    <td><i class="fa {{ $milestone->is_customer_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No milestones on the master shipment yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endforeach
@endforeach
