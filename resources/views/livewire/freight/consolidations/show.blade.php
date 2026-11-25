<div>
    @php
        $masterJob = $breakdown['master_job'];
        $ccy = $masterJob?->currency?->symbol;
    @endphp
    <div class="row mt-30">
        <div class="col-md-12">
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>
                            {{ $consolidation->consolidation_number }}
                            <span class="pull-right">
                                <a href="{{ route('freight.consolidations.add_clients', $consolidation->id) }}" class="btn btn-xs btn-success"><i class="fa fa-users"></i> Add Clients</a>
                                <a href="{{ route('freight.consolidations.manifest', $consolidation->id) }}" target="_blank" class="btn btn-xs btn-primary"><i class="fa fa-print"></i> LCL Manifest</a>
                                <a href="{{ route('freight.consolidations.manifest.excel', $consolidation->id) }}" class="btn btn-xs btn-default"><i class="fa fa-file-excel-o"></i> Excel</a>
                            </span>
                        </h5>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th class="w-20">Master Shipment</th>
                                <td>
                                    {{ $consolidation->master_shipment?->shipment_number }}
                                    @if ($masterJob)
                                        (<a href="{{ route('freight.jobs.show', $masterJob->id) }}" target="_blank">{{ $masterJob->job_number }}</a> &mdash; {{ $masterJob->customer_label }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Container(s)</th>
                                <td>
                                    @forelse ($consolidation->master_shipment?->containers ?? [] as $container)
                                        <span class="label label-default">{{ $container->container_number }} {{ $container->container_type }}</span>
                                        <small>{{ \App\Models\ContainerStage::label($container->status) }}</small>&nbsp;
                                    @empty
                                        <span class="text-muted">No container on the master shipment yet - add it on the master job's Containers tab.</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th>Master Transport Document</th>
                                <td>{{ $consolidation->master_transport_document?->document_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="label label-info label-wide">{{ ucfirst($consolidation->status) }}</span></td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td>{{ $consolidation->notes }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6 class="underline mt-20 mb-10"><strong>Master Cost Split</strong></h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Allocation Basis</label>
                                <select class="form-control" wire:model="cost_allocation_basis">
                                    @foreach ($bases as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-bordered table-condensed mb-0">
                                <tr>
                                    <th>Master Cost Pool</th>
                                    <th>Allocated to Clients</th>
                                    <th>Left on Master Job</th>
                                </tr>
                                <tr>
                                    <td>{{ $ccy }}{{ number_format($breakdown['pool'], 2) }}</td>
                                    <td>{{ $ccy }}{{ number_format($breakdown['allocated'], 2) }}</td>
                                    <td>{{ $ccy }}{{ number_format($breakdown['unallocated'], 2) }}</td>
                                </tr>
                            </table>
                            <small class="text-muted">Pool = the master job's Verified / Approved / Posted cost lines. A management split for job profit only - supplier bills stay on the master job.</small>
                        </div>
                    </div>

                    @foreach ($breakdown['warnings'] as $warning)
                        <div class="alert alert-warning mt-10 mb-0">{{ $warning }}</div>
                    @endforeach

                    <h6 class="underline mt-20 mb-10"><strong>House Shipments (Clients)</strong></h6>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Shipment #</th>
                                <th>Job #</th>
                                <th>Client</th>
                                <th>Commodity</th>
                                <th class="text-right">CBM</th>
                                <th class="text-right">Gross Wt</th>
                                @if ($usesValue)
                                    <th>{{ $bases[$cost_allocation_basis] }} <small>({{ $valueHint }})</small></th>
                                @endif
                                <th class="text-right">Share %</th>
                                <th class="text-right">Allocated Cost</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($consolidation->house_shipments as $houseShipment)
                                @php $row = $breakdown['rows'][$houseShipment->id] ?? null; @endphp
                                <tr wire:key="house-{{ $houseShipment->id }}" class="{{ $row && $row['excluded'] ? 'warning' : '' }}">
                                    <td>{{ $houseShipment->shipment_number }}</td>
                                    <td>
                                        @if ($houseShipment->freight_job)
                                            <a href="{{ route('freight.jobs.show', $houseShipment->freight_job->id) }}" target="_blank">{{ $houseShipment->freight_job->job_number }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $houseShipment->freight_job?->customer_label }}</td>
                                    <td>{{ $houseShipment->cargo_items->map(fn ($c) => $c->commodity ?: ($c->cargo?->name ?: $c->description))->filter()->unique()->implode(', ') }}</td>
                                    <td class="text-right">{{ number_format($houseShipment->cargo_items->sum(fn ($c) => (float) $c->cbm), 3) }}</td>
                                    <td class="text-right">{{ number_format($houseShipment->cargo_items->sum(fn ($c) => (float) $c->gross_weight), 2) }}</td>
                                    @if ($usesValue)
                                        <td style="min-width: 160px">
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="any" min="0" class="form-control" wire:model.defer="allocation_values.{{ $houseShipment->id }}">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default" wire:click="saveAllocationValue({{ $houseShipment->id }})" title="Save"><i class="fa fa-check"></i></button>
                                                </span>
                                            </div>
                                            @error('allocation_values.' . $houseShipment->id) <span class="text-danger error">{{ $message }}</span> @enderror
                                        </td>
                                    @endif
                                    <td class="text-right">{{ $row && !$row['excluded'] ? rtrim(rtrim(number_format($row['percent'], 2), '0'), '.') . '%' : '—' }}</td>
                                    <td class="text-right">
                                        @if ($row && $row['excluded'])
                                            <span title="{{ $row['excluded'] }}">Not allocated <i class="fa fa-info-circle"></i></span>
                                        @else
                                            {{ $ccy }}{{ number_format($row['amount'] ?? 0, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#" wire:click.prevent="detachShipment({{ $houseShipment->id }})" wire:confirm="Remove this house shipment from the consolidation?" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Detach</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $usesValue ? 10 : 9 }}" class="text-center">No house shipments attached yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($consolidation->house_shipments->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <th colspan="4">Total</th>
                                    <th class="text-right">{{ number_format($consolidation->house_shipments->flatMap->cargo_items->sum(fn ($c) => (float) $c->cbm), 3) }}</th>
                                    <th class="text-right">{{ number_format($consolidation->house_shipments->flatMap->cargo_items->sum(fn ($c) => (float) $c->gross_weight), 2) }}</th>
                                    @if ($usesValue)<th></th>@endif
                                    <th></th>
                                    <th class="text-right">{{ $ccy }}{{ number_format($breakdown['allocated'], 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>

                    <form wire:submit.prevent="attachShipment" class="row mb-20">
                        <div class="col-md-6">
                            <select class="form-control" wire:model.defer="house_shipment_id">
                                <option value="">Select Client Shipment to Attach</option>
                                @foreach ($availableShipments as $shipment)
                                    <option value="{{ $shipment->id }}">
                                        {{ $shipment->shipment_number }} &mdash; {{ $shipment->freight_job?->job_number }} &mdash; {{ $shipment->freight_job?->customer_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('house_shipment_id') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        @if ($usesValue)
                            <div class="col-md-3">
                                <input type="number" step="any" min="0" class="form-control" wire:model.defer="allocation_value" placeholder="{{ $valueHint }}">
                                @error('allocation_value') <span class="text-danger error">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info"><i class="fa fa-plus"></i> Attach</button>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group pull-right mt-10">
                                <a href="{{ route('freight.consolidations.index') }}" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i> Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
