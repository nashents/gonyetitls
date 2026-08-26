<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Consolidation</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="store()" class="p-20">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Master Shipment <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="master_shipment_id">
                                                <option value="">Select Master Shipment</option>
                                                @foreach ($shipments as $shipment)
                                                    <option value="{{ $shipment->id }}">
                                                        {{ $shipment->shipment_number }} &mdash; {{ $shipment->freight_job?->job_number }} &mdash; {{ $shipment->freight_job?->customer?->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('master_shipment_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cost Allocation Basis</label>
                                            <select class="form-control" wire:model="cost_allocation_basis">
                                                <option value="">Select</option>
                                                <option value="weight">Weight</option>
                                                <option value="cbm">CBM</option>
                                                <option value="container">Container</option>
                                                <option value="chargeable_weight">Chargeable Weight</option>
                                                <option value="fixed_percentage">Fixed Percentage</option>
                                                <option value="manual">Manual</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea class="form-control" rows="3" wire:model="notes"></textarea>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right">
                                            <button type="submit" class="btn btn-primary btn-wide btn-rounded"><i class="fa fa-save"></i> Save Consolidation</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
