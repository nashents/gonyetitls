<div>
    @php
        $master = $consolidation->master_shipment;
        $masterJob = $master?->freight_job;
    @endphp
    <div class="row mt-30">
        <div class="col-md-12">
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ $consolidation->consolidation_number }} &mdash; Add Clients</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        Each client gets their own freight job with an <strong>LCL</strong> shipment, added to this consolidation.
                        Route, mode, currency, team, loading/offloading points and ETD/ETA are copied from master job
                        <strong>{{ $masterJob?->job_number ?? '—' }}</strong>
                        @if ($master?->containers?->isNotEmpty())
                            (container {{ $master->containers->pluck('container_number')->implode(', ') }})
                        @endif.
                        Rows with the same client, reference and service type become <strong>one job</strong> with several cargo lines -
                        use <i class="fa fa-copy"></i> to add another commodity for the same client.
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Default Service Type <small class="text-muted">(applies to rows without a client yet)</small></label>
                                <select class="form-control" wire:model="default_service_type_id">
                                    <option value="">None</option>
                                    @foreach ($serviceTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width:30px">#</th>
                                        <th style="min-width:180px">Client <span class="text-danger">*</span>
                                            <a href="{{ route('customers.index') }}" target="_blank" title="New customer"><i class="fa fa-plus-square-o"></i></a>
                                            <a href="#" wire:click.prevent="refresh('customers')" title="Refresh"><i class="fa fa-refresh"></i></a>
                                        </th>
                                        <th style="min-width:110px">Client Ref</th>
                                        <th style="min-width:170px">Service Type</th>
                                        <th style="min-width:150px">Commodity</th>
                                        <th style="min-width:150px">Description</th>
                                        <th style="min-width:90px">HS Code</th>
                                        <th style="min-width:80px">Packages</th>
                                        <th style="min-width:90px">Gross Wt (kg)</th>
                                        <th style="min-width:80px">CBM <span class="text-danger">*</span></th>
                                        <th style="min-width:120px">Marks &amp; Nos</th>
                                        <th style="width:70px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $i => $row)
                                        <tr wire:key="client-row-{{ $i }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                <select class="form-control input-sm" wire:model="rows.{{ $i }}.customer_id">
                                                    <option value="">Select client</option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error("rows.$i.customer_id") <span class="text-danger error">{{ $message }}</span> @enderror
                                            </td>
                                            <td><input type="text" class="form-control input-sm" wire:model.defer="rows.{{ $i }}.customer_reference"></td>
                                            <td>
                                                <select class="form-control input-sm" wire:model.defer="rows.{{ $i }}.freight_service_type_id">
                                                    <option value="">None</option>
                                                    @foreach ($serviceTypes as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control input-sm" wire:model.defer="rows.{{ $i }}.cargo_id">
                                                    <option value="">Select</option>
                                                    @foreach ($cargos as $cargo)
                                                        <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control input-sm" wire:model.defer="rows.{{ $i }}.commodity" placeholder="e.g. 5 x motorbikes">
                                                @error("rows.$i.commodity") <span class="text-danger error">{{ $message }}</span> @enderror
                                            </td>
                                            <td><input type="text" class="form-control input-sm" wire:model.defer="rows.{{ $i }}.hs_code"></td>
                                            <td>
                                                <input type="number" min="0" class="form-control input-sm" wire:model.lazy="rows.{{ $i }}.packages">
                                                @error("rows.$i.packages") <span class="text-danger error">{{ $message }}</span> @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="any" min="0" class="form-control input-sm" wire:model.lazy="rows.{{ $i }}.gross_weight">
                                                @error("rows.$i.gross_weight") <span class="text-danger error">{{ $message }}</span> @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="any" min="0" class="form-control input-sm" wire:model.lazy="rows.{{ $i }}.cbm">
                                                @error("rows.$i.cbm") <span class="text-danger error">{{ $message }}</span> @enderror
                                            </td>
                                            <td><input type="text" class="form-control input-sm" wire:model.defer="rows.{{ $i }}.marks_and_numbers"></td>
                                            <td class="text-nowrap">
                                                <a href="#" wire:click.prevent="duplicateRow({{ $i }})" class="btn btn-xs btn-default" title="Another commodity for this client"><i class="fa fa-copy"></i></a>
                                                <a href="#" wire:click.prevent="removeRow({{ $i }})" class="btn btn-xs btn-danger" title="Remove row"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-right">{{ $clientCount }} client job(s) will be created &mdash; Totals</th>
                                        <th>{{ number_format($totalPackages) }}</th>
                                        <th>{{ number_format($totalWeight, 2) }}</th>
                                        <th>{{ number_format($totalCbm, 3) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <a href="#" wire:click.prevent="addRow" class="btn btn-xs btn-info mb-20"><i class="fa fa-plus"></i> Add Row</a>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group pull-right mt-10">
                                    <a href="{{ route('freight.consolidations.show', $consolidation->id) }}" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i> Cancel</a>
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="save"><i class="fa fa-save"></i> Create Client Jobs</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
