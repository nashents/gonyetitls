<div>
    <div class="mb-10">
        <a href="#" wire:click.prevent="newLeg" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addLegModal{{ $shipment->id }}">
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
            @php $linkOn = $this->linkEnabled; @endphp
            @forelse ($legRows as $leg)
                <tr wire:key="{{ '56c716-15-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($leg->id ?? '') }}">
                    <td>{{ $leg->sequence }}</td>
                    <td>{{ ucfirst($leg->transport_mode ?? '') }}</td>
                    <td>{{ $leg->carrierLabel() ?? '—' }}</td>
                    <td>{{ $leg->origin?->city ?? '—' }} &rarr; {{ $leg->destination?->city ?? '—' }}</td>
                    <td>{{ $leg->planned_departure?->format('d M Y') }} / {{ $leg->planned_arrival?->format('d M Y') }}</td>
                    <td><span class="label label-info label-wide">{{ $lifecycleStages[$leg->status] ?? ucwords(str_replace('_', ' ', $leg->status)) }}</span></td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpand({{ $leg->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> Details</a>
                        <a href="#" wire:click.prevent="edit({{ $leg->id }})" class="btn btn-xs btn-default" data-toggle="modal" data-target="#addLegModal{{ $shipment->id }}"><i class="fa fa-edit"></i></a>

                        @php $linkedRoad = $linkOn && \App\Services\GonyetiLink\GonyetiLinkService::isRoadLeg($leg); @endphp
                        @if ($leg->trip_id)
                            <a href="{{ route('trips.show', $leg->trip_id) }}" target="_blank" class="btn btn-xs btn-primary">
                                <i class="fa fa-truck"></i> Trip {{ $leg->trip->trip_number }} — {{ $leg->trip->trip_status }}
                            </a>
                        @elseif ($linkedRoad || $leg->link_status)
                            {{-- Executed by the client's transport instance (Gonyeti Instance Link) --}}
                            @if ($leg->link_status === 'trip_created')
                                <span class="label label-primary" title="{{ $leg->link_message }}">
                                    <i class="fa fa-truck"></i> Transport trip {{ $leg->link_trip_number }} — {{ $leg->link_trip_status }}
                                </span>
                            @elseif ($leg->link_status === 'requested')
                                <span class="label label-info"><i class="fa fa-paper-plane"></i> Requested from transport</span>
                            @elseif ($leg->link_status === 'rejected')
                                <span class="label label-danger" title="{{ $leg->link_message }}"><i class="fa fa-ban"></i> Rejected: {{ \Illuminate\Support\Str::limit($leg->link_message, 40) }}</span>
                            @elseif ($leg->link_status === 'cancelled')
                                <span class="label label-default">Request cancelled</span>
                            @elseif ($leg->link_status === 'failed')
                                <span class="label label-warning" title="{{ $leg->link_message }}"><i class="fa fa-exclamation-triangle"></i> Not delivered</span>
                                <a href="#" wire:click.prevent="retryLink" class="btn btn-xs btn-warning"><i class="fa fa-refresh"></i> Retry</a>
                            @endif
                            @if ($linkedRoad && in_array($leg->link_status, [null, 'rejected', 'cancelled'], true) && $leg->status !== 'cancelled')
                                <a href="#" wire:click.prevent="sendToTransport({{ $leg->id }})" class="btn btn-xs btn-warning">
                                    <i class="fa fa-paper-plane"></i> {{ $leg->link_status ? 'Send again' : 'Send to Transport' }}
                                </a>
                            @endif
                            @if ($leg->link_status !== 'trip_created')
                                <a href="#" wire:click.prevent="delete({{ $leg->id }})" wire:confirm="Remove this leg?{{ $leg->link_ref ? ' The transport instance will be told the request is cancelled.' : '' }}" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                            @endif
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

                            @unless ($leg->trip_id || $leg->link_status === 'trip_created')
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

                            @unless ($leg->trip_id || $leg->link_status === 'trip_created')
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
    @include('livewire.freight.partials.pagination', ['paginator' => $legRows])

    <!-- Add/Edit Leg Modal -->
    <div wire:ignore.self class="modal fade" id="addLegModal{{ $shipment->id }}" tabindex="-1" role="dialog">
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
                                    @if (\App\Models\ShipmentLeg::carrierIsShippingLine($transport_mode))
                                        <label>Carrier (Shipping Line)</label>
                                        <select class="form-control" wire:model="carrier_shipping_line_id">
                                            <option value="">Select Shipping Line</option>
                                            @foreach ($shippingLines as $shippingLine)
                                                <option value="{{ $shippingLine->id }}">{{ $shippingLine->name }}</option>
                                            @endforeach
                                        </select>
                                        <small><a href="{{ route('freight.settings.shipping-lines') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Shipping Line</a></small>
                                        <a href="#" wire:click.prevent="refresh('shipping_lines')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @else
                                        <label>Carrier (Vendor){{ $transport_mode ? '' : ' - pick Sea mode to choose a shipping line' }}</label>
                                        <select class="form-control" wire:model="carrier_vendor_id">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                            @endforeach
                                        </select>
                                        <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small>
                                        <a href="#" wire:click.prevent="refresh('vendors')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @endif
                                    <input type="text" class="form-control mt-10" wire:model="carrier_name" placeholder="or type free text">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Leave carrier blank and use "Dispatch via Own Fleet" from the leg row instead if this is executed by our own trucks.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Origin</label>
                                    <select class="form-control" wire:model.defer="origin_id">
                                        <option value="">Select</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('origin_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                    <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small>
                                    <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Destination</label>
                                    <select class="form-control" wire:model.defer="destination_id">
                                        <option value="">Select</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('destination_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                    <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small>
                                    <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                        @unless ($editing_leg_id)
                            <small class="text-muted">Prefilled: a new leg starts where the previous leg ended (the first leg at the job's From) and heads to the job's To. Change either for a stop in between, e.g. a port.</small>
                        @endunless
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
    <div wire:ignore.self class="modal fade" id="dispatchModal{{ $shipment->id }}" tabindex="-1" role="dialog">
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
                            <small><a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small>
                            <a href="#" wire:click.prevent="refresh('transporters')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                                    <small><a href="{{ route('horses.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Horse</a></small>
                                    <a href="#" wire:click.prevent="refresh('dispatch_horses')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                                    <small><a href="{{ route('vehicles.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vehicle</a></small>
                                    <a href="#" wire:click.prevent="refresh('dispatch_vehicles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                            <small><a href="{{ route('drivers.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Driver</a></small>
                            <a href="#" wire:click.prevent="refresh('dispatch_drivers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From</label>
                                    <select class="form-control" wire:model.defer="dispatch_from">
                                        <option value="">Select</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('dispatch_from') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>To</label>
                                    <select class="form-control" wire:model.defer="dispatch_to">
                                        <option value="">Select</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('dispatch_to') <span class="text-danger error">{{ $message }}</span> @enderror
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
