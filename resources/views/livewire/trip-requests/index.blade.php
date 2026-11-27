<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%;">
                            <p class="text-muted" style="margin-bottom:12px">
                                <i class="fas fa-link"></i> Road legs sent from the freight forwarding instance. Create a trip for each request, or reject it with a reason. The freight team sees the outcome and the trip's progress automatically.
                            </p>

                            @if ($waiting)
                                <div class="alert alert-warning" style="padding:8px 12px">
                                    <i class="fa fa-exclamation-triangle"></i> {{ $waiting }} update(s) haven't reached the freight instance yet.
                                    <a href="#" wire:click.prevent="retryDelivery" class="btn btn-xs btn-warning" style="margin-left:6px"><i class="fa fa-refresh"></i> Retry now</a>
                                </div>
                            @endif

                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-7">
                                    <div class="btn-group" role="group">
                                        <button type="button" wire:click="$set('filterStatus', '')" class="btn btn-sm {{ $filterStatus === '' ? 'btn-primary' : 'btn-default' }}">All</button>
                                        @foreach ($statuses as $key => $label)
                                            <button type="button" wire:click="$set('filterStatus', '{{ $key }}')" class="btn btn-sm {{ $filterStatus === $key ? 'btn-primary' : 'btn-default' }}">
                                                {{ $label }} <span class="badge">{{ $counts[$key] ?? 0 }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search request#, job#, shipment#, place, container...">
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm" width="100%">
                                <thead>
                                    <tr>
                                        <th>Request#</th>
                                        <th>Freight Job / Shipment</th>
                                        <th>Route</th>
                                        <th>Planned</th>
                                        <th>Cargo</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($requests as $req)
                                        <tr wire:key="trip-request-{{ $req->id }}">
                                            <td>
                                                {{ $req->request_number }}
                                                <br><small class="text-muted">{{ optional($req->created_at)->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                {{ $req->job_number }} @if ($req->shipment_number) / {{ $req->shipment_number }} @endif
                                                <br><small class="text-muted">{{ $req->source_company }}{{ $req->job_customer ? ' · for ' . $req->job_customer : '' }}</small>
                                            </td>
                                            <td>
                                                {{ $req->origin_text ?: '—' }} &rarr; {{ $req->destination_text ?: '—' }}
                                                @if ((!$req->origin_id || !$req->destination_id) && $req->isPending())
                                                    <br><small class="text-warning" title="No matching destination here - pick one when creating the trip"><i class="fa fa-map-marker"></i> place not matched</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ optional($req->planned_departure)->format('d M Y') ?? '—' }}
                                                @if ($req->planned_arrival) <br><small class="text-muted">arr {{ $req->planned_arrival->format('d M Y') }}</small> @endif
                                            </td>
                                            <td style="max-width:260px">
                                                {{ \Illuminate\Support\Str::limit($req->cargo_description, 60) }}
                                                @if ($req->containers) <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($req->containers, 60) }}</small> @endif
                                                @if ($req->gross_weight) <br><small class="text-muted">{{ number_format($req->gross_weight, 2) }} kg{{ $req->package_count ? ' · ' . $req->package_count . ' pkgs' : '' }}</small> @endif
                                            </td>
                                            <td>
                                                @php $cls = ['pending' => 'warning', 'trip_created' => 'success', 'rejected' => 'danger', 'cancelled' => 'default'][$req->status] ?? 'default'; @endphp
                                                <span class="label label-{{ $cls }}">{{ $statuses[$req->status] ?? $req->status }}</span>
                                                @if ($req->trip)
                                                    <br><a href="{{ route('trips.show', $req->trip_id) }}" target="_blank"><small>{{ $req->trip->trip_number }} · {{ $req->trip->trip_status }}</small></a>
                                                @endif
                                                @if ($req->status === 'rejected' || $req->status === 'cancelled')
                                                    <br><small class="text-muted" title="{{ $req->rejection_reason }}">{{ \Illuminate\Support\Str::limit($req->rejection_reason, 50) }}</small>
                                                @endif
                                                @if ($req->changed_since_trip)
                                                    <br><span class="label label-danger" title="The freight team changed the leg after the trip was created - check the trip">Leg changed</span>
                                                    <a href="#" wire:click.prevent="acknowledgeChange({{ $req->id }})" title="Mark as checked"><small>checked</small></a>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                @if ($req->isPending())
                                                    <a href="#" wire:click.prevent="openCreate({{ $req->id }})" class="btn btn-xs btn-success"><i class="fa fa-truck"></i> Create Trip</a>
                                                    <a href="#" wire:click.prevent="openLink({{ $req->id }})" class="btn btn-xs btn-default" title="A trip for this already exists"><i class="fa fa-link"></i> Link Trip</a>
                                                    <a href="#" wire:click.prevent="openReject({{ $req->id }})" class="btn btn-xs btn-danger"><i class="fa fa-ban"></i> Reject</a>
                                                @elseif ($req->responder)
                                                    <small class="text-muted">by {{ $req->responder->name }} {{ $req->responder->surname }}<br>{{ optional($req->responded_at)->format('d/m/Y H:i') }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted" style="padding:15px">No trip requests{{ $filterStatus ? ' with this status' : '' }}.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <nav class="text-center" style="float:right">{{ $requests->links() }}</nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Create Trip --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripRequestCreateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-truck"></i> Create Trip from Request <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="createTrip">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Customer (billed) <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="customer_id">
                                <option value="">Select</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Transporter <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="transporter_id">
                                <option value="">Select</option>
                                @foreach ($transporters as $transporter)
                                    <option value="{{ $transporter->id }}">{{ $transporter->name }}{{ $transporter->default ? ' (Own Fleet)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('transporter_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Horse</label>
                                    <select class="form-control" wire:model.defer="horse_id">
                                        <option value="">None</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{ $horse->id }}">{{ $horse->identifier_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vehicle</label>
                                    <select class="form-control" wire:model.defer="vehicle_id">
                                        <option value="">None</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->identifier_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Driver <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="driver_id">
                                <option value="">Select</option>
                                @foreach ($drivers as $driver)
                                    @if ($driver->employee)
                                        <option value="{{ $driver->id }}">{{ $driver->employee->name }} {{ $driver->employee->surname }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('driver_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From <span class="text-danger">*</span></label>
                                    <select class="form-control" wire:model.defer="from">
                                        <option value="">Select</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('from') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>To <span class="text-danger">*</span></label>
                                    <select class="form-control" wire:model.defer="to">
                                        <option value="">Select</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                        @endforeach
                                    </select>
                                    @error('to') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" wire:model.defer="start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trip Status</label>
                                    <select class="form-control" wire:model.defer="trip_status">
                                        @foreach ($tripStatuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Freight, rates and route details can be completed on the trip afterwards. The trip number goes back to the freight team.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-rounded" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-success btn-rounded" wire:loading.attr="disabled"><i class="fa fa-save"></i> Create Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Link existing trip --}}
    <div wire:ignore.self data-backdrop="static" class="modal" id="tripRequestLinkModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-link"></i> Link an Existing Trip <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="linkTrip">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Trip Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model.defer="link_trip_number" placeholder="e.g. FHT00123">
                            @error('link_trip_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <small class="text-muted">Use this when the trip was already created by hand. Its number and progress go back to the freight team.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-rounded" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-success btn-rounded" wire:loading.attr="disabled"><i class="fa fa-link"></i> Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject --}}
    <div wire:ignore.self data-backdrop="static" class="modal" id="tripRequestRejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-ban"></i> Reject Trip Request <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="reject">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="3" wire:model.defer="rejection_reason" placeholder="e.g. No trucks available on that date"></textarea>
                            @error('rejection_reason') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <small class="text-muted">The freight team sees this reason on the leg and can send the request again.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-rounded" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger btn-rounded" wire:loading.attr="disabled"><i class="fa fa-ban"></i> Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
