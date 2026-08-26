<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Consolidations</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <div class="row mb-20">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search consolidation # or master shipment #">
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('freight.consolidations.create') }}" class="btn btn-primary btn-rounded"><i class="fa fa-plus"></i> New Consolidation</a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Consolidation #</th>
                                            <th>Master Shipment</th>
                                            <th>Master Customer</th>
                                            <th>House Shipments</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($consolidations as $consolidation)
                                            <tr>
                                                <td>{{ $consolidation->consolidation_number }}</td>
                                                <td>{{ $consolidation->master_shipment?->shipment_number }}</td>
                                                <td>{{ $consolidation->master_shipment?->freight_job?->customer?->name }}</td>
                                                <td>{{ $consolidation->house_shipments->count() }}</td>
                                                <td><span class="label label-info label-wide">{{ ucfirst($consolidation->status) }}</span></td>
                                                <td>
                                                    <a href="{{ route('freight.consolidations.show', $consolidation->id) }}" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No consolidations found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $consolidations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
