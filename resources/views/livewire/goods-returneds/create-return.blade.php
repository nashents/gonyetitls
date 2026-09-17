<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                                @error('goods_returned') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="panel-title">
                                <i class="fas fa-undo"></i>
                                {{ $goods_returned_id ? 'Edit Draft Return' : 'New Goods Return' }}
                                @if ($goods_returned_id)
                                    <span class="label label-default">Draft</span>
                                @endif
                            </div>
                        </div>

                        <div class="panel-body p-20">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Goods Received Note (GRN) <span class="required" style="color:red">*</span></label>
                                        <select class="form-control" wire:model="goods_received_id">
                                            <option value="">Select an approved GRN</option>
                                            @foreach ($goods_receiveds as $grn)
                                                <option value="{{ $grn->id }}">{{ $grn->goods_received_number }} - {{ $grn->vendor?->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('goods_received_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Vendor <span class="required" style="color:red">*</span></label>
                                        <select class="form-control" wire:model="vendor_id">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Returned By <span class="required" style="color:red">*</span></label>
                                        <select class="form-control" wire:model="employee_id">
                                            <option value="">Select Employee</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Return Date <span class="required" style="color:red">*</span></label>
                                        <input type="date" class="form-control" wire:model="return_date">
                                        @error('return_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Currency</label>
                                        <input type="text" class="form-control" wire:model="currency" maxlength="3">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>Overall Reason</label>
                                        <input type="text" class="form-control" wire:model="reason" placeholder="Optional summary reason for this return">
                                    </div>
                                </div>
                            </div>

                            @if (!empty($lines))
                            <hr>
                            <h4>Select item(s) to return</h4>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th style="width:3%"></th>
                                        <th>Product</th>
                                        <th>Received Qty</th>
                                        <th>Returnable Qty</th>
                                        <th style="width:12%">Qty to Return</th>
                                        <th>Return Reason</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lines as $line)
                                    <tr>
                                        <td>
                                            <input type="checkbox" wire:model="rows.{{ $line->id }}.selected"
                                                @if (($rows[$line->id]['returnable_qty'] ?? 0) <= 0) disabled @endif>
                                        </td>
                                        <td>
                                            {{ $line->product?->name }}
                                            <br><small class="text-muted">{{ $line->inventory_number ?? $line->asset_number ?? $line->tyre_number ?? '' }}</small>
                                        </td>
                                        <td>{{ $line->qty }}</td>
                                        <td>
                                            {{ $rows[$line->id]['returnable_qty'] ?? 0 }}
                                            @if (($rows[$line->id]['returnable_qty'] ?? 0) <= 0)
                                                <br><small class="text-danger">Nothing left to return</small>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" min="0" max="{{ $rows[$line->id]['returnable_qty'] ?? 0 }}"
                                                class="form-control" wire:model="rows.{{ $line->id }}.qty_returned">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" wire:model="rows.{{ $line->id }}.return_reason" placeholder="e.g. damaged, wrong item">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" wire:model="rows.{{ $line->id }}.notes">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @elseif ($goods_received_id)
                            <div class="alert alert-info">This GRN has no returnable line items.</div>
                            @endif

                            <div class="btn-group" role="group">
                                <button type="button" wire:click="saveDraft" class="btn bg-success btn-wide btn-rounded">
                                    <i class="fa fa-save"></i> Save Draft
                                </button>
                                @if ($goods_returned_id)
                                <button type="button" wire:click="submitForApproval" class="btn bg-primary btn-wide btn-rounded"
                                    onclick="return confirm('Submit this return for approval? You will not be able to edit it further.')">
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                                @endif
                                @php
                                    $closeRoute = match ($department) {
                                        'asset' => 'goods_returneds.assets',
                                        'tyre' => 'goods_returneds.tyres',
                                        default => 'goods_returneds.index',
                                    };
                                @endphp
                                <a href="{{ route($closeRoute) }}" class="btn btn-gray btn-wide btn-rounded">
                                    <i class="fa fa-times"></i> Close
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
