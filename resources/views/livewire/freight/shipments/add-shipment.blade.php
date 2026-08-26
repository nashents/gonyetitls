<div style="display:inline-block">
    <a href="#" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addShipmentModal{{ $job->id }}">
        <i class="fa fa-plus"></i> Add Shipment
    </a>

    <div class="modal fade" id="addShipmentModal{{ $job->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Add Shipment to {{ $job->job_number }}</h4>
                    </div>
                    <div class="modal-body">
                        <h6 class="underline mt-10 mb-10"><strong>Shipment Details</strong></h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mode <span class="required text-danger">*</span></label>
                                    <select class="form-control" wire:model="mode">
                                        <option value="">Select Mode</option>
                                        <option value="sea">Sea</option>
                                        <option value="air">Air</option>
                                        <option value="road">Road</option>
                                        <option value="rail">Rail</option>
                                        <option value="courier">Courier</option>
                                        <option value="multimodal">Multimodal</option>
                                    </select>
                                    @error('mode') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Shipment Type</label>
                                    <select class="form-control" wire:model="shipment_type">
                                        <option value="">Select Type</option>
                                        <option value="FCL">FCL</option>
                                        <option value="LCL">LCL</option>
                                        <option value="Breakbulk">Breakbulk</option>
                                        <option value="Bulk">Bulk</option>
                                        <option value="RoRo">RoRo</option>
                                        <option value="Consolidation">Consolidation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Port of Loading</label>
                                    <select class="form-control" wire:model="port_of_loading_id">
                                        <option value="">Select</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Port of Discharge</label>
                                    <select class="form-control" wire:model="port_of_discharge_id">
                                        <option value="">Select</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ETD</label>
                                    <input type="datetime-local" class="form-control" wire:model="etd">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ETA</label>
                                    <input type="datetime-local" class="form-control" wire:model="eta">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Booking Reference</label>
                                    <input type="text" class="form-control" wire:model="booking_reference">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Freight Terms</label>
                                    <select class="form-control" wire:model="freight_terms">
                                        <option value="">Select</option>
                                        <option value="prepaid">Prepaid</option>
                                        <option value="collect">Collect</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h6 class="underline mt-20 mb-10"><strong>Cargo</strong></h6>
                        @foreach ($cargo_rows as $index => $row)
                            <div class="row mb-10">
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="cargo_rows.{{ $index }}.cargo_id">
                                        <option value="">Commodity</option>
                                        @foreach ($cargos as $cargo)
                                            <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" wire:model="cargo_rows.{{ $index }}.commodity" placeholder="Description">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="any" class="form-control" wire:model="cargo_rows.{{ $index }}.quantity" placeholder="Qty">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="any" class="form-control" wire:model="cargo_rows.{{ $index }}.gross_weight" placeholder="Gross Wt">
                                </div>
                                <div class="col-md-2">
                                    @if (count($cargo_rows) > 1)
                                        <a href="#" wire:click.prevent="removeCargoRow({{ $index }})" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <a href="#" wire:click.prevent="addCargoRow" class="btn btn-xs btn-info mb-20"><i class="fa fa-plus"></i> Add Cargo Line</a>

                        <h6 class="underline mt-20 mb-10"><strong>Parties</strong></h6>
                        @foreach ($party_rows as $index => $row)
                            <div class="row mb-10">
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="party_rows.{{ $index }}.party_type">
                                        <option value="">Party Type</option>
                                        @foreach ($partyOptions as $option)
                                            <option value="{{ $option }}">{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="party_rows.{{ $index }}.party_id">
                                        <option value="">Select Party</option>
                                        @if (!empty($row['party_type']) && isset($partyModels[$row['party_type']]))
                                            @foreach ($partyModels[$row['party_type']] as $partyRecord)
                                                <option value="{{ $partyRecord->id }}">{{ $partyRecord->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="party_rows.{{ $index }}.role">
                                        <option value="">Select Role</option>
                                        <option value="shipper">Shipper</option>
                                        <option value="consignee">Consignee</option>
                                        <option value="notify_party">Notify Party</option>
                                        <option value="forwarding_agent">Forwarding Agent</option>
                                        <option value="shipping_line">Shipping Line</option>
                                        <option value="airline">Airline</option>
                                        <option value="customs_broker">Customs Broker</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    @if (count($party_rows) > 1)
                                        <a href="#" wire:click.prevent="removePartyRow({{ $index }})" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <a href="#" wire:click.prevent="addPartyRow" class="btn btn-xs btn-info"><i class="fa fa-plus"></i> Add Party</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Shipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addShipmentModal-{{ $job->id }}', event => {
            $('#addShipmentModal{{ $job->id }}').modal('hide');
        })
    </script>
</div>
