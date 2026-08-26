<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Freight Job</h5>
                                <small style="color: green">Asterisk <span style="color: red">(*)</span> sign indicates all mandatory fields.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="store()" class="p-20">

                                <h6 class="underline mt-20 mb-20"><strong>Customer &amp; Service</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="customer_id">
                                                <option value="">Select Customer</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer Reference</label>
                                            <input type="text" class="form-control" wire:model="customer_reference">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Freight Service Type</label>
                                            <select class="form-control" wire:model="freight_service_type_id">
                                                <option value="">Select Service Type</option>
                                                @foreach ($freight_service_types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Import/Export</label>
                                            <select class="form-control" wire:model="import_export_type">
                                                <option value="">Select</option>
                                                <option value="import">Import</option>
                                                <option value="export">Export</option>
                                                <option value="cross_trade">Cross Trade</option>
                                                <option value="domestic">Domestic</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Primary Transport Mode <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="primary_transport_mode">
                                                <option value="">Select Mode</option>
                                                <option value="sea">Sea</option>
                                                <option value="air">Air</option>
                                                <option value="road">Road</option>
                                                <option value="rail">Rail</option>
                                                <option value="courier">Courier</option>
                                                <option value="multimodal">Multimodal</option>
                                            </select>
                                            @error('primary_transport_mode') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Incoterm</label>
                                            <input type="text" class="form-control" wire:model="incoterm" placeholder="e.g. FOB, CIF, EXW">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" wire:model="currency_id">
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Quotation</label>
                                            <select class="form-control" wire:model="quotation_id">
                                                <option value="">None</option>
                                                @foreach ($quotations as $quotation)
                                                    <option value="{{ $quotation->id }}">{{ $quotation->quotation_number }} - {{ $quotation->customer?->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Origin</label>
                                            <input type="text" class="form-control" wire:model="origin">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Origin Country</label>
                                            <select class="form-control" wire:model="origin_country_id">
                                                <option value="">Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Destination</label>
                                            <input type="text" class="form-control" wire:model="destination">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Destination Country</label>
                                            <select class="form-control" wire:model="destination_country_id">
                                                <option value="">Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Salesperson</label>
                                            <select class="form-control" wire:model="salesperson_id">
                                                <option value="">None</option>
                                                @foreach ($officers as $officer)
                                                    <option value="{{ $officer->id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Operations Officer</label>
                                            <select class="form-control" wire:model="operations_officer_id">
                                                <option value="">None</option>
                                                @foreach ($officers as $officer)
                                                    <option value="{{ $officer->id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Clearing Officer</label>
                                            <select class="form-control" wire:model="clearing_officer_id">
                                                <option value="">None</option>
                                                @foreach ($officers as $officer)
                                                    <option value="{{ $officer->id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="underline mt-20 mb-20"><strong>Shipment Details</strong></h6>
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
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Port/Place of Loading</label>
                                            <select class="form-control" wire:model="port_of_loading_id">
                                                <option value="">Select Location</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Port/Place of Discharge</label>
                                            <select class="form-control" wire:model="port_of_discharge_id">
                                                <option value="">Select Location</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Place of Receipt</label>
                                            <select class="form-control" wire:model="place_of_receipt_id">
                                                <option value="">Select Location</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Place of Delivery</label>
                                            <select class="form-control" wire:model="place_of_delivery_id">
                                                <option value="">Select Location</option>
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
                                </div>

                                <h6 class="underline mt-20 mb-20"><strong>Cargo</strong></h6>
                                @foreach ($cargo_rows as $index => $row)
                                    <div class="row mb-10" style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Commodity</label>
                                                <select class="form-control" wire:model="cargo_rows.{{ $index }}.cargo_id">
                                                    <option value="">Select</option>
                                                    @foreach ($cargos as $cargo)
                                                        <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <input type="text" class="form-control" wire:model="cargo_rows.{{ $index }}.commodity">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>HS Code</label>
                                                <input type="text" class="form-control" wire:model="cargo_rows.{{ $index }}.hs_code">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>Qty</label>
                                                <input type="number" step="any" class="form-control" wire:model="cargo_rows.{{ $index }}.quantity">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>UOM</label>
                                                <input type="text" class="form-control" wire:model="cargo_rows.{{ $index }}.uom">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>Packages</label>
                                                <input type="number" class="form-control" wire:model="cargo_rows.{{ $index }}.packages">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>Gross Wt</label>
                                                <input type="number" step="any" class="form-control" wire:model="cargo_rows.{{ $index }}.gross_weight">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>DG</label><br>
                                                <input type="checkbox" wire:model="cargo_rows.{{ $index }}.is_dangerous_goods">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            @if (count($cargo_rows) > 1)
                                                <a href="#" wire:click.prevent="removeCargoRow({{ $index }})" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <a href="#" wire:click.prevent="addCargoRow" class="btn btn-xs btn-info mb-20"><i class="fa fa-plus"></i> Add Cargo Line</a>

                                <h6 class="underline mt-20 mb-20"><strong>Parties</strong></h6>
                                @foreach ($party_rows as $index => $row)
                                    <div class="row mb-10" style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Party Type</label>
                                                <select class="form-control" wire:model="party_rows.{{ $index }}.party_type">
                                                    <option value="">Select</option>
                                                    @foreach ($partyOptions as $option)
                                                        <option value="{{ $option }}">{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Party</label>
                                                <select class="form-control" wire:model="party_rows.{{ $index }}.party_id">
                                                    <option value="">Select</option>
                                                    @if (!empty($row['party_type']) && isset($partyModels[$row['party_type']]))
                                                        @foreach ($partyModels[$row['party_type']] as $partyRecord)
                                                            <option value="{{ $partyRecord->id }}">{{ $partyRecord->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select class="form-control" wire:model="party_rows.{{ $index }}.role">
                                                    <option value="">Select Role</option>
                                                    <option value="shipper">Shipper</option>
                                                    <option value="consignee">Consignee</option>
                                                    <option value="notify_party">Notify Party</option>
                                                    <option value="forwarding_agent">Forwarding Agent</option>
                                                    <option value="overseas_agent">Overseas Agent</option>
                                                    <option value="shipping_line">Shipping Line</option>
                                                    <option value="airline">Airline</option>
                                                    <option value="customs_broker">Customs Broker</option>
                                                    <option value="warehouse">Warehouse</option>
                                                    <option value="port">Port</option>
                                                    <option value="terminal">Terminal</option>
                                                    <option value="inspection_agent">Inspection Agent</option>
                                                    <option value="container_depot">Container Depot</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label>&nbsp;</label><br>
                                            @if (count($party_rows) > 1)
                                                <a href="#" wire:click.prevent="removePartyRow({{ $index }})" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <a href="#" wire:click.prevent="addPartyRow" class="btn btn-xs btn-info mb-20"><i class="fa fa-plus"></i> Add Party</a>

                                <h6 class="underline mt-20 mb-20"><strong>Notes</strong></h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="3" wire:model="notes"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-20">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right">
                                            <button type="submit" class="btn btn-primary btn-wide btn-rounded"><i class="fa fa-save"></i> Save Freight Job</button>
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
