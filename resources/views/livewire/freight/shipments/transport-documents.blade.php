<div>
    <div class="mb-10">
        <a href="#" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addTransportDocumentModal{{ $shipment->id }}">
            <i class="fa fa-plus"></i> Add Transport Document
        </a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Document #</th>
                <th>Issue Date</th>
                <th>Carrier</th>
                <th># Originals</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($shipment->transport_documents as $doc)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $doc->document_type)) }}</td>
                    <td>{{ $doc->document_number }}</td>
                    <td>{{ $doc->issue_date?->format('d M Y') }}</td>
                    <td>{{ $doc->carrier_vendor?->name ?? $doc->carrier_name }}</td>
                    <td>{{ $doc->number_of_originals }}</td>
                    <td><span class="label label-info label-wide">{{ ucwords(str_replace('_', ' ', $doc->status)) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No transport documents recorded for this shipment yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Add Transport Document Modal -->
    <div class="modal fade" id="addTransportDocumentModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Add Transport Document</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Type <span class="required text-danger">*</span></label>
                            <select class="form-control" wire:model="document_type">
                                <option value="">Select</option>
                                <option value="master_bill_of_lading">Master Bill of Lading</option>
                                <option value="house_bill_of_lading">House Bill of Lading</option>
                                <option value="sea_waybill">Sea Waybill</option>
                                <option value="master_air_waybill">Master Air Waybill</option>
                                <option value="house_air_waybill">House Air Waybill</option>
                                <option value="booking_number">Booking Number</option>
                            </select>
                            @error('document_type') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Document Number <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="document_number">
                            @error('document_number') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Issue Date</label>
                                    <input type="date" class="form-control" wire:model="issue_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label># Originals</label>
                                    <input type="number" class="form-control" wire:model="number_of_originals">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Carrier</label>
                            <select class="form-control" wire:model="carrier_vendor_id">
                                <option value="">Select Vendor</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control mt-10" wire:model="carrier_name" placeholder="or type free text">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Place of Issue</label>
                                    <input type="text" class="form-control" wire:model="place_of_issue">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Freight Payable At</label>
                                    <input type="text" class="form-control" wire:model="freight_payable_at">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" wire:model="status">
                                <option value="draft">Draft</option>
                                <option value="issued">Issued</option>
                                <option value="surrendered">Surrendered</option>
                                <option value="telex_release">Telex Release</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addTransportDocumentModal-{{ $shipment->id }}', event => {
            $('#addTransportDocumentModal{{ $shipment->id }}').modal('hide');
        })
    </script>
</div>
