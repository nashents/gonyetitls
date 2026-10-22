<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>{{ $lockedAuthorization ? ucfirst($lockedAuthorization) . ' ' : '' }}Freight Jobs</h5>
                            </div>
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <div class="row mb-20">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search job #, customer ref, customer, origin, destination">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" wire:model="filter_customer_id">
                                        <option value="">All Customers</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" wire:model="filter_freight_service_type_id">
                                        <option value="">All Service Types</option>
                                        @foreach ($freight_service_types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <select class="form-control" wire:model="filter_status">
                                        <option value="">All Statuses</option>
                                        @foreach (['draft','confirmed','in_progress','customs_clearance','port_storage','transport_arranged','delivered','invoiced','closed','cancelled'] as $status)
                                            <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @unless ($lockedAuthorization)
                                    <div class="col-md-1">
                                        <select class="form-control" wire:model="filter_authorization">
                                            <option value="">All Authorizations</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                @endunless
                                <div class="col-md-{{ $lockedAuthorization ? 3 : 2 }} text-right">
                                    <a href="{{ route('freight.jobs.create') }}" class="btn btn-primary btn-rounded"><i class="fa fa-plus"></i> New Freight Job</a>
                                </div>
                            </div>

                            @if ($pendingAuthorizationCount > 0 && $filter_authorization !== 'pending' && $lockedAuthorization !== 'pending')
                                <div class="alert alert-warning">
                                    <i class="fa fa-gavel"></i> {{ $pendingAuthorizationCount }} freight job{{ $pendingAuthorizationCount > 1 ? 's are' : ' is' }} waiting for authorization.
                                    <a href="{{ route('freight.jobs.pending') }}"><strong>Show them</strong></a>
                                </div>
                            @endif

                            @if ($this->canAuthorize && !empty($selectedRows))
                                <div class="mb-10">
                                    <button type="button" wire:click="showBulkAuthorize()" class="btn btn-default border-primary btn-rounded btn-wide">
                                        <i class="fa fa-gavel"></i> Bulk Authorize
                                    </button>
                                    <span style="margin-left:10px;">{{ count($selectedRows) }} job(s) selected.</span>
                                    <a href="#" wire:click.prevent="$set('selectedRows', [])" style="margin-left:10px;">Clear selection</a>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            @if ($this->canAuthorize)
                                                <th><input type="checkbox" wire:model="selectPageRows" title="Select all pending jobs on this page"></th>
                                            @endif
                                            <th>Job #</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Documents</th>
                                            <th>Shipment</th>
                                            <th>Origin &rarr; Destination</th>
                                            <th>Port Exposure</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $jobStatusColors = [
                                                'draft' => 'default',
                                                'confirmed' => 'primary',
                                                'in_progress' => 'info',
                                                'customs_clearance' => 'warning',
                                                'port_storage' => 'warning',
                                                'transport_arranged' => 'info',
                                                'delivered' => 'success',
                                                'invoiced' => 'success',
                                                'closed' => 'default',
                                                'cancelled' => 'danger',
                                            ];
                                        @endphp
                                        @forelse ($jobs as $job)
                                            @php
                                                $primaryShipment = $job->shipments->sortByDesc('id')->first();
                                                $masterBl = $primaryShipment?->transport_documents
                                                    ->whereIn('document_type', ['master_bill_of_lading', 'master_air_waybill'])
                                                    ->first();
                                                $declaration = $primaryShipment?->customs_declarations->first();
                                                $primaryContainer = $primaryShipment?->containers->first();
                                                $consigneeParty = $primaryShipment?->parties->firstWhere('party_type', 'consignee');
                                                $consigneeName = $consigneeParty?->party()?->name;
                                                $storageExposure = $primaryContainer?->exposures->firstWhere('charge_type', 'port_storage');
                                                $demurrageExposure = $primaryContainer?->exposures->firstWhere('charge_type', 'demurrage');
                                                $daysUntilArrival = ($primaryShipment?->eta && !$primaryShipment->actual_arrival)
                                                    ? now()->startOfDay()->diffInDays($primaryShipment->eta->startOfDay(), false)
                                                    : null;
                                            @endphp
                                            <tr wire:key="freight-job-{{ $job->id }}">
                                                @if ($this->canAuthorize)
                                                    <td>
                                                        @if ($job->authorization === 'pending')
                                                            <input type="checkbox" wire:model="selectedRows" value="{{ $job->id }}">
                                                        @endif
                                                    </td>
                                                @endif
                                                <td>{{ $job->job_number }}</td>
                                                <td>{{ $job->customer?->name }}</td>
                                                <td>
                                                    <strong>Type:</strong> {{ $job->freight_service_type?->name ?? '—' }}<br>
                                                    <strong>Mode:</strong> {{ ucfirst($job->primary_transport_mode ?? '—') }}
                                                </td>
                                                <td>
                                                    <strong>Master BL:</strong> {{ $masterBl?->document_number ?? '—' }}<br>
                                                    <strong>Declaration:</strong> {{ $declaration?->declaration_number ?? '—' }}
                                                </td>
                                                <td>
                                                    <strong>Consignee:</strong> {{ $consigneeName ?? '—' }}<br>
                                                    <strong>Line:</strong> {{ $primaryContainer?->shipping_line?->name ?? '—' }}<br>
                                                    <strong>Container Type:</strong> {{ $primaryContainer?->container_type ?? '—' }}<br>
                                                    <strong>Container#:</strong> {{ $primaryContainer?->container_number ?? '—' }}
                                                </td>
                                                <td>
                                                    @if ($job->fromDestination || $job->toDestination)
                                                        {{ $job->fromDestination ? trim(($job->fromDestination->country->name ?? '') . ' ' . $job->fromDestination->city) : '—' }}
                                                        &rarr;
                                                        {{ $job->toDestination ? trim(($job->toDestination->country->name ?? '') . ' ' . $job->toDestination->city) : '—' }}
                                                    @else
                                                        {{ $job->origin }} &rarr; {{ $job->destination }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>Storage Days:</strong> {{ $storageExposure?->chargeable_days ?? ($primaryContainer ? 0 : '—') }}<br>
                                                    <strong>Demurrage Days:</strong> {{ $demurrageExposure?->chargeable_days ?? ($primaryContainer ? 0 : '—') }}
                                                </td>
                                                <td>
                                                    <span class="label label-{{ $jobStatusColors[$job->status] ?? 'info' }} label-wide">{{ ucwords(str_replace('_', ' ', $job->status)) }}</span>
                                                    <br><span class="label label-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$job->authorization] ?? 'default' }} label-wide" style="margin-top:4px; display:inline-block" title="{{ $job->authorization_comments }}">{{ ucfirst($job->authorization) }}</span>
                                                    @if ($daysUntilArrival !== null)
                                                        <br><small class="text-muted">{{ $daysUntilArrival > 0 ? $daysUntilArrival . ' days until arrival' : ($daysUntilArrival === 0 ? 'Arriving today' : abs($daysUntilArrival) . ' days overdue' ) }}</small>
                                                    @endif
                                                    <br><small class="text-muted">Created {{ $job->created_at->format('d M Y') }}</small>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i> <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                            <li><a href="{{ route('freight.jobs.show', $job->id) }}"><i class="fa fa-eye color-default"></i> View</a></li>
                                                            <li><a href="{{ route('freight.jobs.edit', $job->id) }}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                            @if ($this->canAuthorize && $job->authorization === 'pending')
                                                                <li><a href="#" wire:click.prevent="authorizeJob({{ $job->id }})"><i class="fa fa-gavel color-primary"></i> Authorize</a></li>
                                                            @endif
                                                            <li role="separator" class="divider"></li>
                                                            <li class="dropdown-header">Documents</li>
                                                            <li><a href="{{ route('freight.jobs.sheet', $job->id) }}" target="_blank"><i class="fa fa-file-text color-default"></i> Job Sheet</a></li>
                                                            <li><a href="{{ route('freight.jobs.bill_of_lading', $job->id) }}" target="_blank"><i class="fa fa-file-text-o color-default"></i> Bill of Lading</a></li>
                                                            @foreach ($job->documents as $document)
                                                                <li><a href="{{ asset('myfiles/documents/' . $document->filename) }}" target="_blank"><i class="fa fa-download color-default"></i> {{ $document->title ?: $document->filename }}</a></li>
                                                            @endforeach
                                                            <li role="separator" class="divider"></li>
                                                            <li><a href="#" wire:click.prevent="delete({{ $job->id }})" wire:confirm="Delete freight job {{ $job->job_number }}? This cannot be undone."><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $this->canAuthorize ? 10 : 9 }}" class="text-center">No freight jobs found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $jobs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('livewire.freight.jobs.partials.authorization-modals')
</div>
