<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Freight Job — {{ $job->job_number }}</h5>
                                <small style="color: green">Asterisk <span style="color: red">(*)</span> sign indicates all mandatory fields.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="update()" class="p-20">

                                <h6 class="underline mt-20 mb-20"><strong>Customer &amp; Service</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer @if ($this->isConsolidation())<small class="text-muted">(optional - Consolidation)</small>@else<span class="required text-danger">*</span>@endif</label>
                                            <select class="form-control" wire:model="customer_id">
                                                <option value="">{{ $this->isConsolidation() ? "None (container job)" : "Select Customer" }}</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                            <small><a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small>
                                            <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                                            <small><a href="{{ route('freight.settings.service-types') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Service Type</a></small>
                                            <a href="#" wire:click.prevent="refresh('freight_service_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                                            <select class="form-control" wire:model="incoterm">
                                                <option value="">Select</option>
                                                @foreach ($incoterms as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" wire:model="currency_id">
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small>
                                            <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                                            <small><a href="{{ route('quotations.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Quotation</a></small>
                                            <a href="#" wire:click.prevent="refresh('quotations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Origin</label>
                                            <select class="form-control" wire:model="from">
                                                <option value="">Select Origin</option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small>
                                            <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Destination</label>
                                            <select class="form-control" wire:model="to">
                                                <option value="">Select Destination</option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small>
                                            <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
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
                                                    <option value="{{ $officer->user_id }}">{{ $officer->name }} {{ $officer->surname }}</option>
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
                                                    <option value="{{ $officer->user_id }}">{{ $officer->name }} {{ $officer->surname }}</option>
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
                                                    <option value="{{ $officer->user_id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $partyRoutes = ['customer' => 'customers.index', 'vendor' => 'vendors.index', 'consignee' => 'consignees.index', 'broker' => 'brokers.index', 'agent' => 'agents.index', 'transporter' => 'transporters.index', 'clearing_agent' => 'clearing_agents.index'];
                                    $partyRoles = \App\Http\Livewire\Freight\Jobs\Edit::PARTY_ROLES;
                                @endphp
                                @foreach ($shipments as $s => $shipment)
                                    <div wire:key="edit-shipment-{{ $shipment['id'] ?? 'new-' . $s }}" class="mt-20" style="border:1px solid #e5e5e5; border-radius:5px; padding:15px;">
                                        <h6 class="underline mt-0 mb-20">
                                            <strong>Shipment Details{{ $shipment['shipment_number'] ? ' - ' . $shipment['shipment_number'] : '' }}</strong>
                                            @if (! $shipment['id'])
                                                <small class="text-muted">(this job has no shipment yet - one is created if you fill anything in here)</small>
                                            @endif
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Shipment Type</label>
                                                    <select class="form-control" wire:model="shipments.{{ $s }}.shipment_type">
                                                        <option value="">Select Type</option>
                                                        @foreach (['FCL', 'LCL', 'Breakbulk', 'Bulk', 'RoRo', 'Consolidation'] as $shipmentType)
                                                            <option value="{{ $shipmentType }}">{{ $shipmentType }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Booking Reference</label>
                                                    <input type="text" class="form-control" wire:model="shipments.{{ $s }}.booking_reference">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Freight Terms</label>
                                                    <select class="form-control" wire:model="shipments.{{ $s }}.freight_terms">
                                                        <option value="">Select</option>
                                                        <option value="prepaid">Prepaid</option>
                                                        <option value="collect">Collect</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Loading Point</label>
                                                    <select class="form-control" wire:model="shipments.{{ $s }}.loading_point_id">
                                                        <option value="">Select Loading Point</option>
                                                        @foreach ($loading_points as $loading_point)
                                                            <option value="{{ $loading_point->id }}">{{ $loading_point->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small><a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small>
                                                    <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Offloading Point</label>
                                                    <select class="form-control" wire:model="shipments.{{ $s }}.offloading_point_id">
                                                        <option value="">Select Offloading Point</option>
                                                        @foreach ($offloading_points as $offloading_point)
                                                            <option value="{{ $offloading_point->id }}">{{ $offloading_point->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small><a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Point</a></small>
                                                    <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>ETD</label>
                                                    <input type="datetime-local" class="form-control" wire:model="shipments.{{ $s }}.etd">
                                                    @error('shipments.' . $s . '.etd') <span class="text-danger error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>ETA</label>
                                                    <input type="datetime-local" class="form-control" wire:model="shipments.{{ $s }}.eta">
                                                    @error('shipments.' . $s . '.eta') <span class="text-danger error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            {{-- Estimates are per job, so they sit beside the first shipment's dates only. --}}
                                            @if ($loop->first && \App\Models\Company::freightCostingVisibleTo())
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Estimated Revenue</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.defer="estimated_revenue" placeholder="0.00">
                                                        @error('estimated_revenue') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Estimated Cost</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.defer="estimated_cost" placeholder="0.00">
                                                        @error('estimated_cost') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <h6 class="underline mt-20 mb-20"><strong>Cargo</strong></h6>
                                        @foreach ($shipment['cargo_rows'] as $i => $row)
                                            <div wire:key="edit-shipment-{{ $s }}-cargo-{{ $row['id'] ?? 'new-' . $i }}" class="row mb-10" style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Commodity</label>
                                                        <select class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.cargo_id">
                                                            <option value="">Select</option>
                                                            @foreach ($cargos as $cargo)
                                                                <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New</a></small>
                                                        <a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <input type="text" class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.commodity">
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>HS Code</label>
                                                        <input type="text" class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.hs_code">
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>Qty</label>
                                                        <input type="number" step="any" class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.quantity">
                                                        @error('shipments.' . $s . '.cargo_rows.' . $i . '.quantity') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>UOM</label>
                                                        <select class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.uom">
                                                            <option value="">Select</option>
                                                            @foreach ($uoms as $uom)
                                                                <option value="{{ $uom->abbreviation ?: $uom->name }}">{{ $uom->name }}{{ $uom->abbreviation ? ' ('.$uom->abbreviation.')' : '' }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('units_of_measure.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New</a></small>
                                                        <a href="#" wire:click.prevent="refresh('uoms')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>Packages</label>
                                                        <input type="number" class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.packages">
                                                        @error('shipments.' . $s . '.cargo_rows.' . $i . '.packages') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>Gross Wt</label>
                                                        <input type="number" step="any" class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.gross_weight">
                                                        @error('shipments.' . $s . '.cargo_rows.' . $i . '.gross_weight') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>CBM</label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.cbm">
                                                        @error('shipments.' . $s . '.cargo_rows.' . $i . '.cbm') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>DG</label><br>
                                                        <input type="checkbox" wire:model="shipments.{{ $s }}.cargo_rows.{{ $i }}.is_dangerous_goods">
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <label>&nbsp;</label><br>
                                                    <a href="#" wire:click.prevent="removeCargoRow({{ $s }}, {{ $i }})" class="btn btn-xs btn-danger" title="Remove cargo line"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                        @endforeach
                                        <a href="#" wire:click.prevent="addCargoRow({{ $s }})" class="btn btn-xs btn-info mb-20"><i class="fa fa-plus"></i> Add Cargo Line</a>

                                        <h6 class="underline mt-20 mb-20"><strong>Parties</strong></h6>
                                        @foreach ($shipment['party_rows'] as $i => $row)
                                            <div wire:key="edit-shipment-{{ $s }}-party-{{ $row['id'] ?? 'new-' . $i }}" class="row mb-10" style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Party Type</label>
                                                        <select class="form-control" wire:model="shipments.{{ $s }}.party_rows.{{ $i }}.party_type">
                                                            <option value="">Select</option>
                                                            @foreach ($partyOptions as $option)
                                                                <option value="{{ $option }}">{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Party @if (!empty($row['party_type']))<span class="required text-danger">*</span>@endif</label>
                                                        <select class="form-control" wire:model="shipments.{{ $s }}.party_rows.{{ $i }}.party_id">
                                                            <option value="">Select</option>
                                                            @if (!empty($row['party_type']) && isset($partyModels[$row['party_type']]))
                                                                @foreach ($partyModels[$row['party_type']] as $partyRecord)
                                                                    <option value="{{ $partyRecord['id'] }}">{{ $partyRecord['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('shipments.' . $s . '.party_rows.' . $i . '.party_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        @if (!empty($row['party_type']) && isset($partyRoutes[$row['party_type']]))
                                                            <small><a href="{{ route($partyRoutes[$row['party_type']]) }}" target="_blank"><i class="fa fa-plus-square-o"></i> New {{ ucfirst(str_replace('_', ' ', $row['party_type'])) }}</a></small>
                                                            <a href="#" wire:click.prevent="refreshPartyOptions({{ $s }}, {{ $i }})" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Role @if (!empty($row['party_id']))<span class="required text-danger">*</span>@endif</label>
                                                        <select class="form-control" wire:model="shipments.{{ $s }}.party_rows.{{ $i }}.role">
                                                            <option value="">Select Role</option>
                                                            @foreach ($partyRoles as $roleValue => $roleLabel)
                                                                <option value="{{ $roleValue }}">{{ $roleLabel }}</option>
                                                            @endforeach
                                                            @if (filled($row['role']) && ! isset($partyRoles[$row['role']]))
                                                                {{-- saved value from an import / older form that isn't in the list --}}
                                                                <option value="{{ $row['role'] }}">{{ $row['role'] }}</option>
                                                            @endif
                                                        </select>
                                                        @error('shipments.' . $s . '.party_rows.' . $i . '.role') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>&nbsp;</label><br>
                                                    <a href="#" wire:click.prevent="removePartyRow({{ $s }}, {{ $i }})" class="btn btn-xs btn-danger" title="Remove party"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                        @endforeach
                                        <a href="#" wire:click.prevent="addPartyRow({{ $s }})" class="btn btn-xs btn-info"><i class="fa fa-plus"></i> Add Party</a>
                                    </div>
                                @endforeach
                                @if ($removed_cargo_ids || $removed_party_ids)
                                    <p class="text-warning mt-10"><i class="fa fa-exclamation-triangle"></i> Removed lines are deleted when you click Save Changes.</p>
                                @endif

                                <h6 class="underline mt-20 mb-20"><strong>Status</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="status">
                                                @foreach ($statuses as $statusOption)
                                                    <option value="{{ $statusOption }}">{{ ucwords(str_replace('_', ' ', $statusOption)) }}</option>
                                                @endforeach
                                            </select>
                                            @error('status') <span class="text-danger error">{{ $message }}</span> @enderror
                                            @if (! $job->isApproved())
                                                <small class="text-muted">This job is {{ $job->authorization }} authorization - it can only be Draft or Cancelled until approved.{{ $job->authorization === 'rejected' ? ' Saving sends it for authorization again.' : '' }}</small>
                                            @else
                                                <div style="font-size:12px; line-height:1.4; color:#31708f; margin-top:4px;"><i class="fa fa-info-circle"></i> Updated automatically when container stages are recorded (see Container Stages). Invoiced, Closed and Cancelled are never changed.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

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
                                        <a href="{{ route('freight.jobs.show', $job->id) }}" class="btn bg-gray">Cancel</a>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Asked on Save when the job's Origin/Destination changed and open transport legs follow the old route. --}}
    <div wire:ignore.self class="modal fade" id="legRouteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle text-warning"></i> Update the transport legs too?</h4>
                </div>
                <div class="modal-body">
                    <p>You changed the job's Origin / Destination. These legs on the Transport tab still follow the old route:</p>
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr><th>Shipment</th><th>Leg</th><th>Current</th><th>Will become</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($leg_route_preview as $preview)
                                <tr>
                                    <td>{{ $preview['shipment'] }}</td>
                                    <td>{{ $preview['sequence'] }} {{ $preview['mode'] }}</td>
                                    <td>{{ $preview['current'] }}</td>
                                    <td><strong>{{ $preview['new'] }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <small class="text-muted">Completed or cancelled legs are never changed. Stops in between (e.g. a port) are kept.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-default" wire:click="saveWithLegRoute(0)" wire:loading.attr="disabled">Save, keep legs as they are</button>
                    <button type="button" class="btn btn-primary" wire:click="saveWithLegRoute(1)" wire:loading.attr="disabled"><i class="fa fa-check"></i> Save and update legs</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('show-legRouteModal', event => { $('#legRouteModal').modal('show'); })
        window.addEventListener('hide-legRouteModal', event => { $('#legRouteModal').modal('hide'); })
    </script>
</div>
