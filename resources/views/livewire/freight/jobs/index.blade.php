<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Freight Jobs</h5>
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
                                <div class="col-md-2">
                                    <select class="form-control" wire:model="filter_status">
                                        <option value="">All Statuses</option>
                                        @foreach (['draft','confirmed','in_progress','customs_clearance','port_storage','transport_arranged','delivered','invoiced','closed','cancelled'] as $status)
                                            <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 text-right">
                                    <a href="{{ route('freight.jobs.create') }}" class="btn btn-primary btn-rounded"><i class="fa fa-plus"></i> New Freight Job</a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Job #</th>
                                            <th>Customer</th>
                                            <th>Service Type</th>
                                            <th>Mode</th>
                                            <th>Origin &rarr; Destination</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jobs as $job)
                                            <tr>
                                                <td>{{ $job->job_number }}</td>
                                                <td>{{ $job->customer?->name }}</td>
                                                <td>{{ $job->freight_service_type?->name }}</td>
                                                <td>{{ ucfirst($job->primary_transport_mode ?? '') }}</td>
                                                <td>{{ $job->origin }} &rarr; {{ $job->destination }}</td>
                                                <td><span class="label label-info label-wide">{{ ucwords(str_replace('_', ' ', $job->status)) }}</span></td>
                                                <td>{{ $job->created_at->format('d M Y') }}</td>
                                                <td>
                                                    <a href="{{ route('freight.jobs.show', $job->id) }}" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No freight jobs found.</td>
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
</div>
