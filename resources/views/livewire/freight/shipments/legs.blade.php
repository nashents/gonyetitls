<div>
    <div class="mb-10">
        <a href="#" wire:click.prevent="$set('editing_leg_id', null)" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addLegModal{{ $shipment->id }}">
            <i class="fa fa-plus"></i> Add Leg
        </a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th><th>Mode</th><th>Carrier</th><th>Origin → Destination</th><th>Planned Dep / Arr</th><th>Status</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($shipment->legs as $leg)
                <tr>
                    <td>{{ $leg->sequence }}</td>
                    <td>{{ ucfirst($leg->transport_mode ?? '') }}</td>
                    <td>{{ $leg->carrier_vendor?->name ?? $leg->carrier_name ?? '—' }}</td>
                    <td>{{ $leg->origin_location?->name }} &rarr; {{ $leg->destination_location?->name }}</td>
                    <td>{{ $leg->planned_departure?->format('d M Y') }} / {{ $leg->planned_arrival?->format('d M Y') }}</td>
                    <td><span class="label label-info label-wide">{{ $lifecycleStages[$leg->status] ?? ucwords(str_replace('_', ' ', $leg->status)) }}</span></td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpand({{ $leg->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> Details</a>
                        <a href="#" wire:click.prevent="edit({{ $leg->id }})" class="btn btn-xs btn-default" data-toggle="modal" data-target="#addLegModal{{ $shipment->id }}"><i class="fa fa-edit"></i></a>

                        @if ($leg->trip_id)
                            <a href="{{ route('trips.show', $leg->trip_id) }}" target="_blank" class="btn btn-xs btn-primary">
                                <i class="fa fa-truck"></i> Trip {{ $leg->trip->trip_number }} — {{ $leg->trip->trip_status }}
                            </a>
                        @else
                            @if ($leg->nextLifecycleStage())
                                <a href="#" wire:click.prevent="advanceStage({{ $leg->id }})" wire:confirm="Mark this leg as '{{ $lifecycleStages[$leg->nextLifecycleStage()] }}'?" class="btn btn-xs btn-success">
                                    <i class="fa fa-arrow-right"></i> {{ $lifecycleStages[$leg->nextLifecycleStage()] }}
                                </a>
                            @endif
                            <a href="#" wire:click.prevent="openDispatch({{ $leg->id }})" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#dispatchModal{{ $shipment->id }}">
                                <i class="fa fa-truck"></i> Dispatch via Own Fleet
                            </a>
                            <a href="#" wire:click.prevent="delete({{ $leg->id }})" wire:confirm="Remove this leg?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                        @endif
                    </td>
                </tr>
                @if ($expanded_leg_id == $leg->id)
                    <tr>
                        <td colspan="7" style="background-color:#f9f9f9;">
                            @if ($leg->carrier_reference)
                                <strong>Carrier Reference:</strong> {{ $leg->carrier_reference }}<br>
                            @endif
                            <strong>Estimated:</strong> {{ $leg->estimated_departure?->format('d M Y H:i') }} / {{ $leg->estimated_arrival?->format('d M Y H:i') }}
                            &mdash; <strong>Actual:</strong> {{ $leg->actual_departure?->format('d M Y H:i') ?? '—' }} / {{ $leg->actual_arrival?->format('d M Y H:i') ?? '—' }}

                            @unless ($leg->trip_id)
                                <div class="mt-10">
                                    <a href="#" wire:click.prevent="hold({{ $leg->id }})" class="btn btn-xs btn-default">Put On Hold</a>
                                    <a href="#" wire:click.prevent="cancel({{ $leg->id }})" wire:confirm="Cancel this leg?" class="btn btn-xs btn-default">Cancel</a>
                                </div>
                            @endunless

                            <h6 class="underline mt-20 mb-10"><strong>Milestone History</strong></h6>
                            <table class="table table-condensed">
                                <thead>
                                    <tr><th>Stage</th><th>Status</th><th>Actual</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($leg->milestones as $milestone)
                                        <tr>
                                            <td>{{ $milestone->milestone_name }}</td>
                                            <td>{{ ucfirst($milestone->status) }}</td>
                                            <td>{{ $milestone->actual_at?->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center">No milestones recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @unless ($leg->trip_id)
                                <div class="row mb-20">
                                    <div class="col-md-4">
                                        <select class="form-control" wire:model="ad_hoc_milestone_code">
                                            <option value="">Record ad-hoc milestone...</option>
                                            @foreach ($lifecycleStages as $code => $label)
                                                <option value="{{ $code }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="#" wire:click.prevent="recordAdHocMilestone({{ $leg->id }})" class="btn btn-xs btn-warning">Record</a>
                                    </div>
                                </div>
                            @endunless

                            <h6 class="underline mt-20 mb-10"><strong>Documents</strong></h6>
                            @livewire('documents.index', ['id' => $leg->id, 'category' => 'shipment_leg'], key('leg-docs-'.$leg->id))
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="text-center">No legs recorded for this shipment yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Add/Edit Leg Modal -->
    <div class="modal fade" id="addLegModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ $editing_leg_id ? 'Edit' : 'Add' }} Leg</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Transport Mode</label>
                                    <select class="form-control" wire:model="transport_mode">
                                        <option value="">Select</option>
                                        <option value="sea">Sea</option>
                                        <option value="air">Air</option>
                                        <option value="road">Road</option>
                                        <option value="rail">Rail</option>
                                        <option value="courier">Courier</option>
                                        <option value="multimodal">Multimodal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Carrier Reference</label>
                                    <input type="text" class="form-control" wire:model="carrier_reference">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Carrier (Vendor)</label>
                                    <select class="form-control" wire:model="carrier_vendor_id">
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control mt-10" wire:model="carrier_name" placeholder="or type free text">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="color-gray">Leave carrier blank and use "Dispatch via Own Fleet" from the leg row instead if this is executed by our own trucks.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Origin</label>
                                    <select class="form-control" wire:model="origin_location_id">
                                        <option value="">Select</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Destination</label>
                                    <select class="form-control" wire:model="destination_location_id">
                                        <option value="">Select</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Planned Departure</label>
                                    <input type="datetime-local" class="form-control" wire:model="planned_departure">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Planned Arrival</label>
                                    <input type="datetime-local" class="form-control" wire:model="planned_arrival">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estimated Departure</label>
                                    <input type="datetime-local" class="form-control" wire:model="estimated_departure">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estimated Arrival</label>
                                    <input type="datetime-local" class="form-control" wire:model="estimated_arrival">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Leg</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dispatch via Own Fleet Modal -->
    <div class="modal fade" id="dispatchModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="dispatch">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Dispatch via Own Fleet</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">This creates a real Trip for our own fleet to execute this leg. Freight revenue/cost stays on the job's Costing tab &mdash; this trip carries no freight/rate figures of its own.</p>
                        <div class="form-group">
                            <label>Transporter</label>
                            <select class="form-control" wire:model="dispatch_transporter_id">
                                <option value="">Select</option>
                                @foreach ($transporters as $transporter)
                                    <option value="{{ $transporter->id }}">{{ $transporter->name }}{{ $transporter->default ? ' (Own Fleet)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('dispatch_transporter_id') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Horse</label>
                                    <select class="form-control" wire:model="dispatch_horse_id">
                                        <option value="">None</option>
                                        @foreach ($dispatch_horses as $horse)
                                            <option value="{{ $horse->id }}">{{ $horse->registration_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vehicle</label>
                                    <select class="form-control" wire:model="dispatch_vehicle_id">
                                        <option value="">None</option>
                                        @foreach ($dispatch_vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Driver <span class="required text-danger">*</span></label>
                            <select class="form-control" wire:model="dispatch_driver_id">
                                <option value="">Select</option>
                                @foreach ($dispatch_drivers as $driver)
                                    @if ($driver->employee)
                                        <option value="{{ $driver->id }}">{{ $driver->employee->name }} {{ $driver->employee->surname }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('dispatch_driver_id') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From <small class="color-gray">(leg origin: {{ $shipment->legs->firstWhere('id', $dispatching_leg_id)?->origin_location?->name ?? '—' }})</small></label>
                                    <input type="text" class="form-control" wire:model="dispatch_from">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>To <small class="color-gray">(leg destination: {{ $shipment->legs->firstWhere('id', $dispatching_leg_id)?->destination_location?->name ?? '—' }})</small></label>
                                    <input type="text" class="form-control" wire:model="dispatch_to">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Initial Status</label>
                                    <select class="form-control" wire:model="dispatch_status">
                                        @foreach ($tripStatuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" wire:model="dispatch_start_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-truck"></i> Dispatch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addLegModal-{{ $shipment->id }}', event => { $('#addLegModal{{ $shipment->id }}').modal('hide'); })
        window.addEventListener('show-addLegModal-{{ $shipment->id }}', event => { $('#addLegModal{{ $shipment->id }}').modal('show'); })
        window.addEventListener('hide-dispatchModal-{{ $shipment->id }}', event => { $('#dispatchModal{{ $shipment->id }}').modal('hide'); })
        window.addEventListener('show-dispatchModal-{{ $shipment->id }}', event => { $('#dispatchModal{{ $shipment->id }}').modal('show'); })
    </script>
</div>
