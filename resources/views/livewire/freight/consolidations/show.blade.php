<div>
    <div class="row mt-30">
        <div class="col-md-12">
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ $consolidation->consolidation_number }}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th class="w-20">Master Shipment</th>
                                <td>{{ $consolidation->master_shipment?->shipment_number }} ({{ $consolidation->master_shipment?->freight_job?->job_number }} &mdash; {{ $consolidation->master_shipment?->freight_job?->customer?->name }})</td>
                            </tr>
                            <tr>
                                <th>Master Transport Document</th>
                                <td>{{ $consolidation->master_transport_document?->document_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Cost Allocation Basis</th>
                                <td>{{ ucwords(str_replace('_', ' ', $consolidation->cost_allocation_basis ?? '')) }}</td>
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

                    <h6 class="underline mt-20 mb-10"><strong>House Shipments</strong></h6>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Shipment #</th>
                                <th>Job #</th>
                                <th>Customer</th>
                                <th>Allocation</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($consolidation->house_shipments as $houseShipment)
                                <tr>
                                    <td>{{ $houseShipment->shipment_number }}</td>
                                    <td>{{ $houseShipment->freight_job?->job_number }}</td>
                                    <td>{{ $houseShipment->freight_job?->customer?->name }}</td>
                                    <td>{{ $houseShipment->pivot->allocation_value }}</td>
                                    <td>
                                        <a href="#" wire:click.prevent="detachShipment({{ $houseShipment->id }})" wire:confirm="Remove this house shipment from the consolidation?" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Detach</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No house shipments attached yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <form wire:submit.prevent="attachShipment" class="row mb-20">
                        <div class="col-md-6">
                            <select class="form-control" wire:model="house_shipment_id">
                                <option value="">Select Shipment to Attach</option>
                                @foreach ($availableShipments as $shipment)
                                    <option value="{{ $shipment->id }}">
                                        {{ $shipment->shipment_number }} &mdash; {{ $shipment->freight_job?->job_number }} &mdash; {{ $shipment->freight_job?->customer?->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('house_shipment_id') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="any" class="form-control" wire:model="allocation_value" placeholder="Allocation value (optional)">
                        </div>
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
