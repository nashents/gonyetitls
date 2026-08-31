<div>
    <h6 class="underline mt-10 mb-10"><strong>Job Costing Summary</strong></h6>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>Revenue</th>
                <th>Cost</th>
                <th>Gross Profit</th>
                <th>Margin %</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Estimated</th>
                <td>{{ $job->currency?->symbol }}{{ number_format($job->estimated_revenue ?? 0, 2) }}</td>
                <td>{{ $job->currency?->symbol }}{{ number_format($job->estimated_cost ?? 0, 2) }}</td>
                <td>{{ $job->currency?->symbol }}{{ number_format($job->estimated_margin ?? 0, 2) }}</td>
                <td>{{ $estimatedMarginPercent !== null ? $estimatedMarginPercent.'%' : '—' }}</td>
            </tr>
            <tr>
                <th>Actual</th>
                <td>{{ $job->currency?->symbol }}{{ number_format($job->actual_revenue ?? 0, 2) }}</td>
                <td>{{ $job->currency?->symbol }}{{ number_format($job->actual_cost ?? 0, 2) }}</td>
                <td>{{ $job->currency?->symbol }}{{ number_format($job->actual_margin ?? 0, 2) }}</td>
                <td>{{ $actualMarginPercent !== null ? $actualMarginPercent.'%' : '—' }}</td>
            </tr>
            <tr>
                <th>Variance</th>
                <td>{{ $job->currency?->symbol }}{{ number_format(($job->actual_revenue ?? 0) - ($job->estimated_revenue ?? 0), 2) }}</td>
                <td>{{ $job->currency?->symbol }}{{ number_format(($job->actual_cost ?? 0) - ($job->estimated_cost ?? 0), 2) }}</td>
                <td>{{ $job->currency?->symbol }}{{ number_format(($job->actual_margin ?? 0) - ($job->estimated_margin ?? 0), 2) }}</td>
                <td>—</td>
            </tr>
            <tr class="warning">
                <th>Accrued (Unverified) Cost</th>
                <td colspan="3">{{ $job->currency?->symbol }}{{ number_format($accruedCost, 2) }}</td>
                <td><small>Not yet in Actual Cost</small></td>
            </tr>
        </tbody>
    </table>

    <form wire:submit.prevent="saveEstimates" class="row mb-20">
        <div class="col-md-3">
            <div class="form-group">
                <label>Estimated Revenue</label>
                <input type="number" step="any" class="form-control" wire:model="estimated_revenue">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Estimated Cost</label>
                <input type="number" step="any" class="form-control" wire:model="estimated_cost">
            </div>
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-block">Save Estimates</button>
        </div>
    </form>

    <h6 class="underline mt-20 mb-10"><strong>Freight Costs (Supplier)</strong></h6>
    <a href="#" wire:click.prevent="showAddCost" class="btn btn-xs btn-info mb-10" data-toggle="modal" data-target="#costModal{{ $job->id }}"><i class="fa fa-plus"></i> Add Cost</a>
    <a href="#" wire:click.prevent="generateBills" wire:confirm="Generate Bills for all verified/approved, not-yet-billed cost lines?" class="btn btn-xs btn-success mb-10"><i class="fa fa-file-invoice-dollar"></i> Generate Bills from Approved Costs</a>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Charge Type</th><th>Supplier</th><th>Amount</th><th>Verification</th><th>Bill</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($job->costs as $cost)
                <tr>
                    <td>{{ $cost->charge_type?->name }}</td>
                    <td>{{ $cost->vendor?->name }}</td>
                    <td>{{ $cost->currency?->symbol }}{{ number_format($cost->amount, 2) }}</td>
                    <td><span class="label label-info label-wide">{{ $verificationStatuses[$cost->verification_status] ?? $cost->verification_status }}</span></td>
                    <td>{{ $cost->bill?->bill_number ?? '—' }}</td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpandCost({{ $cost->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a>
                        <a href="#" wire:click.prevent="editCost({{ $cost->id }})" class="btn btn-xs btn-default" data-toggle="modal" data-target="#costModal{{ $job->id }}"><i class="fa fa-edit"></i></a>
                        <a href="#" wire:click.prevent="deleteCost({{ $cost->id }})" wire:confirm="Remove this cost line?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                @if ($expanded_cost_id == $cost->id)
                    <tr>
                        <td colspan="6" style="background-color:#f9f9f9;">
                            <div class="btn-group mb-10">
                                @foreach (['pending_verification','verified','disputed','approved','posted','rejected'] as $statusOption)
                                    <a href="#" wire:click.prevent="transitionCost({{ $cost->id }}, '{{ $statusOption }}')" class="btn btn-xs btn-default">{{ $verificationStatuses[$statusOption] }}</a>
                                @endforeach
                            </div>
                            @if (in_array($cost->verification_status, ['disputed','rejected']))
                                <p><strong>Dispute reason:</strong> {{ $cost->dispute_reason }}</p>
                            @endif
                            <div class="row mb-10">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" wire:model="dispute_reason" placeholder="Dispute/rejection reason (used on next transition)">
                                </div>
                            </div>
                            <h6 class="underline mt-10 mb-10"><strong>Documents</strong></h6>
                            @livewire('documents.index', ['id' => $cost->id, 'category' => 'freight_cost'], key('cost-docs-'.$cost->id))
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="6" class="text-center">No cost lines recorded for this job yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h6 class="underline mt-20 mb-10"><strong>Freight Charges (Customer)</strong></h6>
    <a href="#" wire:click.prevent="showAddCharge" class="btn btn-xs btn-info mb-10" data-toggle="modal" data-target="#chargeModal{{ $job->id }}"><i class="fa fa-plus"></i> Add Charge</a>
    <a href="#" wire:click.prevent="generateInvoices" wire:confirm="Generate Invoices for all approved, not-yet-invoiced charge lines?" class="btn btn-xs btn-success mb-10"><i class="fa fa-file-invoice-dollar"></i> Generate Invoices from Approved Charges</a>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Charge Type</th><th>Customer</th><th>Amount</th><th>Status</th><th>Invoice</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($job->charges as $charge)
                <tr>
                    <td>{{ $charge->charge_type?->name }}</td>
                    <td>{{ $charge->customer?->name }}</td>
                    <td>{{ $charge->currency?->symbol }}{{ number_format($charge->amount, 2) }}</td>
                    <td><span class="label label-info label-wide">{{ $chargeStatuses[$charge->status] ?? $charge->status }}</span></td>
                    <td>{{ $charge->invoice?->invoice_number ?? '—' }}</td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpandCharge({{ $charge->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a>
                        <a href="#" wire:click.prevent="editCharge({{ $charge->id }})" class="btn btn-xs btn-default" data-toggle="modal" data-target="#chargeModal{{ $job->id }}"><i class="fa fa-edit"></i></a>
                        <a href="#" wire:click.prevent="deleteCharge({{ $charge->id }})" wire:confirm="Remove this charge line?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                @if ($expanded_charge_id == $charge->id)
                    <tr>
                        <td colspan="6" style="background-color:#f9f9f9;">
                            <div class="btn-group mb-10">
                                @foreach (['draft','approved','invoiced','cancelled'] as $statusOption)
                                    <a href="#" wire:click.prevent="transitionCharge({{ $charge->id }}, '{{ $statusOption }}')" class="btn btn-xs btn-default">{{ $chargeStatuses[$statusOption] }}</a>
                                @endforeach
                            </div>
                            <h6 class="underline mt-10 mb-10"><strong>Documents</strong></h6>
                            @livewire('documents.index', ['id' => $charge->id, 'category' => 'freight_charge'], key('charge-docs-'.$charge->id))
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="6" class="text-center">No charge lines recorded for this job yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Add/Edit Cost Modal -->
    <div class="modal fade" id="costModal{{ $job->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveCost">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ $cost_id ? 'Edit' : 'Add' }} Freight Cost</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Charge Type</label>
                                    <select class="form-control" wire:model="cost_charge_type_id">
                                        <option value="">Select</option>
                                        @foreach ($chargeTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select class="form-control" wire:model="cost_vendor_id">
                                        <option value="">Select</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Supplier Invoice Ref</label>
                                    <input type="text" class="form-control" wire:model="cost_supplier_invoice_reference">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Shipment</label>
                                    <select class="form-control" wire:model="cost_shipment_id">
                                        <option value="">None</option>
                                        @foreach ($job->shipments as $shipment)
                                            <option value="{{ $shipment->id }}">{{ $shipment->shipment_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date Received</label>
                                    <input type="date" class="form-control" wire:model="cost_date_received">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="cost_currency_id">
                                        <option value="">Select</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Qty</label>
                                    <input type="number" step="any" class="form-control" wire:model="cost_quantity">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Chargeable Days</label>
                                    <input type="number" class="form-control" wire:model="cost_chargeable_days">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Rate</label>
                                    <input type="number" step="any" class="form-control" wire:model="cost_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Amount <span class="required text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model="cost_amount">
                                    @error('cost_amount') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Exch. Rate</label>
                                    <input type="number" step="any" class="form-control" wire:model="cost_exchange_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tax</label>
                                    <select class="form-control" wire:model="cost_tax_id">
                                        <option value="">None</option>
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" wire:model="cost_recoverable"> Recoverable Disbursement</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" wire:model="cost_customer_billable"> Customer Billable</label>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" wire:model="cost_notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Cost</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Charge Modal -->
    <div class="modal fade" id="chargeModal{{ $job->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveCharge">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ $charge_id ? 'Edit' : 'Add' }} Freight Charge</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Charge Type</label>
                                    <select class="form-control" wire:model="charge_charge_type_id">
                                        <option value="">Select</option>
                                        @foreach ($chargeTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <select class="form-control" wire:model="charge_customer_id">
                                        <option value="">Select</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customer Invoice Ref</label>
                                    <input type="text" class="form-control" wire:model="charge_customer_invoice_reference">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Shipment</label>
                                    <select class="form-control" wire:model="charge_shipment_id">
                                        <option value="">None</option>
                                        @foreach ($job->shipments as $shipment)
                                            <option value="{{ $shipment->id }}">{{ $shipment->shipment_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date Billed</label>
                                    <input type="date" class="form-control" wire:model="charge_date_billed">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="charge_currency_id">
                                        <option value="">Select</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Qty</label>
                                    <input type="number" step="any" class="form-control" wire:model="charge_quantity">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Chargeable Days</label>
                                    <input type="number" class="form-control" wire:model="charge_chargeable_days">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Rate</label>
                                    <input type="number" step="any" class="form-control" wire:model="charge_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Amount <span class="required text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model="charge_amount">
                                    @error('charge_amount') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Exch. Rate</label>
                                    <input type="number" step="any" class="form-control" wire:model="charge_exchange_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tax</label>
                                    <select class="form-control" wire:model="charge_tax_id">
                                        <option value="">None</option>
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" wire:model="charge_notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Charge</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-costModal-{{ $job->id }}', event => { $('#costModal{{ $job->id }}').modal('hide'); })
        window.addEventListener('show-costModal-{{ $job->id }}', event => { $('#costModal{{ $job->id }}').modal('show'); })
        window.addEventListener('hide-chargeModal-{{ $job->id }}', event => { $('#chargeModal{{ $job->id }}').modal('hide'); })
        window.addEventListener('show-chargeModal-{{ $job->id }}', event => { $('#chargeModal{{ $job->id }}').modal('show'); })
    </script>
</div>
